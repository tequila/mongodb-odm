<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\WriteModel\DeleteOneDocument;
use Tequila\MongoDB\ODM\WriteModel\InsertOneDocument;
use Tequila\MongoDB\ODM\WriteModel\ReplaceOneDocument;
use Tequila\MongoDB\ODM\WriteModel\UpdateOneDocument;
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
     * @var DocumentsCollection
     */
    private $collection;

    /**
     * @var WriteModelInterface[]
     */
    private $writeModels = [];

    /**
     * @param DocumentsCollection $collection
     */
    public function __construct(DocumentsCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Flushes bulk write to MongoDB
     *
     * @param array $bulkWriteOptions
     * @return \Tequila\MongoDB\WriteResult
     */
    public function flush(array $bulkWriteOptions = [])
    {
        $result = $this->collection->bulkWrite($this->writeModels, $bulkWriteOptions);
        $this->writeModels = [];

        return $result;
    }

    /**
     * @param DocumentInterface $document
     * @return DeleteOneDocument
     */
    public function delete(DocumentInterface $document)
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
    public function insert(DocumentInterface $document)
    {
        $this->writeModels[\spl_object_hash($document)] = new InsertOneDocument($document);
    }

    /**
     * @param DocumentInterface $document
     * @return UpdateOneDocument
     */
    public function update(DocumentInterface $document)
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
    public function replace(DocumentInterface $document)
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
    private function ensureOneOperationPerDocument(DocumentInterface $document, $operation) {
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