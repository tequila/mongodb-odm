<?php

namespace Tequila\MongoDB\ODM\Repository\Factory;

use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\ODM\Repository\Repository;

class DefaultRepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var Repository[]
     */
    private $repositoriesCache = [];

    /**
     * {@inheritdoc}
     */
    public function getRepository(DocumentManager $documentManager, string $documentClass): Repository
    {
        if (!array_key_exists($documentClass, $this->repositoriesCache)) {
            $metadata = $documentManager->getMetadata($documentClass);
            if (null === $repositoryClass = $metadata->getRepositoryClass()) {
                $repositoryClass = Repository::class;
            }

            $this->repositoriesCache[$documentClass] = new $repositoryClass($documentManager, $documentClass);
        }

        return $this->repositoriesCache[$documentClass];
    }
}
