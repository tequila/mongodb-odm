<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Collection;
use Tequila\MongoDB\WriteModelInterface;

class DocumentManager
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var DocumentChangeListener
     */
    private $documentListener;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
        $this->documentListener = new DocumentChangeListener();
    }

    /**
     * @param array $filter
     * @return QueryBuilder
     */
    public function find(array $filter = [])
    {
        $queryBuilder = new QueryBuilder($this->collection);
        $queryBuilder->setDocumentListener($this->documentListener);
        if ($filter) {
            $queryBuilder->filter($filter);
        }

        return $queryBuilder;
    }

    public function flush()
    {
        $documents = $this->documentListener->getDocuments();
        $changedDocuments = array_filter($documents, function(DocumentInterface $document) {
            return $document->getWriteModel();
        });

        /** @var WriteModelInterface[] $writeModels */
        $writeModels = array_map(function(DocumentInterface $document) {
            return $document->getWriteModel();
        }, $changedDocuments);

        $result = $this->collection->bulkWrite($writeModels);

        if ($result->isAcknowledged()) {
            foreach ($result->getInsertedIds() as $position => $id) {
                $changedDocuments[$position]->setId($id);
            }

            foreach ($result->getUpsertedIds() as $position => $id) {
                $changedDocuments[$position]->setId($id);
            }
        }

        return $result;
    }
}