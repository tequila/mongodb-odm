<?php

namespace Tequila\MongoDB\ODM\Serializer;

class IntegerSerializer extends Serializer
{
    public function serialize($value, array $options = [])
    {
        return (int) $value;
    }
}