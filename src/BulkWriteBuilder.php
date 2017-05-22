<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\Driver\WriteConcern;
use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\Manager;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\Write\Model\DeleteMany;
use Tequila\MongoDB\Write\Model\DeleteOne;
use Tequila\MongoDB\Write\Model\InsertMany;
use Tequila\MongoDB\Write\Model\InsertOne;
use Tequila\MongoDB\Write\Model\ReplaceOne;
use Tequila\MongoDB\Write\Model\UpdateMany;
use Tequila\MongoDB\Write\Model\UpdateOne;
use Tequila\MongoDB\WriteModelInterface;

class BulkWriteBuilder
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var WriteModelInterface[]
     */
    private $writeModels = [];

    /**
     * @param Manager $manager
     * @param string  $namespace
     */
    public function __construct(Manager $manager, $namespace)
    {
        $this->manager = $manager;
        $this->namespace = $namespace;
    }

    /**
     * @param WriteModelInterface $writeModel
     */
    public function add(WriteModelInterface $writeModel)
    {
        $this->writeModels[] = $writeModel;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->writeModels);
    }

    /**
     * Flushes bulk write to MongoDB.
     *
     * @param array $bulkWriteOptions
     *
     * @return \Tequila\MongoDB\WriteResult
     */
    public function flush(array $bulkWriteOptions = [])
    {
        if (0 === count($this->writeModels)) {
            throw new LogicException('BulkWriteBuilder does not contain any write operations.');
        }

        $writeConcern = null;
        if (isset($bulkWriteOptions['writeConcern'])) {
            if (!$bulkWriteOptions['writeConcern'] instanceof WriteConcern) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Option "writeConcern" is expected to be "%s", "%s" given.',
                        WriteConcern::class,
                        \Tequila\MongoDB\getType($bulkWriteOptions['writeConcern'])
                    )
                );
            }

            $writeConcern = $bulkWriteOptions['writeConcern'];
            unset($bulkWriteOptions['writeConcern']);
        }

        $bulkWrite = new BulkWrite($this->writeModels, $bulkWriteOptions);
        $result = $this->manager->executeBulkWrite($this->namespace, $bulkWrite, $writeConcern);
        $this->writeModels = [];

        return $result;
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return $this
     */
    public function deleteMany(array $filter, array $options = [])
    {
        $this->writeModels[] = new DeleteMany($filter, $options);

        return $this;
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return $this
     */
    public function deleteOne(array $filter, array $options = [])
    {
        $this->writeModels[] = new DeleteOne($filter, $options);

        return $this;
    }

    /**
     * @param array $documents
     *
     * @return $this
     */
    public function insertMany(array $documents)
    {
        $this->writeModels[] = new InsertMany($documents);

        return $this;
    }

    /**
     * @param $document
     *
     * @return $this
     */
    public function insertOne($document)
    {
        $this->writeModels[] = new InsertOne($document);

        return $this;
    }

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     *
     * @return $this
     */
    public function updateMany(array $filter, array $update, array $options = [])
    {
        $this->writeModels[] = new UpdateMany($filter, $update, $options);

        return $this;
    }

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     *
     * @return $this
     */
    public function updateOne(array $filter, array $update, array $options = [])
    {
        $this->writeModels[] = new UpdateOne($filter, $update, $options);

        return $this;
    }

    /**
     * @param array        $filter
     * @param array|object $replacement
     * @param array        $options
     *
     * @return $this
     */
    public function replaceOne(array $filter, $replacement, array $options = [])
    {
        $this->writeModels[] = new ReplaceOne($filter, $replacement, $options);

        return $this;
    }
}
