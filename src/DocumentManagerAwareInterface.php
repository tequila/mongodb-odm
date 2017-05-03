<?php

namespace Tequila\MongoDB\ODM;

interface DocumentManagerAwareInterface
{
    /**
     * @param DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager);
}
