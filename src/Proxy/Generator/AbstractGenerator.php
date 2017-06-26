<?php

namespace Tequila\MongoDB\ODM\Proxy\Generator;

use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\Proxy\Factory\GeneratorFactory;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;

abstract class AbstractGenerator
{
    /**
     * @var ClassMetadata
     */
    protected $metadata;

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
     * @return string
     */
    abstract public function getProxyClass(): string;

    /**
     * @return MethodGenerator
     */
    abstract protected function createBsonUnserializeMethod(): MethodGenerator;

    /**
     * @return string[]
     */
    abstract protected function getInterfaces(): array;

    /**
     * @return string[]
     */
    abstract protected function getTraits(): array;

    /**
     * @param string                   $documentClass
     * @param MetadataFactoryInterface $metadataFactory
     * @param GeneratorFactory         $factory
     * @param string                   $proxyNamespace
     */
    public function __construct(
        string $documentClass,
        MetadataFactoryInterface $metadataFactory,
        GeneratorFactory $factory,
        string $proxyNamespace
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
        $interfaces = $this->getInterfaces();
        foreach ($interfaces as $interface) {
            $this->classGenerator->addUse($interface);
        }
        $this->classGenerator->setImplementedInterfaces($interfaces);

        $traits = $this->getTraits();
        foreach ($traits as $trait) {
            $this->classGenerator->addUse($trait);
            $this->classGenerator->addTrait((new \ReflectionClass($trait))->getShortName());
        }

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

        $this->addMethod($this->createBsonUnserializeMethod());
    }
}
