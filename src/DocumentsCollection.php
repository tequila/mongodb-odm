<?php

namespace Tequila\MongoDB\ODM;

use Iterator;
use MongoDB\Driver\Cursor;
use Tequila\MongoDB\ODM\Exception\LogicException;

class DocumentsCollection implements Iterator
{
    /**
     * @var Cursor
     */
    private $cursor;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var \Generator
     */
    private $generator;

    /**
     * @var bool
     */
    private $iterationStarted = false;

    /**
     * @var string|int
     */
    private $currentKey;

    /**
     * @var DocumentManagerAwareInterface
     */
    private $currentDocument;

    /**
     * @param DocumentManager $documentManager
     * @param Cursor $cursor
     * @param string $documentClass
     */
    public function __construct(DocumentManager $documentManager, Cursor $cursor, string $documentClass)
    {
        $this->documentManager = $documentManager;
        $this->cursor = $cursor;
        $this->documentClass = $documentClass;

        $proxyClass = $documentManager->getProxyClass($documentClass);
        $cursor->setTypeMap(['root' => $proxyClass, 'document' => 'array', 'array' => 'array']);

        $this->generator = $this->createGenerator();
    }

    /**
     * @return array|object|\MongoDB\BSON\Unserializable
     */
    public function current()
    {
        if (($key = $this->key()) === $this->currentKey) {
            return $this->currentDocument;
        }

        /** @var DocumentManagerAwareInterface $document */
        $document = $this->generator->current();
        $document->setDocumentManager($this->documentManager);
        $this->currentDocument = $document;
        $this->currentKey = $key;

        return $document;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->generator->key();
    }

    /**
     * @return array|\MongoDB\BSON\Unserializable|object
     */
    public function next()
    {
        $this->generator->next();

        return $this->current();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        if ($this->iterationStarted) {
            throw new LogicException(sprintf('%s cannot yield multiple iterators', get_class($this)));
        }
        $this->iterationStarted = true;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->generator->valid();
    }

    /**
     * @return \Generator
     */
    private function createGenerator()
    {
        foreach ($this->cursor as $key => $document) {
            yield $key => $document;
        }
    }
}