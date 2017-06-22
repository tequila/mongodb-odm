<?php

namespace Tequila\MongoDB\ODM\Proxy;

use MongoDB\BSON\Unserializable;
use Tequila\MongoDB\ODM\DocumentManagerAwareInterface;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\Proxy\Factory\GeneratorFactory;
use Tequila\MongoDB\ODM\Proxy\Traits\NestedProxyTrait;
use Tequila\MongoDB\ODM\Proxy\Traits\RootProxyTrait;
use Tequila\MongoDB\ODM\WriteModelInterface;
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
            $this->classGenerator->addTrait('RootProxyTrait');
            $this->classGenerator->addUse(RootProxyTrait::class);
            $interfaces = [
                RootProxyInterface::class,
                UpdateBuilderInterface::class,
                DocumentManagerAwareInterface::class,
                WriteModelInterface::class,
            ];
        } else {
            $this->classGenerator->addTrait('NestedProxyTrait');
            $this->classGenerator->addUse(NestedProxyTrait::class);
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

        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $fieldMetadata->generateProxy($this);
        }

        $this->generateBsonUnserializeMethod();

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

        $method = new MethodGenerator($this->isRoot ? 'bsonUnserialize' : 'doBsonUnserialize');
        if ($this->isRoot) {
            $method->setParameter(new ParameterGenerator('dbData', 'array'));
        }

        $methodBody = $this->buildUnserializationDefaultsCode();

        foreach ($this->metadata->getFieldsMetadata() as $field) {
            $pathInDocument = $this->isRoot
                ? "'{$field->getDbFieldName()}'"
                : sprintf('$this->getPathInDocument(\'%s\')', $field->getDbFieldName());
            $dbDataReplacement = $this->isRoot
                ? sprintf('$dbData[\'%s\']', $field->getDbFieldName())
                : sprintf('$this->_mongoDbData[\'%s\']', $field->getDbFieldName());

            $code = strtr($field->getUnserializationCode($this), [
                '$objectData' => '$objectData[\''.$field->getPropertyName().'\']',
                '$dbData' => $dbDataReplacement,
                '$pathInDocument' => $pathInDocument,
                '$rootProxy' => '$this->getRootProxy()',
            ]);

            $methodBody .= PHP_EOL.$code;
        }

        $methodBody .= PHP_EOL.'parent::bsonUnserialize($objectData);';
        $methodBody .= PHP_EOL.'$this->_mongoDbData = null;';

        $method->setBody($methodBody);

        $this->addMethod($method);
    }

    private function buildUnserializationDefaultsCode(): string
    {
        $lines = [];
        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $lines[] = "'{$fieldMetadata->getDbFieldName()}' => null,";
        }

        if ($this->isRoot) {
            $code = <<<'EOT'
$defaults = [
%s
]; 
$dbData += $defaults;
$objectData = [];
EOT;
        } else {
            $code = <<<'EOT'
$defaults = [
%s
]; 
$this->_mongoDbData += $defaults;
$objectData = [];
EOT;
        }

        return sprintf($code, implode("    \n", $lines));
    }
}
