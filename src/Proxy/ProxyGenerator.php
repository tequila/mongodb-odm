<?php

namespace Tequila\MongoDB\ODM\Proxy;

use MongoDB\BSON\Unserializable;
use Tequila\MongoDB\ODM\DocumentManagerAwareInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\DocumentMetadata;
use Tequila\MongoDB\ODM\Proxy\Traits\ProxyTrait;
use Tequila\MongoDB\ODM\Proxy\Traits\RootDocumentTrait;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Reflection\ClassReflection;

class ProxyGenerator
{
    /**
     * @var DocumentMetadata
     */
    private $metadata;

    /**
     * @var ClassReflection
     */
    private $reflection;

    /**
     * @var ClassGenerator
     */
    private $documentClassGenerator;

    /**
     * @var ClassGenerator
     */
    private $proxyClassGenerator;

    /**
     * @var ProxyGeneratorFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param DocumentMetadata      $metadata
     * @param ProxyGeneratorFactory $factory
     */
    public function __construct(DocumentMetadata $metadata, ProxyGeneratorFactory $factory)
    {
        $this->metadata = $metadata;
        $this->factory = $factory;
        $this->reflection = new ClassReflection($metadata->getDocumentClass());
        $this->documentClassGenerator = ClassGenerator::fromReflection($this->reflection);
        $this->proxyClassGenerator = new ClassGenerator();
        $this->proxyClassGenerator->addUse($metadata->getDocumentClass());
        $this->proxyClassGenerator->setExtendedClass($metadata->getDocumentClass());
    }

    public function addError(string $error)
    {
        $this->errors = $error;
    }

    /**
     * @return DocumentMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return string
     */
    public function getDocumentClass(): string
    {
        return $this->metadata->getDocumentClass();
    }

    /**
     * @return ClassReflection
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * @param MethodGenerator $method
     */
    public function addMethod(MethodGenerator $method)
    {
        $this->proxyClassGenerator->addMethodFromGenerator($method);
    }

    /**
     * @param bool $asRootDocument
     *
     * @return ClassGenerator
     */
    public function generateClass(bool $asRootDocument = true): ClassGenerator
    {
        if ($asRootDocument) {
            $this->proxyClassGenerator->addTrait('RootDocumentTrait');
            $this->proxyClassGenerator->addUse(RootDocumentTrait::class);
            $this->proxyClassGenerator->setImplementedInterfaces([
                RootDocumentInterface::class,
                ProxyInterface::class,
                UpdateBuilderInterface::class,
                MongoIdAwareInterface::class,
                DocumentManagerAwareInterface::class,
                Unserializable::class,
            ]);
        } else {
            $this->proxyClassGenerator->addTrait('ProxyTrait');
            $this->proxyClassGenerator->addUse(ProxyTrait::class);
            $this->proxyClassGenerator->setImplementedInterfaces([
                ProxyInterface::class,
                Unserializable::class,
            ]);
        }

        $this->proxyClassGenerator->addUse(CurrentRootDocument::class);

        $unserializeMethod = new MethodGenerator('bsonUnserialize');
        $unserializeMethod->setParameter(new ParameterGenerator('dbData', 'array'));

        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            if (method_exists($fieldMetadata, 'setProxyGeneratorFactory')) {
                $fieldMetadata->setProxyGeneratorFactory($this->factory);
            }
            $fieldMetadata->generateProxy($this);
        }

        $this->generateBsonUnserializeMethod($asRootDocument);

        return $this->proxyClassGenerator;
    }

    /**
     * @return ClassGenerator
     */
    public function getProxyClassGenerator(): ClassGenerator
    {
        return $this->proxyClassGenerator;
    }

    private function generateBsonUnserializeMethod(bool $asRootDocument = true)
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

        $this->proxyClassGenerator->addUse(CurrentRootDocument::class);

        $methodBody = $asRootDocument
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
            $lines[] = "'{$fieldMetadata->getDbFieldName()}' => {$fieldMetadata->getPropertyDefaultValue()}";
        }

        $code = <<<'EOT'
$defaults = [
    %s
]; 
$dbData += $defaults;
$objectData = [];
EOT;

        return sprintf($code, implode(",\n", $lines));
    }
}
