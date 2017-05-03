<?php

namespace Tequila\MongoDB\ODM\QueryListener;

use Tequila\MongoDB\DocumentListenerInterface;
use Tequila\MongoDB\ODM\DocumentManagerAwareInterface;
use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\QueryCursor;
use Tequila\MongoDB\QueryListenerInterface;

class SetDocumentManagerListener implements QueryListenerInterface, DocumentListenerInterface
{
    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @param DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function onQueryExecuted($namespace, $filter, array $options, QueryCursor $cursor)
    {
        $cursor->addDocumentListener($this);
    }

    /**
     * {@inheritdoc}
     */
    public function onDocument(QueryCursor $cursor, $document)
    {
        if ($document instanceof DocumentManagerAwareInterface) {
            $document->setDocumentManager($this->documentManager);
        }
    }
}
