<?php

namespace Tequila\MongoDB\ODM\Serializer;

class IntegerSerializer implements SerializerInterface
{
    public function serialize($value, array $options = [])
    {
        return (int) $value;
    }
}
