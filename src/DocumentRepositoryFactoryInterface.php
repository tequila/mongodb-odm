<?php

namespace Tequila\MongoDB\ODM;

interface DocumentRepositoryFactoryInterface
{
    /**
     * @param DocumentManager $documentManager
     * @param string          $documentClass
     *
     * @return DocumentRepository
     */
    public function getDocumentRepository(DocumentManager $documentManager, $documentClass);
}
