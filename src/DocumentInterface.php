<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Persistable;

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
     * @param BulkWriteBuilder $manager
     */
    public function setBulkWriteBuilder(BulkWriteBuilder $manager);
}