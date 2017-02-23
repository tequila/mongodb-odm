<?php

namespace Tequila\MongoDB\ODM\Listener;


use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\ODM\BulkWriteBuilderFactory;
use Tequila\MongoDB\Server;

class DocumentChangeTracker implements QueryListenerInterface
{
    /**
     * @var DocumentChangesListener[]
     */
    private $documentListeners = [];

    /**
     * @var BulkWriteBuilderFactory
     */
    private $builderFactory;

    /**
     * @param BulkWriteBuilderFactory $builderFactory
     */
    public function __construct(BulkWriteBuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    public function beforeQuery(Server $server, $namespace, $filter, $options)
    {
    }

    public function afterQuery(Server $server, $namespace, $filter, $options, CursorInterface $cursor)
    {
        if (!isset($this->documentListeners[$namespace])) {
            $bulkBuilder = $this->builderFactory->getBulkWriteBuilder($namespace);
            $this->documentListeners[$namespace] = new DocumentChangesListener($bulkBuilder);
        }

        $cursor->setDocumentListener($this->documentListeners[$namespace]);
    }
}