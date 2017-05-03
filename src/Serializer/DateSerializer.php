<?php

namespace Tequila\MongoDB\ODM\Serializer;

use MongoDB\BSON\UTCDatetime;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;

class DateSerializer extends Serializer
{
    public function serialize($value, array $options = [])
    {
        if (null === $value) {
            return new UTCDatetime();
        } elseif ($value instanceof \DateTime) {
            return new UTCDatetime($value);
        } else {
            throw new InvalidArgumentException(
                'Invalid value for date field.'
            );
        }
    }
}