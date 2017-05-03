<?php

namespace Tequila\MongoDB\ODM;

class FieldMetadata
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var null|string
     */
    private $serializerClass;

    /**
     * @var array
     */
    private $serializerOptions;

    /**
     * @var string
     */
    private $dbFieldName;

    /**
     * FieldMetadata constructor.
     *
     * @param $propertyName
     * @param $serializerClass
     * @param array $serializerOptions
     * @param null  $dbFieldName
     */
    public function __construct(
        $propertyName,
        $serializerClass,
        array $serializerOptions = [],
        $dbFieldName = null
    ) {
        $this->propertyName = $propertyName;
        $this->serializerClass = $serializerClass;
        $this->serializerOptions = $serializerOptions;
        $this->dbFieldName = $dbFieldName ?: $propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return null|string
     */
    public function getSerializerClass()
    {
        return $this->serializerClass;
    }

    /**
     * @return array
     */
    public function getSerializerOptions()
    {
        return $this->serializerOptions;
    }

    /**
     * @return string
     */
    public function getDbFieldName()
    {
        return $this->dbFieldName;
    }
}
