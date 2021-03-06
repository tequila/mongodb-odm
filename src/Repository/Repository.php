<?php

namespace Tequila\MongoDB\ODM\Repository;

use MongoDB\BSON\ObjectID;
use MongoDB\Collection;
use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\ODM\DocumentManagerAwareInterface;
use Tequila\MongoDB\ODM\DocumentsCollection;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;

class Repository
{
    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var string
     */
    protected $documentClass;

    /**
     * @param DocumentManager $documentManager
     * @param string          $documentClass
     */
    final public function __construct(DocumentManager $documentManager, string $documentClass)
    {
        $this->documentManager = $documentManager;
        $this->documentClass = $documentClass;
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return DocumentsCollection
     */
    public function findAll(array $filter = [], array $options = []): DocumentsCollection
    {
        $cursor = $this->getCollection()->find($filter, $options);

        return new DocumentsCollection($this->documentManager, $cursor, $this->documentClass);
    }

    /**
     * @param array $ids
     *
     * @return DocumentsCollection|iterable
     */
    public function findAllByIds(array $ids): iterable
    {
        if (!$ids) {
            throw new InvalidArgumentException('$ids array cannot be empty.');
        }

        return $this->findAll(['_id' => ['$in' => array_values($ids)]]);
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOne(array $filter = [], array $options = [])
    {
        $documents = $this->findAll($filter, ['limit' => 1] + $options);

        return $documents->valid() ? $documents->current() : null;
    }

    /**
     * @param ObjectID|mixed $id
     * @param array          $options
     *
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOneById($id, array $options = [])
    {
        return $this->findOne(['_id' => $id], $options);
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return object
     */
    public function findOneAndDelete(array $filter, array $options = [])
    {
        $options['typeMap'] = [
            'root' => $this->documentManager->getProxyClass($this->documentClass),
            'document' => 'array',
        ];

        $document = $this->getCollection()->findOneAndDelete($filter, $options);
        if ($document instanceof DocumentManagerAwareInterface) {
            $document->setManager($this->documentManager);
        }

        return $document;
    }

    /**
     * @param array $filter
     * @param array $update
     * @param array $options
     *
     * @return object
     */
    public function findOneAndUpdate(array $filter, array $update, array $options = [])
    {
        $options['typeMap'] = [
            'root' => $this->documentManager->getProxyClass($this->documentClass),
            'document' => 'array',
        ];

        $document = $this->getCollection()->findOneAndUpdate($filter, $update, $options);
        if ($document instanceof DocumentManagerAwareInterface) {
            $document->setManager($this->documentManager);
        }

        return $document;
    }

    /**
     * @param array        $filter
     * @param array|object $replacement
     * @param array        $options
     *
     * @return object
     */
    public function findOneAndReplace(array $filter, $replacement, array $options = [])
    {
        $options['typeMap'] = [
            'root' => $this->documentManager->getProxyClass($this->documentClass),
            'document' => 'array',
        ];

        $document = $this->getCollection()->findOneAndReplace($filter, $replacement, $options);
        if ($document instanceof DocumentManagerAwareInterface) {
            $document->setManager($this->documentManager);
        }

        return $document;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->documentManager->getCollectionByDocumentClass($this->documentClass);
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager(): DocumentManager
    {
        return $this->documentManager;
    }
}
