<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Persistable;
use Tequila\MongoDB\DocumentInterface;

abstract class Document implements DocumentInterface, Persistable, BulkWriteBuilderAwareInterface
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