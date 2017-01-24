<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Collection;
use Tequila\MongoDB\ODM\WriteModel\DocumentAwareWriteModel;

class DocumentsCollection extends Collection
{
    /**
     * @var DelegatingDocumentListener
     */
    private $documentListener;

    /**
     * @var BulkWriteBuilder
     */
    private $bulkWriteBuilder;

    /**
     * @param DocumentListenerInterface $listener
     */
    public function addDocumentListener(DocumentListenerInterface $listener)
    {
        $this->documentListener->addListener($listener);
    }

    /**
     * @inheritdoc
     */
    public function bulkWrite($requests, array $options = [])
    {
        if ($requests instanceof \Traversable) {
            $requests = iterator_to_array($requests);
        }
        $result = parent::bulkWrite($requests, $options);

        // set ids to inserted documents
        foreach ($result->getInsertedIds() as $position => $id) {
            if ($writeModel = $requests[$position] instanceof DocumentAwareWriteModel) {
                /** @var DocumentAwareWriteModel $writeModel */
                $writeModel->getDocument()->setId($id);
            }
        }

        // set ids to upserted documents
        foreach ($result->getUpsertedIds() as $position => $id) {
            if ($writeModel = $requests[$position] instanceof DocumentAwareWriteModel) {
                /** @var DocumentAwareWriteModel $writeModel */
                $writeModel->getDocument()->setId($id);
            }
        }

        return $result;
    }

    /**
     * @param array $filter
     * @param array $options
     * @return \Tequila\MongoDB\CursorInterface
     */
    public function find(array $filter = [], array $options = [])
    {
        return new DocumentsCursor(parent::find($filter, $options), $this->getDocumentListener());
    }

    /**
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder()
    {
        if (null === $this->bulkWriteBuilder) {
            $this->bulkWriteBuilder = new BulkWriteBuilder($this);
        }

        return $this->bulkWriteBuilder;
    }

    /**
     * @return DelegatingDocumentListener
     */
    private function getDocumentListener()
    {
        if (null === $this->documentListener) {
            $this->documentListener = new DelegatingDocumentListener();
            $this->documentListener->addListener(
                new DocumentChangesListener($this->getBulkWriteBuilder())
            );
        }

        return $this->documentListener;
    }
}