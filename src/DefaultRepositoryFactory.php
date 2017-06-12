<?php

namespace Tequila\MongoDB\ODM;

class DefaultRepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var DocumentRepository[]
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
                $repositoryClass = DocumentRepository::class;
            }

            $this->repositoriesCache[$documentClass] = new $repositoryClass($collection);
        }

        return $this->repositoriesCache[$documentClass];
    }
}
