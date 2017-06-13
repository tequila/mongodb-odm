<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Repository\Repository;
use Tequila\MongoDB\ODM\Metadata\Field\AbstractFieldMetadata;
use Tequila\MongoDB\ODM\Metadata\Field\BooleanField;
use Tequila\MongoDB\ODM\Metadata\Field\CollectionField;
use Tequila\MongoDB\ODM\Metadata\Field\DateField;
use Tequila\MongoDB\ODM\Metadata\Field\DocumentField;
use Tequila\MongoDB\ODM\Metadata\Field\FieldMetadataInterface;
use Tequila\MongoDB\ODM\Metadata\Field\FloatField;
use Tequila\MongoDB\ODM\Metadata\Field\IntegerField;
use Tequila\MongoDB\ODM\Metadata\Field\ObjectIdField;
use Tequila\MongoDB\ODM\Metadata\Field\StringField;

class ClassMetadata
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
    private $repositoryClass = Repository::class;

    /**
     * @var AbstractFieldMetadata[]
     */
    private $fieldsMetadata = [];

    /**
     * @param string $documentClass
     * @param string $collectionNamePrefix
     */
    public function __construct(string $documentClass, string $collectionNamePrefix = '')
    {
        $this->documentClass = $documentClass;
        $this->collectionNamePrefix = (string) $collectionNamePrefix;
    }

    /**
     * @param AbstractFieldMetadata $fieldMetadata
     *
     * @return $this
     */
    public function addField(AbstractFieldMetadata $fieldMetadata)
    {
        $this->fieldsMetadata[] = $fieldMetadata;

        return $this;
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     *
     * @return $this
     */
    public function addBooleanField(string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new BooleanField($propertyName, $dbFieldName));
    }

    /**
     * @param FieldMetadataInterface $itemMetadata
     * @param string                 $propertyName
     * @param string|null            $dbFieldName
     *
     * @return $this
     */
    public function addCollectionField(
        FieldMetadataInterface $itemMetadata,
        string $propertyName,
        string $dbFieldName = null
    ) {
        return $this->addField(new CollectionField($itemMetadata, $propertyName, $dbFieldName));
    }

    /**
     * @param string $propertyName
     * @param string $dbFieldName
     *
     * @return $this
     */
    public function addDateField(string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new DateField($propertyName, $dbFieldName));
    }

    /**
     * @param string $documentClass
     * @param string $propertyName
     * @param string $dbFieldName
     *
     * @return $this
     */
    public function addDocumentField(string $documentClass, string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new DocumentField($documentClass, $propertyName, $dbFieldName));
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     *
     * @return $this
     */
    public function addFloatField(string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new FloatField($propertyName, $dbFieldName));
    }

    /**
     * @param string $propertyName
     * @param string $dbFieldName
     * @param bool   $generateIfNotSet
     *
     * @return $this
     */
    public function addObjectIdField(string $propertyName, string $dbFieldName = null, bool $generateIfNotSet = false)
    {
        return $this->addField(
            new ObjectIdField($propertyName, $dbFieldName, $generateIfNotSet)
        );
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     * @param mixed|null  $defaultValue
     *
     * @return $this
     */
    public function addIntegerField(string $propertyName, string $dbFieldName = null, $defaultValue = 'null')
    {
        return $this->addField(new IntegerField($propertyName, $dbFieldName, $defaultValue));
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     * @param mixed|null  $defaultValue
     *
     * @return $this
     */
    public function addStringField(string $propertyName, string $dbFieldName = null, $defaultValue = 'null')
    {
        return $this->addField(new StringField($propertyName, $dbFieldName, $defaultValue));
    }

    /**
     * @param string $propertyName
     *
     * @return FieldMetadataInterface
     */
    public function getFieldByPropertyName(string $propertyName): ?FieldMetadataInterface
    {
        foreach ($this->fieldsMetadata as $fieldMetadata) {
            if ($propertyName === $fieldMetadata->getPropertyName()) {
                return $fieldMetadata;
            }
        }

        return null;
    }

    /**
     * @param string $dbFieldName
     *
     * @return FieldMetadataInterface
     */
    public function getFieldByDbName(string $dbFieldName): ?FieldMetadataInterface
    {
        foreach ($this->fieldsMetadata as $fieldMetadata) {
            if ($dbFieldName === $fieldMetadata->getDbFieldName()) {
                return $fieldMetadata;
            }
        }

        return null;
    }

    /**
     * @return FieldMetadataInterface
     */
    public function getPrimaryKeyField(): ?FieldMetadataInterface
    {
        return $this->getFieldByDbName('_id');
    }

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName
     *
     * @return $this
     */
    public function setCollectionName(string $collectionName)
    {
        $this->collectionName = $collectionName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    /**
     * @return FieldMetadataInterface[]
     */
    public function getFieldsMetadata(): array
    {
        return $this->fieldsMetadata;
    }

    /**
     * @return array
     */
    public function getCollectionOptions(): array
    {
        return $this->collectionOptions;
    }

    /**
     * @param array $collectionOptions
     *
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
    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * @param string $repositoryClass
     *
     * @return $this
     */
    public function setRepositoryClass(string $repositoryClass)
    {
        $this->repositoryClass = (string) $repositoryClass;

        return $this;
    }
}
