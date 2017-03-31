<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Serializable;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\Database;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;

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
     * @param Database $database
     * @param BulkWriteBuilderFactory $bulkWriteBuilderFactory
     * @param DocumentRepositoryFactoryInterface $repositoryFactory
     */
    public function __construct(
        Database $database,
        BulkWriteBuilderFactory $bulkWriteBuilderFactory,
        DocumentRepositoryFactoryInterface $repositoryFactory
    ) {
        $this->database = $database;
        $this->bulkWriteBuilderFactory = $bulkWriteBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @param string $collectionName
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder($collectionName)
    {
        if (!$collectionName) {
            throw new InvalidArgumentException('$collectionName cannot be empty.');
        }
        $namespace = $this->database->getDatabaseName() . '.' . $collectionName;

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
     * @param string $repositoryClass
     * @return DocumentRepository
     */
    public function getRepository($repositoryClass)
    {
        return $this->repositoryFactory->getDocumentRepository($this, $repositoryClass);
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