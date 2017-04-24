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
     * {@inheritdoc}
     */
    public function writeToBulk(BulkWrite $bulk)
    {
        (new UpdateOne($this->getFilter(), $this->update, $this->options))->writeToBulk($bulk);
    }

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function addAllToSet($field, array $values)
    {
        $this->addToSet($field, ['$each' => $values]);

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return $this
     */
    public function addToSet($field, $value)
    {
        $this->update['$addToSet'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function popFirst($field)
    {
        $this->update['$pop'][$field] = -1;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function popLast($field)
    {
        $this->update['$pop'][$field] = 1;

        return $this;
    }

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function pullAll($field, array $values)
    {
        $this->update['$pullAll'][$field] = $values;

        return $this;
    }

    /**
     * @param string      $field
     * @param array|mixed $condition - a condition to specify values to delete, or a value to delete
     *
     * @return $this
     */
    public function pull($field, $condition)
    {
        $this->update['$pull'][$field] = $condition;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     */
    public function push($field, $value)
    {
        $this->update['$push'][$field] = $value;
    }

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function pushAll($field, array $values)
    {
        $this->push($field, ['$each' => $values]);

        return $this;
    }

    /**
     * @param string $field
     */
    public function currentDate($field)
    {
        $this->update['$currentDate'][$field] = true;
    }

    /**
     * @param string $field
     */
    public function currentTimestamp($field)
    {
        $this->update['$currentDate'][$field] = ['$type' => 'timestamp'];
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function increment($field, $value)
    {
        $this->update['$inc'][$field] = $value;

        return $this;
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function multiply($field, $value)
    {
        $this->update['$mul'][$field] = $value;

        return $this;
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function min($field, $value)
    {
        $this->update['$min'][$field] = $value;

        return $this;
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function max($field, $value)
    {
        $this->update['$max'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOnInsert($field, $value)
    {
        $this->update['$setOnInsert'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return $this
     */
    public function set($field, $value)
    {
        $this->update['$set'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function unsetField($field)
    {
        $this->update['$unset'][$field] = '';

        return $this;
    }
}
