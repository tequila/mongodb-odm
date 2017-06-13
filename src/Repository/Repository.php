<?php

namespace Tequila\MongoDB\ODM\Repository;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;

class Repository
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
     * @param array $filter
     * @param array $options
     *
     * @return \Tequila\MongoDB\QueryCursor
     */
    public function findAll(array $filter = [], array $options = [])
    {
        return $this->collection->find($filter, $options);
    }

    /**
     * @param array $filter
     * @param array $options
     *
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOne(array $filter = [], array $options = [])
    {
        return $this->collection->findOne($filter, $options);
    }

    /**
     * @param ObjectID|mixed $id
     * @param array          $options
     *
     * @return array|\MongoDB\BSON\Unserializable|null
     */
    public function findOneById($id, array $options = [])
    {
        return $this->collection->findOne(['_id' => $id], $options);
    }

    /**
     * @param array $ids
     *
     * @return \Tequila\MongoDB\QueryCursor
     */
    public function findAllByIds(array $ids)
    {
        if (!$ids) {
            throw new InvalidArgumentException('$ids array cannot be empty.');
        }

        return $this->collection->find(['_id' => ['$in' => array_values($ids)]]);
    }
}
