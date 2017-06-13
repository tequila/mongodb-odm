<?php

namespace Tequila\MongoDB\ODM;

trait UnserializableTrait
{
    public function bsonUnserialize(array $data)
    {
        foreach ($data as $propertyName => $value) {
            $this->$propertyName = $value;
        }
    }
}
