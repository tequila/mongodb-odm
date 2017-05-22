<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface MongoIdAwareInterface
{
    /**
     * @return mixed
     */
    public function getMongoId();
}
