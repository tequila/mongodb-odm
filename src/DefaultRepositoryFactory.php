<?php

namespace Tequila\MongoDB\ODM;

class DefaultRepositoryFactory implements DocumentRepositoryFactoryInterface
{
    /**
     * @var DocumentRepository[]
     */
    private $repositoriesCache = [];

    /**
     * @var DocumentMetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param DocumentMetadataFactoryInterface $metadataFactory
     */
    public function __construct(DocumentMetadataFactoryInterface $metadataFactory)
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

            $metadata = $this->metadataFactory->getDocumentMetadata($documentClass);
            if (null === $repositoryClass = $metadata->getRepositoryClass()) {
                $repositoryClass = DocumentRepository::class;
            }

            $this->repositoriesCache[$documentClass] = new $repositoryClass($collection);
        }

        return $this->repositoriesCache[$documentClass];
    }
}
