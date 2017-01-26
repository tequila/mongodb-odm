<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\ODM\Listener\DocumentListenerInterface;

class DocumentsCursor implements CursorInterface
{
    /**
     * @var DocumentListenerInterface
     */
    private $listener;

    /**
     * @var CursorInterface
     */
    private $wrappedCursor;

    /**
     * @param CursorInterface $wrappedCursor
     * @param DocumentListenerInterface $listener
     */
    public function __construct(CursorInterface $wrappedCursor, DocumentListenerInterface $listener)
    {
        $this->wrappedCursor = $wrappedCursor;
        $this->listener = $listener;
    }

    public function current()
    {
        $document = $this->wrappedCursor->current();

        if ($this->listener && $document instanceof DocumentInterface) {
            $this->listener->documentFetched($document);
        }

        return $document;
    }

    public function key()
    {
        return $this->wrappedCursor->key();
    }

    public function next()
    {
        return $this->wrappedCursor->valid();
    }

    public function rewind()
    {
        $this->wrappedCursor->rewind();
    }

    public function valid()
    {
        return $this->wrappedCursor->valid();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->wrappedCursor->getId();
    }

    /**
     * @inheritdoc
     */
    public function getServer()
    {
        return $this->wrappedCursor->getServer();
    }

    /**
     * @inheritdoc
     */
    public function isDead()
    {
        return $this->wrappedCursor->isDead();
    }

    /**
     * @inheritdoc
     */
    public function setTypeMap(array $typeMap)
    {
        $this->wrappedCursor->setTypeMap($typeMap);
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return iterator_to_array($this);
    }
}