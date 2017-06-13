<?php

namespace Tequila\MongoDB\ODM\Repository\Factory;

use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\ODM\Repository\Repository;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;

class DefaultRepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var Repository[]
     */
    private $repositoriesCache = [];

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentRepository(DocumentManager $documentManager, $documentClass)
    {
        if (!array_key_exists($documentClass, $this->repositoriesCache)) {
            $collection = $documentManager->getCollectionByDocumentClass($documentClass);

            $metadata = $this->metadataFactory->getClassMetadata($documentClass);
            if (null === $repositoryClass = $metadata->getRepositoryClass()) {
                $repositoryClass = Repository::class;
            }

            $this->repositoriesCache[$documentClass] = new $repositoryClass($collection);
        }

        return $this->repositoriesCache[$documentClass];
    }
}