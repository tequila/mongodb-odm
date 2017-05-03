<?php

namespace Tequila\MongoDB\ODM\Serializer;

class StringSerializer extends Serializer
{
    public function serialize($value, array $options = [])
    {
        return (string) $value;
    }
}