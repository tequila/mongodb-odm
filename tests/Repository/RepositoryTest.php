<?php

namespace Tequila\MongoDB\ODM\Tests;

use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\Collection;
use Tequila\MongoDB\ODM\Repository\Repository;

class RepositoryTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $collection;
    /** @var Repository */
    private $repository;

    protected function setUp()
    {
        $this->collection = $this->createMock(Collection::class);
        $this->repository = new Repository($this->collection);
    }

    public function testFindAll()
    {
        $filter = ['a' => 1];
        $options = ['b' => 42];
        $this->collection->expects($this->once())
            ->method('find')
            ->with($filter, $options);

        $this->repository->findAll($filter, $options);
    }

    public function testFindOne()
    {
        $filter = ['a' => 1];
        $options = ['b' => 42];
        $this->collection->expects($this->once())
            ->method('findOne')
            ->with($filter, $options);

        $this->repository->findOne($filter, $options);
    }

    public function testFindOneById()
    {
        $id = 1;
        $options = ['b' => 42];
        $this->collection->expects($this->once())
            ->method('findOne')
            ->with(['_id' => $id], $options);

        $this->repository->findOneById($id, $options);
    }

    public function testFindAllByIds()
    {
        $ids = [1, 2, 4];
        $this->collection->expects($this->once())
            ->method('find')
            ->with(['_id' => ['$in' => array_values($ids)]]);

        $this->repository->findAllByIds($ids);
    }
}
