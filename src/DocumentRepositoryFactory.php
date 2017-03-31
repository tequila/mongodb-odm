<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Exception\LogicException;

class DocumentRepositoryFactory implements DocumentRepositoryFactoryInterface
{
    /**
     * @var DocumentRepository[]
     */
    private $repositoriesCache = [];

    /**
     * @inheritdoc
     */
    public function getDocumentRepository(DocumentManager $documentManager, $repositoryClass)
    {
        if (!array_key_exists($repositoryClass, $this->repositoriesCache)) {
            if (!class_exists($repositoryClass)) {
                throw new LogicException(sprintf('Repository class %s does not exist.', $repositoryClass));
            }

            if (!is_subclass_of($repositoryClass, DocumentRepository::class, true)) {
                throw new LogicException(
                    sprintf(
                        'Custom repository class %s must be a subclass of %s.',
                        $repositoryClass,
                        DocumentRepository::class
                    )
                );
            }

            if (!is_subclass_of($repositoryClass, CustomDocumentRepositoryInterface::class, true)) {
                throw new LogicException(
                    sprintf(
                        'Custom repository class %s must implement %s.',
                        $repositoryClass,
                        CustomDocumentRepositoryInterface::class
                    )
                );
            }

            $collection = $documentManager->getCollection(
                call_user_func([$repositoryClass, 'getCollectionName'])
            );

            $this->repositoriesCache[$repositoryClass] = new $repositoryClass($collection);
        }

        return $this->repositoriesCache[$repositoryClass];
    }
}