<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Client;

class Connection
{
    /**
     * @var Client
     */
    private $mongoClient;

    /**
     * @param Client $mongoClient
     */
    public function __construct(Client $mongoClient)
    {
        $this->mongoClient = $mongoClient;
    }

    /**
     * Wraps @see \Tequila\MongoDB\Client::dropDatabase()
     *
     * @param string $databaseName
     * @param array $options
     * @return array
     */
    public function dropDatabase($databaseName, array $options = [])
    {
        return $this->mongoClient->dropDatabase($databaseName, $options);
    }

    /**
     * Wraps @see \Tequila\MongoDB\Client::getReadConcern()
     *
     * @return \MongoDB\Driver\ReadConcern
     */
    public function getReadConcern()
    {
        return $this->mongoClient->getReadConcern();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Client::getReadPreference()
     *
     * @return \MongoDB\Driver\ReadPreference
     */
    public function getReadPreference()
    {
        return $this->mongoClient->getReadPreference();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Client::getWriteConcern()
     *
     * @return \MongoDB\Driver\WriteConcern
     */
    public function getWriteConcern()
    {
        return $this->mongoClient->getWriteConcern();
    }

    /**
     * Wraps @see \Tequila\MongoDB\Client::listDatabases()
     *
     * @return array
     */
    public function listDatabases()
    {
        return $this->mongoClient->listDatabases();
    }

    /**
     * @param $databaseName
     * @param $collectionName
     * @param array $options
     * @return DocumentsCollection
     */
    public function selectCollection($databaseName, $collectionName, array $options = [])
    {
        $collection = $this->mongoClient->selectCollection(
            $databaseName,
            $collectionName,
            $options
        );

        return new DocumentsCollection($collection);
    }

    /**
     * @param $databaseName
     * @param array $options
     * @return Database
     */
    public function selectDatabase($databaseName, array $options = [])
    {
        $database = $this->mongoClient->selectDatabase($databaseName, $options);

        return new Database($database);
    }
}