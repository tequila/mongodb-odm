<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\DocumentInterface;

class DocumentManager
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var BulkWriteBuilderFactory
     */
    private $bulkWriteBuilderFactory;

    /**
     * @var Collection[]
     */
    private $collectionsCache = [];

    /**
     * @var DocumentRepositoryFactoryInterface
     */
    private $repositoryFactory;

    /**
     * @var DocumentMetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param Database                           $database
     * @param BulkWriteBuilderFactory            $bulkWriteBuilderFactory
     * @param DocumentRepositoryFactoryInterface $repositoryFactory
     * @param DocumentMetadataFactoryInterface   $metadataFactory
     */
    public function __construct(
        Database $database,
        BulkWriteBuilderFactory $bulkWriteBuilderFactory,
        DocumentRepositoryFactoryInterface $repositoryFactory,
        DocumentMetadataFactoryInterface $metadataFactory
    ) {
        $this->database = $database;
        $this->bulkWriteBuilderFactory = $bulkWriteBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param DocumentInterface $document
     *
     * @return WriteModel\DeleteOneDocument
     */
    public function deleteDocument(DocumentInterface $document)
    {
        return $this->getBulkWriteBuilder(get_class($document))->deleteDocument($document);
    }

    /**
     * @param array $bulkWriteOptions
     */
    public function flush(array $bulkWriteOptions = [])
    {
        foreach ($this->bulkWriteBuilderFactory->getBulkWriteBuilders() as $builder) {
            if ($builder->count()) {
                $builder->flush($bulkWriteOptions);
            }
        }
    }

    /**
     * @param string $documentClass
     *
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder($documentClass)
    {
        $metadata = $this->metadataFactory->getDocumentMetadata($documentClass);
        $namespace = $this->database->getDatabaseName().'.'.$metadata->getCollectionName();

        return $this->bulkWriteBuilderFactory->getBulkWriteBuilder($namespace);
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return Collection
     */
    public function getCollection($collectionName, array $options = [])
    {
        $cacheKey = $collectionName.$this->getCollectionOptionsHash($options);
        if (!array_key_exists($cacheKey, $this->collectionsCache)) {
            $this->collectionsCache[$cacheKey] = $this->database->selectCollection(
                $collectionName,
                $options
            );
        }

        return $this->collectionsCache[$cacheKey];
    }

    /**
     * @param string $documentClass
     *
     * @return Collection
     */
    public function getCollectionByDocumentClass($documentClass)
    {
        $metadata = $this->metadataFactory->getDocumentMetadata($documentClass);

        return $this->getCollection($metadata->getCollectionName(), $metadata->getCollectionOptions());
    }

    /**
     * @param $documentClass
     *
     * @return DocumentMetadata
     */
    public function getMetadata($documentClass)
    {
        return $this->metadataFactory->getDocumentMetadata($documentClass);
    }

    /**
     * @param string $documentClass
     *
     * @return DocumentRepository
     */
    public function getRepository($documentClass)
    {
        return $this->repositoryFactory->getDocumentRepository($this, $documentClass);
    }

    /**
     * @param DocumentInterface $document
     */
    public function insertDocument(DocumentInterface $document)
    {
        $this->getBulkWriteBuilder(get_class($document))->insertDocument($document);
    }

    /**
     * @param DocumentInterface $document
     */
    public function persist(DocumentInterface $document)
    {
        if (!$document->getId()) {
            $this->insertDocument($document);
        } else {
            $this->getBulkWriteBuilder(get_class($document))->replaceOne(
                ['_id' => $document->getId()],
                $document,
                ['upsert' => true]
            );
        }
    }

    /**
     * @param DocumentInterface $document
     *
     * @return WriteModel\ReplaceOneDocument
     */
    public function replaceDocument(DocumentInterface $document)
    {
        return $this->getBulkWriteBuilder(get_class($document))->replaceDocument($document);
    }

    /**
     * @param DocumentInterface $document
     *
     * @return WriteModel\UpdateOneDocument
     */
    public function updateDocument(DocumentInterface $document)
    {
        return $this->getBulkWriteBuilder(get_class($document))->updateDocument($document);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    private function getCollectionOptionsHash(array $options)
    {
        ksort($options);
        $str = '';
        foreach ($options as $option) {
            if (!$option instanceof  Serializable) {
                // All valid Collection options are BSON serializable
                continue;
            }
            $str .= var_export($option->bsonSerialize(), true);
        }

        return md5($str);
    }
}
