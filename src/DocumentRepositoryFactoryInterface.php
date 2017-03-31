<?php

namespace Tequila\MongoDB\ODM;

interface DocumentRepositoryFactoryInterface
{
    /**
     * @param DocumentManager $documentManager
     * @param string $repositoryClass
     * @return DocumentRepository
     */
    public function getDocumentRepository(DocumentManager $documentManager, $repositoryClass);
}