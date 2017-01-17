<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Persistable;
use Tequila\MongoDB\WriteModelInterface;

interface DocumentInterface extends Persistable
{
    /**
     * @return ObjectID|mixed
     */
    public function getId();

    /**
     * @param ObjectID|mixed $objectId
     */
    public function setId($objectId);

    /**
     * @return WriteModelInterface|null
     */
    public function getWriteModel();
}