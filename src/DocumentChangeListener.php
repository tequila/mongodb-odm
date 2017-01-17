<?php

namespace Tequila\MongoDB\ODM;

class DocumentChangeListener implements DocumentListenerInterface
{
    /**
     * @var DocumentInterface[]
     */
    private $documents = [];

    public function documentFetched(DocumentInterface $document)
    {
        $this->documents[\spl_object_hash($document)] = $document;
    }

    /**
     * @return DocumentInterface[]
     */
    public function getDocuments()
    {
        return $this->documents;
    }
}