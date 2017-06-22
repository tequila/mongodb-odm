<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use Tequila\MongoDB\ODM\DocumentManager;

trait DocumentManagerAwareTrait
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @param DocumentManager $documentManager
     */
    public function setManager(DocumentManager $documentManager): void
    {
        $this->documentManager = $documentManager;
    }
}
