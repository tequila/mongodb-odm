<?php

namespace Tequila\MongoDB\ODM\Traits;

use Tequila\MongoDB\ODM\Listener\CollectionListenerInterface;
use Tequila\MongoDB\ODM\Listener\DelegatingCollectionListener;

trait CollectionListenerTrait
{
    /**
     * @var CollectionListenerInterface
     */
    private $collectionListener;

    /**
     * @param CollectionListenerInterface $listener
     */
    public function addCollectionListener(CollectionListenerInterface $listener)
    {
        $this->getCollectionListener()->addListener($listener);
    }

    /**
     * @return DelegatingCollectionListener
     */
    private function getCollectionListener()
    {
        if (null === $this->collectionListener) {
            $this->collectionListener = new DelegatingCollectionListener();
        }

        return $this->collectionListener;
    }
}