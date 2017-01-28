<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\ODM\Exception\LogicException;

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
     * @return BulkWriteBuilder
     */
    private function getBulkWriteBuilder()
    {
        if (null === $this->bulkWriteBuilder) {
            throw new LogicException('BulkWriteBuilder was not set to this document.');
        }

        return $this->bulkWriteBuilder;
    }

    /**
     * @param BulkWriteBuilder $builder
     */
    public function setBulkWriteBuilder(BulkWriteBuilder $builder)
    {
        $this->bulkWriteBuilder = $builder;
    }

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
}