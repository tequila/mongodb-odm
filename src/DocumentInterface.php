<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;

interface DocumentInterface extends Serializable, Unserializable
{
    /**
     * @return mixed
     */
    public function getMongoId();
}
