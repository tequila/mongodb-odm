<?php

namespace Tequila\MongoDB\ODM\WriteModel;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\ODM\WriteModel\Traits\CollationTrait;
use Tequila\MongoDB\ODM\WriteModel\Traits\FilterAndOptionsTrait;
use Tequila\MongoDB\ODM\WriteModel\Traits\UpsertTrait;
use Tequila\MongoDB\Write\Model\Update;

class ReplaceOneDocument extends DocumentAwareWriteModel
{
    use FilterAndOptionsTrait;
    use CollationTrait;
    use UpsertTrait;

    /**
     * {@inheritdoc}
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        $options = ['multi' => false] + $this->options;

        (new Update($this->getFilter(), $this->getDocument(), $options))->writeToBulk($bulk);
    }
}
