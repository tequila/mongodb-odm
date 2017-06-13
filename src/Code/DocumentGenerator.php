<?php

namespace Tequila\MongoDB\ODM\Code;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\UnserializableTrait;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\ClassReflection;

class DocumentGenerator
{
    /**
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    /**
     * @var ClassReflection
     */
    private $reflection;

    /**
     * @param ClassMetadata $metadata
     */
    public function __construct(ClassMetadata $metadata)
    {
        $this->metadata = $metadata;

        $this->reflection = new ClassReflection($this->metadata->getDocumentClass());
        $this->fileGenerator = FileGenerator::fromReflectedFileName($this->reflection->getFileName());
        $this->classGenerator = $this->fileGenerator->getClass($this->reflection->getShortName());
    }

    public function addProperty(PropertyGenerator $property)
    {
        if (!$this->classGenerator->hasProperty($property->getName())) {
            $this->classGenerator->addPropertyFromGenerator($property);
        }
    }

    public function addMethod(MethodGenerator $method)
    {
        if (!$this->classGenerator->hasMethod($method->getName())) {
            $this->classGenerator->addMethodFromGenerator($method);
        }
    }

    /**
     * @param string      $use
     * @param string|null $useAlias
     */
    public function addUse(string $use, string $useAlias = null)
    {
        $this->classGenerator->addUse($use, $useAlias);
    }

    public function generateClass(): void
    {
        $this->classGenerator->addUse(Serializable::class);
        $this->classGenerator->setImplementedInterfaces([Serializable::class]);
        $this->classGenerator->addTrait('UnserializableTrait');
        $this->classGenerator->addUse(UnserializableTrait::class);
        if ($this->classGenerator->hasMethod('bsonSerialize')) {
            $this->classGenerator->removeMethod('bsonSerialize');
        }

        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $fieldMetadata->generateDocument($this);
        }

        $this->classGenerator->addMethodFromGenerator($this->generateBsonSerializeMethod());

        $code = $this->fileGenerator->generate();
        file_put_contents($this->reflection->getFileName(), $code);
    }

    private function generateBsonSerializeMethod(): MethodGenerator
    {
        $method = new MethodGenerator('bsonSerialize');

        $methodBody = '$dbData = [];';

        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $code = strtr($fieldMetadata->getSerializationCode(), [
                '$objectData' => '$this->'.$fieldMetadata->getPropertyName(),
                '$dbData' => sprintf('$dbData[\'%s\']', $fieldMetadata->getDbFieldName()),
            ]);

            $methodBody .= PHP_EOL.$code;
        }

        $methodBody .= str_repeat(PHP_EOL, 2).'return $dbData;';

        $method->setBody($methodBody);

        return $method;
    }
}
