<?php

namespace Tequila\MongoDB\ODM;

class DocumentMetadata
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var string
     */
    private $collectionNamePrefix;

    /**
     * @var array
     */
    private $collectionOptions = [];

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var string|null
     */
    private $repositoryClass = DocumentRepository::class;

    /**
     * @var FieldMetadata[]
     */
    private $fieldsMetadata = [];

    /**
     * @param string $documentClass
     * @param string $collectionNamePrefix
     */
    public function __construct($documentClass, $collectionNamePrefix = '')
    {
        $this->documentClass;
        $this->collectionNamePrefix = (string) $collectionNamePrefix;
    }

    /**
     * @param string $propertyName
     * @param string $serializerClass
     * @param array $serializerOptions
     * @param string $dbFieldName
     * @return $this
     */
    public function addField($propertyName, $serializerClass, array $serializerOptions = [], $dbFieldName = null)
    {
        $this->fieldsMetadata[] = new FieldMetadata($propertyName, $serializerClass, $serializerOptions, $dbFieldName);

        return $this;
    }

    /**
     * @return $this
     */
    public function generateCollectionNameIfNotSet()
    {
        if (null === $this->collectionName) {
            $shortName = lcfirst((new \ReflectionClass($this->documentClass))->getShortName());
            $shortName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
            if ('s' !== substr($shortName, -1)) {
                $shortName .= 's';
            }
            $this->collectionName = $this->collectionNamePrefix
                ? $this->collectionNamePrefix.'_'.$shortName
                : $shortName;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName
     *
     * @return $this
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;

        return $this;
    }

    /**
     * @return FieldMetadata[]
     */
    public function getFieldsMetadata()
    {
        return $this->fieldsMetadata;
    }

    /**
     * @return array
     */
    public function getCollectionOptions()
    {
        return $this->collectionOptions;
    }

    /**
     * @param array $collectionOptions
     * @return $this
     */
    public function setCollectionOptions(array $collectionOptions)
    {
        $this->collectionOptions = $collectionOptions;

        return $this;
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
     * @return $this
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = (string) $repositoryClass;

        return $this;
    }
}
