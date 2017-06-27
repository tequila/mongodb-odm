<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\Serializable;
use MongoDB\Collection;
use MongoDB\Database;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;
use Tequila\MongoDB\ODM\Proxy\Factory\ProxyFactoryInterface;
use Tequila\MongoDB\ODM\Proxy\ProxyInterface;
use Tequila\MongoDB\ODM\Repository\Repository;
use Tequila\MongoDB\ODM\Repository\Factory\RepositoryFactoryInterface;

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
     * @var RepositoryFactoryInterface
     */
    private $repositoryFactory;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var ProxyFactoryInterface
     */
    private $proxyFactory;

    /**
     * @param Database                   $database
     * @param BulkWriteBuilderFactory    $bulkWriteBuilderFactory
     * @param RepositoryFactoryInterface $repositoryFactory
     * @param MetadataFactoryInterface   $metadataFactory
     * @param ProxyFactoryInterface      $proxyFactory
     */
    public function __construct(
        Database $database,
        BulkWriteBuilderFactory $bulkWriteBuilderFactory,
        RepositoryFactoryInterface $repositoryFactory,
        MetadataFactoryInterface $metadataFactory,
        ProxyFactoryInterface $proxyFactory
    ) {
        $this->database = $database;
        $this->bulkWriteBuilderFactory = $bulkWriteBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->metadataFactory = $metadataFactory;
        $this->proxyFactory = $proxyFactory;
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
    public function getBulkWriteBuilder(string $documentClass): BulkWriteBuilder
    {
        $metadata = $this->metadataFactory->getClassMetadata($documentClass);

        return $this->bulkWriteBuilderFactory->getBulkWriteBuilder($this, $metadata->getCollectionName());
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return Collection
     */
    public function getCollection($collectionName, array $options = []): Collection
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
    public function getCollectionByDocumentClass($documentClass): Collection
    {
        $metadata = $this->metadataFactory->getClassMetadata($documentClass);

        return $this->getCollection($metadata->getCollectionName(), $metadata->getCollectionOptions());
    }

    /**
     * @param $documentClass
     *
     * @return ClassMetadata
     */
    public function getMetadata($documentClass): ClassMetadata
    {
        return $this->metadataFactory->getClassMetadata($documentClass);
    }

    /**
     * @param string $documentClass
     * @param bool   $rootProxy
     *
     * @return string
     */
    public function getProxyClass(string $documentClass, bool $rootProxy = true): string
    {
        return $this->proxyFactory->getProxyClass($documentClass, $rootProxy);
    }

    /**
     * @param string $documentClass
     *
     * @return Repository
     */
    public function getRepository($documentClass): Repository
    {
        return $this->repositoryFactory->getRepository($this, $documentClass);
    }

    /**
     * @param DocumentInterface $document
     */
    public function persist(DocumentInterface $document)
    {
        if (!$document->getMongoId()) {
            $this->insert($document);
        } elseif (!$document instanceof ProxyInterface) {
            $this->replace($document, ['upsert' => true]);
        }
    }

    /**
     * @param DocumentInterface $document
     */
    public function insert(DocumentInterface $document)
    {
        $this->getBulkWriteBuilder(self::getDocumentClass($document))->insertOne($document);
    }

    /**
     * @param DocumentInterface $document
     * @param array             $options
     */
    public function replace(DocumentInterface $document, array $options = [])
    {
        if (!$document->getMongoId()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot replace new document: %s() method expects $document to have an id.',
                    __METHOD__
                )
            );
        }

        $this->getBulkWriteBuilder(self::getDocumentClass($document))->replaceOne(
            ['_id' => $document->getMongoId()],
            $document,
            $options
        );
    }

    /**
     * @param DocumentInterface $document
     * @param array             $options
     */
    public function delete(DocumentInterface $document, array $options = [])
    {
        if (!$document->getMongoId()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot delete new document: %s() method expects $document to have an id.',
                    __METHOD__
                )
            );
        }

        $this->getBulkWriteBuilder(self::getDocumentClass($document))->deleteOne(
            ['_id' => $document->getMongoId()],
            $options
        );
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
            $str .= serialize($option->bsonSerialize());
        }

        return md5($str);
    }

    /**
     * @param DocumentInterface $document
     *
     * @return string
     */
    private static function getDocumentClass(DocumentInterface $document): string
    {
        return $document instanceof ProxyInterface ? $document->getRealClass() : get_class($document);
    }
}
