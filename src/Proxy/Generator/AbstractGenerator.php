<?php

namespace Tequila\MongoDB\ODM\Proxy\Generator;

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
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var GeneratorFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $errors = [];

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
     * @param ClassMetadata    $metadata
     * @param GeneratorFactory $factory
     * @param string           $proxyNamespace
     */
    public function __construct(ClassMetadata $metadata, GeneratorFactory $factory, string $proxyNamespace)
    {
        $this->metadata = $metadata;
        $this->factory = $factory;
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
     * @param string $documentClass
     *
     * @return string
     */
    public function getOtherProxyClass(string $documentClass): string
    {
        return $this->factory->getProxyClass($documentClass);
    }

    /**
     * @param string $error
     */
    public function addError(string $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return ClassReflection
     */
    public function getReflection(): ClassReflection
    {
        return $this->metadata->getReflection();
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

    public function getProxyClass(): string
    {
        return $this->proxyNamespace.'\\'.$this->getDocumentClass().'Proxy';
    }

    private function generateBsonUnserializeMethod()
    {
        if (!$this->getReflection()->hasMethod('bsonUnserialize')) {
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
