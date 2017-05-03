<?php

namespace Tequila\MongoDB\ODM\Serializer;

interface SerializerInterface
{
    /**
     * @param $value
     * @param array $options
     * @return mixed
     */
    public function serialize($value, array $options = []);
}