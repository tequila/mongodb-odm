<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;

class DocumentRepository
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     */
    final public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param ObjectID|mixed $id
     * @param array $options
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOneById($id, array $options = [])
    {
        return $this->collection->findOne(['_id' => $id], $options);
    }

    /**
     * @param array $ids
     * @return \Tequila\MongoDB\Cursor
     */
    public function findAllByIds(array $ids)
    {
        if (!$ids) {
            throw new InvalidArgumentException('$ids array cannot be empty.');
        }

        return $this->collection->find(['_id' => ['$in' => array_values($ids)]]);
    }
}