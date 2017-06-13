<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\Operation\BulkWrite;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\WriteModelInterface;

class BulkWriteBuilder
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var WriteModelInterface[]
     */
    private $writeModels = [];

    /**
     * @param DocumentManager $documentManager
     * @param string $collectionName
     */
    public function __construct(DocumentManager $documentManager, string $collectionName)
    {
        $this->documentManager = $documentManager;
        $this->collectionName = $collectionName;
    }

    /**
     * @param array|WriteModelInterface $writeModel
     * @return $this
     */
    public function addWriteModel($writeModel)
    {
        if (is_array($writeModel) || $writeModel instanceof WriteModelInterface) {
            $this->writeModels[] = $writeModel;

            return $this;
        }

        throw new InvalidArgumentException(
            sprintf(
                '$write model must be an array in format %s or an instance of %s.',
                '[$operationType => $arrayOfArguments]',
                WriteModelInterface::class
            )
        );
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->writeModels);
    }

    public function flush(array $bulkWriteOptions = [])
    {
        if (0 === count($this->writeModels)) {
            throw new LogicException('BulkWriteBuilder does not contain any write operations.');
        }

        $writeModels = array_map(function($writeModel) {
            if ($writeModel instanceof WriteModelInterface) {
                return $writeModel->toArray();
            }

            return $writeModel;
        }, $this->writeModels);

        $collection = $this->documentManager->getCollection($this->collectionName);
        $result = $collection->bulkWrite($writeModels, $bulkWriteOptions);
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
        $this->writeModels[] = [BulkWrite::DELETE_MANY => [$filter, $options]];

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
        $this->writeModels[] = [BulkWrite::DELETE_ONE => [$filter, $options]];

        return $this;
    }

    /**
     * @param array $documents
     *
     * @return $this
     */
    public function insertMany(array $documents)
    {
        foreach ($documents as $document) {
            $this->writeModels[] = [BulkWrite::INSERT_ONE => [$document]];
        }

        return $this;
    }

    /**
     * @param $document
     *
     * @return $this
     */
    public function insertOne($document)
    {
        $this->writeModels[] = [BulkWrite::INSERT_ONE => [$document]];

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
        $this->writeModels[] = [BulkWrite::UPDATE_MANY => [$filter, $update, $options]];

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
        $this->writeModels[] = [BulkWrite::UPDATE_ONE => [$filter, $update, $options]];

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
        $this->writeModels[] = [BulkWrite::REPLACE_ONE => [$filter, $replacement, $options]];

        return $this;
    }
}
