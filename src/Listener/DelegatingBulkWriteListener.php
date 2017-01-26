<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\BulkWriteListenerInterface;

class DelegatingBulkWriteListener implements BulkWriteListenerInterface
{
    /**
     * @var BulkWriteListenerInterface[]
     */
    private $listeners = [];

    /**
     * @param BulkWriteListenerInterface $listener
     */
    public function addListener(BulkWriteListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * @inheritdoc
     */
    public function onInsert($document)
    {
        foreach ($this->listeners as $listener) {
            $listener->onInsert($document);
        }
    }

    /**
     * @inheritdoc
     */
    public function onUpdate($filter, $update, array $options = [])
    {
        foreach ($this->listeners as $listener) {
            $listener->onUpdate($filter, $update, $options);
        }
    }

    /**
     * @inheritdoc
     */
    public function onDelete($filter, array $options = [])
    {
        foreach ($this->listeners as $listener) {
            $listener->onDelete($filter, $options);
        }
    }
}