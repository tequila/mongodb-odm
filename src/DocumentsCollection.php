<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\BulkWriteListenerInterface;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Index;
use Tequila\MongoDB\ODM\Listener\DelegatingBulkWriteListener;
use Tequila\MongoDB\ODM\Listener\DelegatingDocumentListener;
use Tequila\MongoDB\ODM\Listener\DocumentChangesListener;
use Tequila\MongoDB\ODM\Listener\DocumentListenerInterface;
use Tequila\MongoDB\ODM\WriteModel\DocumentAwareWriteModel;
use Tequila\MongoDB\WriteModelInterface;
use Tequila\MongoDB\WriteResult;

class DocumentsCollection
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var DelegatingBulkWriteListener
     */
    private $bulkWriteListener;

    /**
     * @var DelegatingDocumentListener
     */
    private $documentListener;

    /**
     * @var BulkWriteBuilder
     */
    private $bulkWriteBuilder;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
        $this->bulkWriteListener = new DelegatingBulkWriteListener();
        $this->collection->setBulkWriteListener($this->bulkWriteListener);
    }

    /**
     * @param BulkWriteListenerInterface $listener
     */
    public function addBulkWriteListener(BulkWriteListenerInterface $listener)
    {
        $this->bulkWriteListener->addListener($listener);
    }

    /**
     * @param DocumentListenerInterface $listener
     */
    public function addDocumentListener(DocumentListenerInterface $listener)
    {
        $this->getDocumentListener()->addListener($listener);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::aggregate()
     *
     * @param array $pipeline
     * @param array $options
     * @return \Tequila\MongoDB\CursorInterface
     */
    public function aggregate(array $pipeline, array $options = [])
    {
        return $this->collection->aggregate($pipeline, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::bulkWrite()
     *
     * @param WriteModelInterface[] $requests
     * @param array $options
     * @return WriteResult
     */
    public function bulkWrite(array $requests, array $options = [])
    {
        $result = $this->collection->bulkWrite($requests, $options);

        if ($result->isAcknowledged()) {
            // set ids to inserted documents
            foreach ($result->getInsertedIds() as $position => $id) {
                if ($writeModel = $requests[$position] instanceof DocumentAwareWriteModel) {
                    /** @var DocumentAwareWriteModel $writeModel */
                    $writeModel->getDocument()->setId($id);
                }
            }

            // set ids to upserted documents
            foreach ($result->getUpsertedIds() as $position => $id) {
                if ($writeModel = $requests[$position] instanceof DocumentAwareWriteModel) {
                    /** @var DocumentAwareWriteModel $writeModel */
                    $writeModel->getDocument()->setId($id);
                }
            }
        }

        return $result;
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::count()
     *
     * @param array $filter
     * @param array $options
     * @return int
     */
    public function count(array $filter = [], array $options = [])
    {
        return $this->collection->count($filter, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::createIndex()
     *
     * @param array $key
     * @param array $options
     * @return string
     */
    public function createIndex(array $key, array $options = [])
    {
        return $this->collection->createIndex($key, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::createIndexes()
     *
     * @param Index[] $indexes
     * @param array $options
     * @return string[]
     */
    public function createIndexes(array $indexes, array $options = [])
    {
        return $this->collection->createIndexes($indexes, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::deleteMany()
     *
     * @param array $filter
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\DeleteResult
     */
    public function deleteMany(array $filter, array $options = [])
    {
        return $this->collection->deleteMany($filter, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::deleteOne()
     *
     * @param array $filter
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\DeleteResult
     */
    public function deleteOne(array $filter, array $options = [])
    {
        return $this->collection->deleteOne($filter, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::distinct()
     *
     * @param string $fieldName
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function distinct($fieldName, array $filter = [], array $options = [])
    {
        return $this->collection->distinct($fieldName, $filter, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::drop()
     *
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        return $this->collection->drop($options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::dropIndexes()
     *
     * @param array $options
     * @return array
     */
    public function dropIndexes(array $options = [])
    {
        return $this->collection->dropIndexes($options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::dropIndex()
     *
     * @param $indexName
     * @param array $options
     * @return array
     */
    public function dropIndex($indexName, array $options = [])
    {
        return $this->collection->dropIndex($indexName, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::find()
     *
     * @param array $filter
     * @param array $options
     * @return \Tequila\MongoDB\CursorInterface
     */
    public function find(array $filter = [], array $options = [])
    {
        $cursor = $this->collection->find($filter, $options);

        return new DocumentsCursor($cursor, $this->getDocumentListener());
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::findOne()
     *
     * @param array $filter
     * @param array $options
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOne(array $filter = [], array $options = [])
    {
        return $this->collection->findOne($filter, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::findOneAndDelete()
     *
     * @param array $filter
     * @param array $options
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOneAndDelete(array $filter, array $options = [])
    {
        return $this->collection->findOneAndDelete($filter, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::findOneAndReplace()
     *
     * @param array $filter
     * @param array|object $replacement
     * @param array $options
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOneAndReplace(array $filter, $replacement, array $options = [])
    {
        return $this->collection->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::findOneAndUpdate()
     *
     * @param array $filter
     * @param array $update
     * @param array $options
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOneAndUpdate(array $filter, array $update, array $options = [])
    {
        return $this->collection->findOneAndUpdate($filter, $update, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::getCollectionName()
     *
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collection->getCollectionName();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::getDatabaseName()
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->collection->getDatabaseName();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::getNamespace()
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->collection->getNamespace();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::insertMany()
     *
     * @param array|\Traversable $documents
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\InsertManyResult
     */
    public function insertMany($documents, array $options = [])
    {
        return $this->collection->insertMany($documents, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::insertOne()
     *
     * @param array|object $document
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\InsertOneResult
     */
    public function insertOne($document, array $options = [])
    {
        return $this->collection->insertOne($document, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::listIndexes()
     *
     * @return array
     */
    public function listIndexes()
    {
        return $this->collection->listIndexes();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::replaceOne()
     *
     * @param $filter
     * @param $replacement
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\UpdateResult
     */
    public function replaceOne(array $filter, $replacement, array $options = [])
    {
        return $this->collection->replaceOne($filter, $replacement, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::updateMany()
     *
     * @param array $filter
     * @param $update
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\UpdateResult
     */
    public function updateMany(array $filter, $update, array $options = [])
    {
        return $this->collection->updateMany($filter, $update, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Collection::updateOne()
     *
     * @param array $filter
     * @param array|object $update
     * @param array $options
     * @return \Tequila\MongoDB\Write\Result\UpdateResult
     */
    public function updateOne($filter, $update, array $options = [])
    {
        return $this->collection->updateOne($filter, $update, $options);
    }

    /**
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder()
    {
        if (null === $this->bulkWriteBuilder) {
            $this->bulkWriteBuilder = new BulkWriteBuilder($this);
        }

        return $this->bulkWriteBuilder;
    }

    /**
     * @return DelegatingDocumentListener
     */
    private function getDocumentListener()
    {
        if (null === $this->documentListener) {
            $this->documentListener = new DelegatingDocumentListener();
            $this->documentListener->addListener(
                new DocumentChangesListener($this->getBulkWriteBuilder())
            );
        }

        return $this->documentListener;
    }
}