<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;

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
     * @param Database $database
     * @param BulkWriteBuilderFactory $bulkWriteBuilderFactory
     * @param DocumentRepositoryFactoryInterface $repositoryFactory
     * @param DocumentMetadataFactoryInterface $metadataFactory
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
     * @param string $documentClass
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder($documentClass)
    {
        $metadata = $this->metadataFactory->getDocumentMetadata($documentClass);
        $namespace = $this->database->getDatabaseName() . '.' . $metadata->getCollectionName();

        return $this->bulkWriteBuilderFactory->getBulkWriteBuilder($namespace);
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return Collection
     */
    public function getCollection($collectionName, array $options = [])
    {
        $cacheKey = $collectionName . $this->getCollectionOptionsHash($options);
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
     * @return Collection
     */
    public function getCollectionByDocumentClass($documentClass)
    {
        $metadata = $this->metadataFactory->getDocumentMetadata($documentClass);

        return $this->getCollection($metadata->getCollectionName(), $metadata->getCollectionOptions());
    }

    /**
     * @param string $documentClass
     * @return DocumentRepository
     */
    public function getRepository($documentClass)
    {
        return $this->repositoryFactory->getDocumentRepository($this, $documentClass);
    }

    /**
     * @param array $options
     * @return string
     */
    private function getCollectionOptionsHash(array $options)
    {
        $options = array_filter($options, function ($option) {
            // All valid Collection options are BSON serializable
            return $option instanceof Serializable;
        });

        ksort($options);
        $str = '';
        foreach ($options as $option) {
            if (!$option instanceof  Serializable) {
                // All valid Collection options are BSON serializable
                continue;
            }
            $str = $str . var_export($option->bsonSerialize(), true);
        }

        return md5($str);
    }
}