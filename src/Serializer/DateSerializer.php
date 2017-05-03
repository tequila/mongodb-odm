<?php

namespace Tequila\MongoDB\ODM\Serializer;

use MongoDB\BSON\UTCDatetime;

class DateSerializer implements SerializerInterface
{
    public function serialize($value, array $options = [])
    {
        return new UTCDatetime($value);
    }
}
