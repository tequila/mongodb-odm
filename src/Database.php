<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\Driver\ReadPreference;
use Tequila\MongoDB\CommandInterface;
use Tequila\MongoDB\ODM\Listener\CollectionListenerInterface;
use Tequila\MongoDB\ODM\Traits\CollectionListenerTrait;

class Database
{
    use CollectionListenerTrait;

    /**
     * @var \Tequila\MongoDB\Database
     */
    private $mongoDatabase;

    /**
     * @param \Tequila\MongoDB\Database $database
     */
    public function __construct(\Tequila\MongoDB\Database $database)
    {
        $this->mongoDatabase = $database;
    }

    /**
     * Wraps @see \Tequila\MongoDB\Database::createCollection()
     *
     * @param string $collectionName
     * @param array $options
     * @return array
     */
    public function createCollection($collectionName, array $options = [])
    {
        return $this->mongoDatabase->createCollection($collectionName, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Database::drop()
     *
     * @param array $options
     * @return array
     */
    public function drop(array $options = [])
    {
        return $this->mongoDatabase->drop($options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Database::dropCollection()
     *
     * @param $collectionName
     * @param array $options
     * @return array
     */
    public function dropCollection($collectionName, array $options = [])
    {
        return $this->mongoDatabase->dropCollection($collectionName, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Database::getDatabaseName()
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->mongoDatabase->getDatabaseName();
    }

    /**
     * @param array $options
     * @return \Tequila\MongoDB\CursorInterface
     */
    public function listCollections(array $options = [])
    {
        return $this->mongoDatabase->listCollections($options);
    }

    /**
     * @param CommandInterface $command
     * @param ReadPreference|null $readPreference
     * @return \Tequila\MongoDB\CursorInterface
     */
    public function runCommand(CommandInterface $command, ReadPreference $readPreference = null)
    {
        return $this->mongoDatabase->runCommand($command, $readPreference);
    }

    /**
     * @param string $collectionName
     * @param array $options
     * @return DocumentsCollection
     */
    public function selectCollection($collectionName, array $options = [])
    {
        $collection = $this->mongoDatabase->selectCollection($collectionName, $options);
        $collection = new DocumentsCollection($collection);

        if (null !== $this->collectionListener) {
            $this->collectionListener->collectionSelected($collection);
        }

        return $collection;
    }

    /**
     * @param CollectionListenerInterface $listener
     */
    public function setCollectionListener(CollectionListenerInterface $listener)
    {
        $this->collectionListener = $listener;
    }
}