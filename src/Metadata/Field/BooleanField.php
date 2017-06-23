<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;

class BooleanField extends AbstractFieldMetadata
{
    /**
     * @var bool
     */
    private $defaultValue;

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     * @param bool|null   $defaultValue
     */
    public function __construct(string $propertyName, string $dbFieldName = null, bool $defaultValue = null)
    {
        $this->defaultValue = $defaultValue;
        parent::__construct($propertyName, $dbFieldName);
    }

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $normalizedPropertyName = 'is' === substr($this->propertyName, 0, 2)
            ? substr($this->propertyName, 2)
            : $this->propertyName;

        $normalizedPropertyName = StringUtil::camelize($normalizedPropertyName);
        $isser = new MethodGenerator('is'.$normalizedPropertyName);
        $isser->setBody('return $this->'.$this->propertyName.';');
        $isser->setReturnType(null === $this->defaultValue ? '?bool' : 'bool');

        $setter = $this->createSetter();
        $setter->setName(str_replace('setIs', 'set', $setter->getName()));

        $documentGenerator->addProperty($this->createProperty());
        $documentGenerator->addMethod($isser);
        $documentGenerator->addMethod($setter);
    }

    public function getType(): string
    {
        return 'bool';
    }

    public function getSerializationCode(): string
    {
        return '$dbData = null === $objectData ? null : (bool) $objectData;';
    }

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (bool) $dbData;';
    }

    protected function createProperty(): PropertyGenerator
    {
        $property = parent::createProperty();
        $property->setDefaultValue($this->defaultValue);

        return $property;
    }
}
