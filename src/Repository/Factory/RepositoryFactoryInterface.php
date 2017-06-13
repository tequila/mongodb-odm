<?php

namespace Tequila\MongoDB\ODM\Repository\Factory;

use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\ODM\Repository\Repository;

interface RepositoryFactoryInterface
{
    /**
     * @param DocumentManager $documentManager
     * @param string          $documentClass
     *
     * @return Repository
     */
    public function getDocumentRepository(DocumentManager $documentManager, $documentClass);
}
