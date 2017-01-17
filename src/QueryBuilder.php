<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Collection;
use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\QueryInterface;

class QueryBuilder
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var DocumentListenerInterface
     */
    private $documentListener;

    /**
     * @var array
     */
    private $filter = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return CursorInterface
     */
    public function execute()
    {
        $cursor = $this->collection->find($this->filter, $this->options);

        if ($this->documentListener) {
            $cursor = new Cursor($cursor, $this->documentListener);
        }

        return $cursor;
    }

    /**
     * @param array $filter
     * @return $this
     */
    public function filter(array $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowPartialResults()
    {
        $this->options['allowPartialResults'] = true;

        return $this;
    }

    /**
     * @param $size
     * @return $this
     */
    public function batchSize($size)
    {
        $this->options['batchSize'] = $size;

        return $this;
    }

    /**
     * @param $collation
     * @return $this
     */
    public function collation($collation)
    {
        $this->options['collation'] = $collation;

        return $this;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function comment($comment)
    {
        $this->options['comment'] = $comment;

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->options['limit'] = $limit;

        return $this;
    }

    /**
     * @param $modifiers
     * @return $this
     */
    public function modifiers($modifiers)
    {
        $this->options['modifiers'] = $modifiers;

        return $this;
    }

    /**
     * @return $this
     */
    public function noCursorTimeout()
    {
        $this->options['noCursorTimeout'] = true;

        return $this;
    }

    /**
     * @param $projection
     * @return $this
     */
    public function projection($projection)
    {
        $this->options['projection'] = $projection;

        return $this;
    }

    /**
     * @param DocumentListenerInterface $listener
     */
    public function setDocumentListener(DocumentListenerInterface $listener)
    {
        $this->documentListener = $listener;
    }

    /**
     * @param $skip
     * @return $this
     */
    public function skip($skip)
    {
        $this->options['skip'] = $skip;

        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function sort($sort)
    {
        $this->options['sort'] = $sort;

        return $this;
    }

    /**
     * @return $this
     */
    public function tailable()
    {
        $this->options['cursorType'] = QueryInterface::CURSOR_TAILABLE;

        return $this;
    }

    /**
     * @return $this
     */
    public function tailableAwait()
    {
        $this->options['cursorType'] = QueryInterface::CURSOR_TAILABLE_AWAIT;

        return $this;
    }
}