<?php

namespace Tequila\MongoDB\ODM\WriteModel;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\ODM\WriteModel\Traits\CollationTrait;
use Tequila\MongoDB\ODM\WriteModel\Traits\FilterAndOptionsTrait;
use Tequila\MongoDB\Write\Model\DeleteOne;

class DeleteOneDocument extends DocumentAwareWriteModel
{
    use FilterAndOptionsTrait;
    use CollationTrait;

    /**
     * {@inheritdoc}
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        (new DeleteOne($this->getFilter(), $this->options))->writeToBulk($bulk);
    }
}
