<?php

namespace Tequila\MongoDB\ODM\FieldMetadata;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Generator\ProxyGenerator;
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

    public function getType(): string
    {
        return 'bool';
    }

    public function getSerializationCode(): string
    {
        return '$dbData = null === $objectData ? null : (bool) $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (bool) $dbData;';
    }

    protected function createProperty(): PropertyGenerator
    {
        $property = parent::createProperty();
        $property->setDefaultValue($this->defaultValue);

        return $property;
    }

    protected function createGetter(): MethodGenerator
    {
        $getter = parent::createGetter();
        $getter->setReturnType(null === $this->defaultValue ? '?bool' : 'bool');

        return $getter;
    }
}
