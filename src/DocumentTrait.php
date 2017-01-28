<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;

trait DocumentTrait
{
    /**
     * @var ObjectID|null|mixed
     */
    private $id;

    /**
     * @var BulkWriteBuilder
     */
    private $bulkWriteBuilder;

    /**
     * @return mixed|ObjectID|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param BulkWriteBuilder $builder
     */
    public function setBulkWriteBuilder(BulkWriteBuilder $builder)
    {
        $this->bulkWriteBuilder = $builder;
    }
}