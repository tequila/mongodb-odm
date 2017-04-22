<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Persistable;
use Tequila\MongoDB\DocumentInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;

abstract class Document implements DocumentInterface, Persistable, BulkWriteBuilderAwareInterface
{
    /**
     * @var ObjectID|null|mixed
     */
    protected $id;

    /**
     * @var BulkWriteBuilder
     */
    private $bulkWriteBuilder;

    /**
     * @return WriteModel\UpdateOneDocument
     */
    public function update()
    {
        return $this->getBulkWriteBuilder()->updateDocument($this);
    }

    /**
     * @return WriteModel\ReplaceOneDocument
     */
    public function replace()
    {
        return $this->getBulkWriteBuilder()->replaceDocument($this);
    }

    /**
     * @return WriteModel\DeleteOneDocument
     */
    public function delete()
    {
        return $this->getBulkWriteBuilder()->deleteDocument($this);
    }

    /**
     * @param BulkWriteBuilder $builder
     */
    final public function setBulkWriteBuilder(BulkWriteBuilder $builder)
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

    /**
     * @inheritdoc
     */
    public function bsonSerialize()
    {
        if ($this->id) {
            return ['_id' => $this->id];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function bsonUnserialize(array $data)
    {
        $this->id = $data['_id'];
    }

    public function __debugInfo()
    {
        $reflection = new \ReflectionObject($this);
        $debugInfo = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $debugInfo[$property->getName()] = $property->getValue($this);
        }

        return $debugInfo;
    }

    /**
     * @return BulkWriteBuilder
     */
    private function getBulkWriteBuilder()
    {
        if (null === $this->bulkWriteBuilder) {
            throw new LogicException(
                'BulkWriteBuilder was not set to this document. Maybe document is new?'
            );
        }

        return $this->bulkWriteBuilder;
    }
}