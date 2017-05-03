<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Serializer\SerializerInterface;

class SerializerFactory
{
    /**
     * @var SerializerInterface[]
     */
    private $serializersCache = [];

    /**
     * @param string $className
     *
     * @return SerializerInterface
     */
    public function getSerializer($className)
    {
        if (!array_key_exists($className, $this->serializersCache)) {
            $this->serializersCache[$className] = new $className();
        }

        return $this->serializersCache[$className];
    }
}
