<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\DocumentListenerInterface;
use Tequila\MongoDB\ODM\BulkWriteBuilder;
use Tequila\MongoDB\ODM\DocumentInterface;

class DocumentChangesListener implements DocumentListenerInterface
{
    /**
     * @var BulkWriteBuilder[]
     */
    private $bulkWriteBuilder;

    /**
     * @param BulkWriteBuilder $bulkWriteBuilder
     */
    public function __construct(BulkWriteBuilder $bulkWriteBuilder)
    {
        $this->bulkWriteBuilder = $bulkWriteBuilder;
    }

    /**
     * @param DocumentInterface $document
     */
    public function onDocument($document)
    {
        if ($document instanceof DocumentInterface) {
            $document->setBulkWriteBuilder($this->bulkWriteBuilder);
        }
    }
}