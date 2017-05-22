<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\ODM\DocumentMetadata;
use Tequila\MongoDB\ODM\Metadata\StringField;
use Tequila\MongoDB\ODM\PersistenceTrait;

class Author
{
    use PersistenceTrait;

    /**
     * @var ObjectID
     */
    private $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string[]
     */
    private $emails = [];

    /**
     * @return ObjectID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ObjectID $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $email
     */
    public function addEmail(string $email): void
    {
        $this->emails[] = $email;
    }

    /**
     * @param string $email
     */
    public function removeEmail(string $email): void
    {
        $this->emails = array_diff($this->emails, [$email]);
    }

    /**
     * @return \string[]
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * @param \string[] $emails
     */
    public function setEmails(array $emails): void
    {
        $this->emails = $emails;
    }

    public static function loadDocumentMetadata(DocumentMetadata $metadata)
    {
        $metadata
            ->addIdField('id')
            ->addStringField('firstName', 'first_name')
            ->addStringField('lastName', 'last_name')
            ->addCollectionField(
                'emails',
                new StringField('email'),
                '_emails'
            );
    }
}
