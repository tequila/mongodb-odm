<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\ODM\DocumentsCollection;

class DelegatingCollectionListener implements CollectionListenerInterface
{
    /**
     * @var CollectionListenerInterface[]
     */
    private $listeners = [];

    /**
     * @param CollectionListenerInterface $listener
     */
    public function addListener(CollectionListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param DocumentsCollection $collection
     */
    public function collectionSelected(DocumentsCollection $collection)
    {
        foreach ($this->listeners as $listener) {
            $listener->collectionSelected($collection);
        }
    }
}