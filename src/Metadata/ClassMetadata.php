<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Metadata\Field\HashField;
use Tequila\MongoDB\ODM\Metadata\Field\RawField;
use Tequila\MongoDB\ODM\Repository\Repository;
use Tequila\MongoDB\ODM\Metadata\Field\AbstractFieldMetadata;
use Tequila\MongoDB\ODM\Metadata\Field\BooleanField;
use Tequila\MongoDB\ODM\Metadata\Field\ListField;
use Tequila\MongoDB\ODM\Metadata\Field\DateField;
use Tequila\MongoDB\ODM\Metadata\Field\DocumentField;
use Tequila\MongoDB\ODM\Metadata\Field\FieldMetadataInterface;
use Tequila\MongoDB\ODM\Metadata\Field\FloatField;
use Tequila\MongoDB\ODM\Metadata\Field\IntegerField;
use Tequila\MongoDB\ODM\Metadata\Field\ObjectIdField;
use Tequila\MongoDB\ODM\Metadata\Field\StringField;
use Zend\Code\Reflection\ClassReflection;

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
    private $collectionOptions;

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
     * @var bool
     */
    private $nested = false;

    /**
     * @var ClassReflection
     */
    private $reflection;

    /**
     * @var bool
     */
    private $identifiable = true;

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
    public function addListField(
        FieldMetadataInterface $itemMetadata,
        string $propertyName,
        string $dbFieldName = null
    ) {
        return $this->addField(new ListField($itemMetadata, $propertyName, $dbFieldName));
    }

    /**
     * @param FieldMetadataInterface $itemMetadata
     * @param string                 $propertyName
     * @param string|null            $dbFieldName
     *
     * @return ClassMetadata
     */
    public function addHashField(
        FieldMetadataInterface $itemMetadata,
        string $propertyName,
        string $dbFieldName = null
    ) {
        return $this->addField(new HashField($itemMetadata, $propertyName, $dbFieldName));
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
     *
     * @return $this
     */
    public function addIntegerField(string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new IntegerField($propertyName, $dbFieldName));
    }

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     *
     * @return $this
     */
    public function addStringField(string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new StringField($propertyName, $dbFieldName));
    }

    /**
     * @param string $propertyName
     * @param string|null $dbFieldName
     * 
     * @return ClassMetadata
     */
    public function addRawField(string $propertyName, string $dbFieldName = null)
    {
        return $this->addField(new RawField($propertyName, $dbFieldName));
    }

    /**
     * @return bool
     */
    public function isNested(): bool
    {
        return $this->nested;
    }

    /**
     * @param bool $nested
     *
     * @return $this
     */
    public function setNested(bool $nested = true)
    {
        if ($nested && (null !== $this->collectionName || null !== $this->collectionOptions)) {
            $err = 'Document class "%s" cannot be nested if collection name or collection options had been specified.';

            throw new LogicException(sprintf($err, $this->documentClass));
        }

        $this->nested = $nested;

        return $this;
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
        if (!$this->identifiable) {
            throw new \LogicException(
                sprintf(
                    'Calling method %s for non-identifiable document class %s is prohibited.',
                    __METHOD__,
                    $this->documentClass
                )
            );
        }

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
        if ($this->nested) {
            throw new LogicException(
                sprintf(
                    'Collection name cannot be set for nested document class "%s".',
                    $this->documentClass
                )
            );
        }

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
        return (array) $this->collectionOptions;
    }

    /**
     * @param array $collectionOptions
     *
     * @return $this
     */
    public function setCollectionOptions(array $collectionOptions)
    {
        if ($this->nested) {
            throw new LogicException(
                sprintf(
                    'Collection options cannot be set for nested document class "%s".',
                    $this->documentClass
                )
            );
        }

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

    /**
     * @return bool
     */
    public function isIdentifiable(): bool
    {
        return $this->identifiable;
    }

    /**
     * @param bool $identifiable
     *
     * @return $this
     */
    public function setIdentifiable(bool $identifiable)
    {
        $this->identifiable = $identifiable;

        return $this;
    }

    /**
     * @return ClassReflection
     *
     * @throws \ReflectionException
     */
    public function getReflection(): ClassReflection
    {
        if (null === $this->reflection) {
            $this->reflection = new ClassReflection($this->documentClass);
        }

        return $this->reflection;
    }
}
