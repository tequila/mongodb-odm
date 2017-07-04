<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use MongoDB\Operation\BulkWrite;
use Tequila\MongoDB\ODM\Proxy\RootProxyInterface;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;
use Tequila\MongoDB\ODM\WriteModelInterface;

trait RootProxyTrait
{
    use DocumentManagerAwareTrait;
    use RealClassTrait;

    /**
     * @var bool
     */
    private $addedToBulk = false;

    /**
     * @var array
     */
    private $mongoDbUpdate = [];

    /**
     * @var array
     */
    private $mongoDbOptions = [];

    /**
     * @return mixed
     */
    abstract public function getMongoId();

    /**
     * @return RootProxyInterface|$this
     */
    public function getRootProxy(): RootProxyInterface
    {
        /* @var RootProxyInterface $this */
        return $this;
    }

    /**
     * @param string $dbFieldName
     *
     * @return string
     */
    public function getPathInDocument(string $dbFieldName): string
    {
        return $dbFieldName;
    }

    /**
     * @return UpdateBuilderInterface
     */
    public function update(): UpdateBuilderInterface
    {
        if (!$this->addedToBulk) {
            /* @var WriteModelInterface|UpdateBuilderInterface $this */
            $this->documentManager->getBulkWriteBuilder(parent::class)->addWriteModel($this);
            $this->addedToBulk = true;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            BulkWrite::UPDATE_ONE => [
                ['_id' => $this->getMongoId()],
                $this->mongoDbUpdate,
                $this->mongoDbOptions,
            ],
        ];
    }

    /**
     * @param string $field
     * @param array  $values
     *
     * @return $this
     */
    public function addAllToSet(string $field, array $values)
    {
        $this->update()->addToSet($field, ['$each' => $values]);

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
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
        $this->mongoDbUpdate['$pull'][$field] = $condition;

        return $this;
    }

    /**
     * @param string $field
     * @param mixed  $value
     */
    public function push(string $field, $value)
    {
        $this->update();
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
        $this->update()->push($field, ['$each' => $values]);

        return $this;
    }

    /**
     * @param string $field
     */
    public function currentDate(string $field)
    {
        $this->update();
        $this->mongoDbUpdate['$currentDate'][$field] = true;
    }

    /**
     * @param string $field
     */
    public function currentTimestamp(string $field)
    {
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
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
        $this->update();
        $this->mongoDbUpdate['$unset'][$field] = '';

        return $this;
    }
}
