<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

trait UpdateBuilderTrait
{
    /**
     * @var array
     */
    private $mongoDbUpdate = [];

    /**
     * @var array
     */
    private $mongoDbOptions = [];

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function addAllToSet(string $field, array $values)
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
    public function addToSet(string $field, $value)
    {
        $this->mongoDbUpdate['$addToSet'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function popFirst(string $field)
    {
        $this->mongoDbUpdate['$pop'][$field] = -1;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function popLast(string $field)
    {
        $this->mongoDbUpdate['$pop'][$field] = 1;

        return $this;
    }

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function pullAll(string $field, array $values)
    {
        $this->mongoDbUpdate['$pullAll'][$field] = $values;

        return $this;
    }

    /**
     * @param string      $field
     * @param array|mixed $condition - a condition to specify values to delete, or a value to delete
     *
     * @return $this
     */
    public function pull(string $field, $condition)
    {
        $this->mongoDbUpdate['$pull'][$field] = $condition;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     */
    public function push(string $field, $value)
    {
        $this->mongoDbUpdate['$push'][$field] = $value;
    }

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function pushAll(string $field, array $values)
    {
        $this->push($field, ['$each' => $values]);

        return $this;
    }

    /**
     * @param string $field
     */
    public function currentDate(string $field)
    {
        $this->mongoDbUpdate['$currentDate'][$field] = true;
    }

    /**
     * @param string $field
     */
    public function currentTimestamp(string $field)
    {
        $this->mongoDbUpdate['$currentDate'][$field] = ['$type' => 'timestamp'];
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function increment(string $field, $value)
    {
        $this->mongoDbUpdate['$inc'][$field] = $value;

        return $this;
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function multiply(string $field, $value)
    {
        $this->mongoDbUpdate['$mul'][$field] = $value;

        return $this;
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function min(string $field, $value)
    {
        $this->mongoDbUpdate['$min'][$field] = $value;

        return $this;
    }

    /**
     * @param string    $field
     * @param int|float $value
     *
     * @return $this
     */
    public function max(string $field, $value)
    {
        $this->mongoDbUpdate['$max'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOnInsert(string $field, $value)
    {
        $this->mongoDbUpdate['$setOnInsert'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return $this
     */
    public function set(string $field, $value)
    {
        $this->mongoDbUpdate['$set'][$field] = $value;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function unsetField(string $field)
    {
        $this->mongoDbUpdate['$unset'][$field] = '';

        return $this;
    }
}
