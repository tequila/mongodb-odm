<?php

namespace Tequila\MongoDB\ODM;

trait PersistenceTrait
{
    /**
     * @var string[]
     */
    private $_propertiesToSerialize = [];

    public function bsonSerialize()
    {
        $data = [];
        foreach ($this->_propertiesToSerialize as $propertyName) {
            $data[$propertyName] = $this->$propertyName;
        }
    }

    public function bsonUnserialize(array $data)
    {
        foreach ($data as $propertyName => $value) {
            $this->$propertyName = $value;
        }
    }

    /**
     * @param string[] $propertyNames
     */
    public function setPropertiesToSerialize(array $propertyNames)
    {
        $this->_propertiesToSerialize = $propertyNames;
    }
}
