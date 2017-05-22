<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use DateTime;
use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\ODM\DocumentMetadata;
use Tequila\MongoDB\ODM\PersistenceTrait;

class Comment
{
    use PersistenceTrait;

    /**
     * @var ObjectID
     */
    private $id;

    /**
     * @var Author
     */
    private $author;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $content;

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
     * @return Author
     */
    public function getAuthor(): Author
    {
        return $this->author;
    }

    /**
     * @param Author $author
     */
    public function setAuthor(Author $author): void
    {
        $this->author = $author;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public static function loadDocumentMetadata(DocumentMetadata $metadata)
    {
        $metadata
            ->addIdField('id')
            ->addDocumentField('author', Author::class, '_author')
            ->addDateField('createdAt', 'created_at')
            ->addStringField('content', '_content');
    }
}
