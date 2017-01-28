<?php

namespace Tequila\MongoDB\ODM;

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
}