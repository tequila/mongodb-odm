<?php

namespace Tequila\MongoDB\ODM\WriteModel;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\ODM\WriteModel\Traits\CollationTrait;
use Tequila\MongoDB\ODM\WriteModel\Traits\FilterAndOptionsTrait;
use Tequila\MongoDB\ODM\WriteModel\Traits\UpsertTrait;
use Tequila\MongoDB\Write\Model\UpdateOne;

class UpdateOneDocument extends DocumentAwareWriteModel
{
    use FilterAndOptionsTrait;
    use CollationTrait;
    use UpsertTrait;

    /**
     * @var array
     */
    private $update = [];

    /**
     * @inheritdoc
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        (new UpdateOne($this->getFilter(), $this->update, $this->options))->writeToBulk($bulk);
    }

    /**
     * @param string $field
     */
    public function currentDate($field)
    {
        $this->update['$currentDate'][$field] = true;
    }

    /**
     * @param $field
     */
    public function currentTimestamp($field)
    {
        $this->update['$currentDate'][$field] = ['$type' => 'timestamp'];
    }

    /**
     * @param string $field
     * @param int|float $value
     * @return $this
     */
    public function increment($field, $value)
    {
        $this->update['$inc'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param int|float $value
     * @return $this
     */
    public function multiply($field, $value)
    {
        $this->update['$mul'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param int|float $value
     * @return $this
     */
    public function min($field, $value)
    {
        $this->update['$min'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param int|float $value
     * @return $this
     */
    public function max($field, $value)
    {
        $this->update['$max'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function setOnInsert($field, $value)
    {
        $this->update['$setOnInsert'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function set($field, $value)
    {
        $this->update['$set'][$field] = $value;

        return $this;
    }
}