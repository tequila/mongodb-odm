<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\ODM\DocumentInterface;

class DelegatingDocumentListener implements DocumentListenerInterface
{
    /**
     * @var DocumentListenerInterface[]
     */
    private $listeners = [];

    /**
     * @param DocumentListenerInterface $listener
     */
    public function addListener(DocumentListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @inheritdoc
     */
    public function documentFetched(DocumentInterface $document)
    {
        foreach ($this->listeners as $listener) {
            $listener->documentFetched($document);
        }
    }
}