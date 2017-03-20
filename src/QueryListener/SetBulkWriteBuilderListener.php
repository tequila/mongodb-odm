<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Cursor;
use Tequila\MongoDB\DocumentListenerInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\QueryListenerInterface;

class SetBulkWriteBuilderListener implements QueryListenerInterface, DocumentListenerInterface
{
    /**
     * @var BulkWriteBuilderFactory
     */
    private $bulkBuilderFactory;

    /**
     * @var string[]
     */
    private $cursorHashToNamespaceMap;

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
    public function onQueryExecuted($namespace, $filter, array $options, Cursor $cursor)
    {
        $cursor->setDocumentListener($this);
        $this->cursorHashToNamespaceMap[\spl_object_hash($cursor)] = $namespace;
    }

    /**
     * @inheritdoc
     */
    public function onDocument(Cursor $cursor, $document)
    {
        if ($document instanceof BulkWriteBuilderAwareInterface) {
            $cursorHash = \spl_object_hash($cursor);
            if (!array_key_exists($cursorHash, $this->cursorHashToNamespaceMap)) {
                throw new LogicException(
                    'Namespace for $cursor is not found. $cursor must have been tracked by the onQueryExecuted().'
                );
            }
            $namespace = $this->cursorHashToNamespaceMap[$cursorHash];
            $document->setBulkWriteBuilder($this->bulkBuilderFactory->getBulkWriteBuilder($namespace));
        }
    }
}