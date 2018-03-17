<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Serializable;

interface DocumentInterface extends Serializable
{
    /**
     * @return mixed
     */
    public function getMongoId();
}
