<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\Write\Model\DeleteOne;
use Tequila\MongoDB\Write\Model\ReplaceOne;
use Tequila\MongoDB\Write\Model\UpdateOne;
use Tequila\MongoDB\WriteModelInterface;

trait DocumentTrait
{
    /**
     * @var ObjectID|null|mixed
     */
    private $id;

    /**
     * @var array
     */
    private $update = [];

    /**
     * @var array
     */
    private $updateOptions = [];

    /**
     * @var WriteModelInterface|null
     */
    private $writeModel = false;

    /**
     * @return array|object
     */
    abstract public function bsonSerialize();

    /**
     * @param array|object $serialized
     * @return mixed
     */
    abstract public function bsonUnserialize(array $serialized);

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
     * @return WriteModelInterface|null
     */
    public function getWriteModel()
    {
        if (false === $this->writeModel) {
            if ($this->update) {
                $this->writeModel = new UpdateOne(
                    ['_id' => $this->id],
                    $this->update,
                    $this->updateOptions
                );
            } else {
                $this->writeModel = null;
            }
        }

        return $this->writeModel;
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
     * @return bool
     */
    public function isNew()
    {
        return (bool)$this->getId();
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

    /**
     * @param array $options
     * @return $this
     */
    public function update(array $options = [])
    {
        $this->updateOptions = $options;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function replace(array $options = [])
    {
        $this->writeModel = new ReplaceOne(['_id' => $this->id], $this, $options);

        return $this;
    }

    /**
     * @param array $options
     */
    public function delete(array $options = [])
    {
        $this->writeModel = new DeleteOne(['_id' => $this->id], $options);
    }
}