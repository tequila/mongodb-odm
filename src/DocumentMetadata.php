<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Metadata\AbstractFieldMetadata;
use Tequila\MongoDB\ODM\Metadata\BooleanField;
use Tequila\MongoDB\ODM\Metadata\CollectionField;
use Tequila\MongoDB\ODM\Metadata\DateField;
use Tequila\MongoDB\ODM\Metadata\DocumentField;
use Tequila\MongoDB\ODM\Metadata\FieldMetadataInterface;
use Tequila\MongoDB\ODM\Metadata\FloatField;
use Tequila\MongoDB\ODM\Metadata\IntegerField;
use Tequila\MongoDB\ODM\Metadata\ObjectIdField;
use Tequila\MongoDB\ODM\Metadata\StringField;

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
     * @param mixed|null  $defaultValue
     *
     * @return $this
     */
    public function addBooleanField(string $propertyName, string $dbFieldName = null, $defaultValue = 'null')
    {
        return $this->addField(new BooleanField($propertyName, $dbFieldName, $defaultValue));
    }

    /**
     * @param string                 $propertyName
     * @param FieldMetadataInterface $itemMetadata
     * @param string|null            $dbFieldName
     *
     * @return $this
     */
    public function addCollectionField(
        string $propertyName,
        FieldMetadataInterface $itemMetadata,
        string $dbFieldName = null
    ) {
        return $this->addField(new CollectionField($propertyName, $itemMetadata, $dbFieldName));
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     * @param mixed|null  $defaultValue
     *
     * @return $this
     */
    public function addDateField(string $propertyName, string $dbFieldName = null, $defaultValue = 'null')
    {
        return $this->addField(new DateField($propertyName, $dbFieldName, $defaultValue));
    }

    /**
     * @param string      $propertyName
     * @param string      $documentClass
     * @param string|null $dbFieldName
     * @param mixed|null  $defaultValue
     *
     * @return $this
     */
    public function addDocumentField(
        string $propertyName,
        string $documentClass,
        string $dbFieldName = null,
        $defaultValue = 'null'
    ) {
        return $this->addField(new DocumentField($propertyName, $documentClass, $dbFieldName, $defaultValue));
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     * @param mixed|null  $defaultValue
     *
     * @return $this
     */
    public function addFloatField(string $propertyName, string $dbFieldName = null, $defaultValue = 'null')
    {
        return $this->addField(new FloatField($propertyName, $dbFieldName, $defaultValue));
    }

    /**
     * @param string $propertyName
     * @param bool   $canBeGenerated
     *
     * @return $this
     *
     * @internal param null|string $dbFieldName
     */
    public function addIdField(string $propertyName = 'id', bool $canBeGenerated = true)
    {
        return $this->addField(
            new ObjectIdField($propertyName, $canBeGenerated, '_id')
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
     * @return FieldMetadataInterface
     */
    public function getIdFieldMetadata(): FieldMetadataInterface
    {
        foreach ($this->fieldsMetadata as $fieldMetadata) {
            if ('_id' === $fieldMetadata->getDbFieldName()) {
                return $fieldMetadata;
            }
        }

        throw new LogicException(
            sprintf('Metadata for document %s does not contain _id field', $this->documentClass)
        );
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
