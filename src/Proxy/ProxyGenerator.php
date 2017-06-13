<?php

namespace Tequila\MongoDB\ODM\Proxy;

use MongoDB\BSON\Unserializable;
use Tequila\MongoDB\ODM\DocumentManagerAwareInterface;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\Proxy\Factory\GeneratorFactory;
use Tequila\MongoDB\ODM\Proxy\Traits\ProxyTrait;
use Tequila\MongoDB\ODM\Proxy\Traits\RootDocumentTrait;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Reflection\ClassReflection;

class ProxyGenerator
{
    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * @var ClassReflection
     */
    private $reflection;

    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var GeneratorFactory
     */
    private $factory;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var
     */
    private $isRoot;

    /**
     * @param string                   $documentClass
     * @param MetadataFactoryInterface $metadataFactory
     * @param GeneratorFactory         $factory
     * @param string                   $proxyNamespace
     * @param bool                     $isRoot
     */
    public function __construct(
        string $documentClass,
        MetadataFactoryInterface $metadataFactory,
        GeneratorFactory $factory,
        string $proxyNamespace,
        bool $isRoot = true
    ) {
        $metadata = $metadataFactory->getClassMetadata($documentClass);

        $this->metadata = $metadata;
        $this->metadataFactory = $metadataFactory;
        $this->factory = $factory;
        $this->reflection = new ClassReflection($metadata->getDocumentClass());
        if ('' === $proxyNamespace || '\\' === $proxyNamespace) {
            throw new InvalidArgumentException('$proxyNamespace cannot be empty.');
        }
        $this->proxyNamespace = trim($proxyNamespace, '\\');
        $this->isRoot = $isRoot;

        $this->classGenerator = new ClassGenerator($this->getProxyClass());
        $this->classGenerator->addUse($metadata->getDocumentClass());
        $this->classGenerator->setExtendedClass($metadata->getDocumentClass());
    }

    /**
     * @param string      $use
     * @param string|null $useAlias
     */
    public function addUse(string $use, string $useAlias = null)
    {
        $this->classGenerator->addUse($use, $useAlias);
    }

    /**
     * @return string
     */
    public function getDocumentClass(): string
    {
        return $this->metadata->getDocumentClass();
    }

    /**
     * @return string
     */
    public function getProxyClass(): string
    {
        $suffix = $this->isRoot ? 'RootProxy' : 'NestedProxy';
        $proxyClassName = $this->getDocumentClass().$suffix;

        return $this->proxyNamespace.'\\'.$proxyClassName;
    }

    /**
     * @return GeneratorFactory
     */
    public function getFactory(): GeneratorFactory
    {
        return $this->factory;
    }

    /**
     * @return MetadataFactoryInterface
     */
    public function getMetadataFactory(): MetadataFactoryInterface
    {
        return $this->metadataFactory;
    }

    /**
     * @param string $error
     */
    public function addError(string $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return ClassMetadata
     */
    public function getMetadata(): ClassMetadata
    {
        return $this->metadata;
    }

    /**
     * @return ClassReflection
     */
    public function getDocumentReflection(): ClassReflection
    {
        return $this->reflection;
    }

    /**
     * @param MethodGenerator $method
     */
    public function addMethod(MethodGenerator $method)
    {
        $this->classGenerator->addMethodFromGenerator($method);
    }

    /**
     * @return ClassGenerator
     */
    public function generateClass(): ClassGenerator
    {
        if ($this->isRoot) {
            $this->classGenerator->addTrait('RootDocumentTrait');
            $this->classGenerator->addUse(RootDocumentTrait::class);
            $interfaces = [
                MongoIdAwareInterface::class,
                RootProxyInterface::class,
                NestedProxyInterface::class,
                UpdateBuilderInterface::class,
                DocumentManagerAwareInterface::class,
                Unserializable::class,
            ];
        } else {
            $this->classGenerator->addTrait('ProxyTrait');
            $this->classGenerator->addUse(ProxyTrait::class);
            $interfaces = [
                NestedProxyInterface::class,
                Unserializable::class,
            ];
        }

        foreach ($interfaces as $interface) {
            $this->classGenerator->addUse($interface);
        }
        $this->classGenerator->setImplementedInterfaces($interfaces);

        $this->classGenerator->addUse(CurrentRootDocument::class);

        $unserializeMethod = new MethodGenerator('bsonUnserialize');
        $unserializeMethod->setParameter(new ParameterGenerator('dbData', 'array'));

        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $fieldMetadata->generateProxy($this);
        }

        $this->generateBsonUnserializeMethod();
        $this->generateGetMongoIdMethod();

        return $this->classGenerator;
    }

    private function generateBsonUnserializeMethod()
    {
        if (!$this->reflection->hasMethod('bsonUnserialize')) {
            throw new LogicException(
                sprintf(
                    'Document class %s does not have method "bsonUnserialize", proxy cannot be generated.',
                    $this->getDocumentClass()
                )
            );
        }

        $method = new MethodGenerator('bsonUnserialize', [
            new ParameterGenerator('dbData', 'array'),
        ]);

        $this->classGenerator->addUse(CurrentRootDocument::class);

        $methodBody = $this->isRoot
            ? 'CurrentRootDocument::$value = $this;'
            : '$this->rootDocument = CurrentRootDocument::$value;'.PHP_EOL.'$this->extractPathInDocument($dbData);'
        ;

        $methodBody .= PHP_EOL.$this->buildUnserializationDefaultsCode();

        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $code = strtr($fieldMetadata->getUnserializationCode($this), [
                '$objectData' => '$objectData[\''.$fieldMetadata->getPropertyName().'\']',
                '$dbData' => sprintf('$dbData[\'%s\']', $fieldMetadata->getDbFieldName()),
            ]);

            $methodBody .= PHP_EOL.$code;
        }

        $methodBody .= PHP_EOL.'parent::bsonUnserialize($objectData);';

        $method->setBody($methodBody);

        $this->addMethod($method);
    }

    private function buildUnserializationDefaultsCode(): string
    {
        $lines = [];
        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $lines[] = "'{$fieldMetadata->getDbFieldName()}' => null,";
        }

        $code = <<<'EOT'
$defaults = [
%s
]; 
$dbData += $defaults;
$objectData = [];
EOT;

        return sprintf($code, implode("    \n", $lines));
    }

    private function generateGetMongoIdMethod()
    {
        if (!$this->reflection->hasMethod('getMongoId')) {
            $pkField = $this->metadata->getPrimaryKeyField();
            $propertyName = $pkField->getPropertyName();
            if (
                $this->reflection->hasProperty($propertyName)
                && !$this->reflection->getProperty($propertyName)->isPrivate()
            ) {
                $mongoIdCode = 'return $this->'.$propertyName.';';
            } elseif (
                $this->reflection->hasMethod($methodName = 'get'.StringUtil::camelize($propertyName))
                && !$this->reflection->getMethod($methodName)->isPrivate()
            ) {
                $mongoIdCode = 'return $this->'.$methodName.'();';
            } else {
                throw new LogicException(
                    sprintf(
                        'Mongo id cannot be retrieved from %s. This class must contain not private property %s or not private method %s()',
                        $this->getDocumentClass(),
                        $propertyName,
                        $methodName
                    )
                );
            }

            $method = new MethodGenerator('getMongoId');
            $method->setBody($mongoIdCode);
            $this->classGenerator->addMethodFromGenerator($method);
        }
    }
}
