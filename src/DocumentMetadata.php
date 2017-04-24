<?php

namespace Tequila\MongoDB\ODM;

class DocumentMetadata
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $collectionOptions;

    /**
     * @var string|null
     */
    private $repositoryClass;

    /**
     * @param string $collectionName
     * @param array  $collectionOptions
     * @param string $repositoryClass
     */
    public function __construct($collectionName, array $collectionOptions = [], $repositoryClass = null)
    {
        $this->collectionName = (string) $collectionName;
        $this->collectionOptions = $collectionOptions;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @return array
     */
    public function getCollectionOptions()
    {
        return $this->collectionOptions;
    }

    /**
     * @return string|null
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @param string $repositoryClass
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = (string) $repositoryClass;
    }
}
