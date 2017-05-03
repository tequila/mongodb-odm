<?php

namespace Tequila\MongoDB\ODM\Serializer;

class StringSerializer implements SerializerInterface
{
    public function serialize($value, array $options = [])
    {
        return (string) $value;
    }
}
