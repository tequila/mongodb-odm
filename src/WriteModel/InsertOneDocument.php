<?php

namespace Tequila\MongoDB\ODM\WriteModel;

use Tequila\MongoDB\BulkWrite;

class InsertOneDocument extends DocumentAwareWriteModel
{
    /**
     * {@inheritdoc}
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $bulk->insert($this->getDocument());
    }
}
