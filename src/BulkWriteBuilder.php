<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Collection;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\WriteModel\DeleteOneDocument;
use Tequila\MongoDB\ODM\WriteModel\DocumentAwareWriteModel;
use Tequila\MongoDB\ODM\WriteModel\InsertOneDocument;
use Tequila\MongoDB\ODM\WriteModel\ReplaceOneDocument;
use Tequila\MongoDB\ODM\WriteModel\UpdateOneDocument;
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
     * @var array
     */
    private static $modelClassToOperationMap = [
        DeleteOneDocument::class => 'delete',
        UpdateOneDocument::class => 'update',
        ReplaceOneDocument::class => 'replace',
    ];

    /**
     * @var WriteModelInterface[]
     */
    private $writeModels = [];

    /**
     * Flushes bulk write to MongoDB
     *
     * @param Collection $collection
     * @param array $bulkWriteOptions
     * @return \Tequila\MongoDB\WriteResult
     */
    public function flush(Collection $collection, array $bulkWriteOptions = [])
    {
        if (0 === count($this->writeModels)) {
            throw new LogicException('BulkWriteBuilder does not contain any write operations.');
        }

        $result = $collection->bulkWrite($this->writeModels, $bulkWriteOptions);
        if ($result->isAcknowledged()) {
            // set ids to inserted documents
            foreach ($result->getInsertedIds() as $position => $id) {
                if ($writeModel = $this->writeModels[$position] instanceof DocumentAwareWriteModel) {
                    /** @var DocumentAwareWriteModel $writeModel */
                    $writeModel->getDocument()->setId($id);
                }
            }

            // set ids to upserted documents
            foreach ($result->getUpsertedIds() as $position => $id) {
                if ($writeModel = $this->writeModels[$position] instanceof DocumentAwareWriteModel) {
                    /** @var DocumentAwareWriteModel $writeModel */
                    $writeModel->getDocument()->setId($id);
                }
            }
        }

        $this->writeModels = [];

        return $result;
    }

    /**
     * @param array $filter
     * @param array $options
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
     * @return $this
     */
    public function deleteOne(array $filter, array $options = [])
    {
        $this->writeModels[] = new DeleteOne($filter, $options);

        return $this;
    }

    /**
     * @param array $documents
     * @return $this
     */
    public function insertMany(array $documents)
    {
        $this->writeModels[] = new InsertMany($documents);

        return $this;
    }

    /**
     * @param $document
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
     * @return $this
     */
    public function updateOne(array $filter, array $update, array $options = [])
    {
        $this->writeModels[] = new UpdateOne($filter, $update, $options);

        return $this;
    }

    /**
     * @param array $filter
     * @param array|object $replacement
     * @param array $options
     * @return $this
     */
    public function replaceOne(array $filter, $replacement, array $options = [])
    {
        $this->writeModels[] = new ReplaceOne($filter, $replacement, $options);

        return $this;
    }

    /**
     * @param DocumentInterface $document
     * @return DeleteOneDocument
     */
    public function deleteDocument(DocumentInterface $document)
    {
        if (null === $document->getId()) {
            throw new InvalidArgumentException('Attempt to delete a new document.');
        }

        $key = \spl_object_hash($document);

        if (!array_key_exists($key, $this->writeModels)) {
            $this->writeModels[$key] = new DeleteOneDocument($document);
        } else {
            $this->ensureOneOperationPerDocument($document, 'delete');
        }

        return $this->writeModels[$key];
    }

    /**
     * @param DocumentInterface $document
     */
    public function insertDocument(DocumentInterface $document)
    {
        $this->writeModels[\spl_object_hash($document)] = new InsertOneDocument($document);
    }

    /**
     * @param DocumentInterface $document
     * @return UpdateOneDocument
     */
    public function updateDocument(DocumentInterface $document)
    {
        if (null === $document->getId()) {
            throw new InvalidArgumentException('Attempt to update a new document.');
        }

        $key = \spl_object_hash($document);

        if (!array_key_exists($key, $this->writeModels)) {
            $this->writeModels[$key] = new UpdateOneDocument($document);
        } else {
            $this->ensureOneOperationPerDocument($document, 'update');
        }

        return $this->writeModels[$key];
    }

    /**
     * @param DocumentInterface $document
     * @return ReplaceOneDocument
     */
    public function replaceDocument(DocumentInterface $document)
    {
        if (null === $document->getId()) {
            throw new InvalidArgumentException('Attempt to replace a new document.');
        }

        $key = \spl_object_hash($document);

        if (!array_key_exists($key, $this->writeModels)) {
            $this->writeModels[$key] = new ReplaceOneDocument($document);
        } else {
            $this->ensureOneOperationPerDocument($document, 'delete');
        }

        return $this->writeModels[$key];
    }

    /**
     * @param DocumentInterface $document
     * @param string $operation - "delete", "update" or "replace"
     */
    private function ensureOneOperationPerDocument(DocumentInterface $document, $operation)
    {
        $key = \spl_object_hash($document);
        $firstOperation = self::getOperationByModelClass(get_class($this->writeModels[$key]));

        if ($firstOperation !== $operation) {
            throw new LogicException(
                sprintf(
                    'Trying to %s and %s the same document in one request.',
                    $firstOperation,
                    $operation
                )
            );
        }
    }

    /**
     * @param $builderClass
     * @return string
     */
    private static function getOperationByModelClass($builderClass)
    {
        if (isset(self::$modelClassToOperationMap[$builderClass])) {
            return self::$modelClassToOperationMap[$builderClass];
        }

        throw new LogicException(
            sprintf('Unknown write model builder class "%s".', $builderClass)
        );
    }
}