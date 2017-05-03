<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Serializer\Serializer;

class SerializerFactory
{
    /**
     * @param string $className
     * @return Serializer
     */
    public function getSerializer($className)
    {
        /** @var Serializer $serializer */
        $serializer = new $className();
        $serializer->setFactory($this);

        return $serializer;
    }
}