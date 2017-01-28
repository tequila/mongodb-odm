<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Exception\LogicException;

abstract class Document implements DocumentInterface
{
    use DocumentTrait;

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
     * @return BulkWriteBuilder
     */
    private function getBulkWriteBuilder()
    {
        if (null === $this->bulkWriteBuilder) {
            throw new LogicException('BulkWriteBuilder was not set to this document.');
        }

        return $this->bulkWriteBuilder;
    }
}