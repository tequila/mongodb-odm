<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\DocumentListenerInterface;
use Tequila\MongoDB\QueryCursor;
use Tequila\MongoDB\QueryListenerInterface;

class SetBulkWriteBuilderListener implements QueryListenerInterface, DocumentListenerInterface
{
    /**
     * @var BulkWriteBuilderFactory
     */
    private $bulkBuilderFactory;

    /**
     * @param BulkWriteBuilderFactory $bulkBuilderFactory
     */
    public function __construct(BulkWriteBuilderFactory $bulkBuilderFactory)
    {
        $this->bulkBuilderFactory = $bulkBuilderFactory;
    }

    /**
     * @inheritdoc
     */
    public function onQueryExecuted($namespace, $filter, array $options, QueryCursor $cursor)
    {
        $cursor->setDocumentListener($this);
    }

    /**
     * @inheritdoc
     */
    public function onDocument(QueryCursor $cursor, $document)
    {
        if ($document instanceof BulkWriteBuilderAwareInterface) {
            $document->setBulkWriteBuilder(
                $this->bulkBuilderFactory->getBulkWriteBuilder($cursor->getNamespace())
            );
        }
    }
}