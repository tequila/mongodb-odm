<?php

namespace Tequila\MongoDB\ODM;

interface DocumentManagerAwareInterface
{
    /**
     * @param DocumentManager $documentManager
     */
    public function setManager(DocumentManager $documentManager);
}
