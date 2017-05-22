<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use DateTime;
use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\ODM\Metadata\DocumentField;
use Tequila\MongoDB\ODM\DocumentMetadata;
use Tequila\MongoDB\ODM\PersistenceTrait;

class BlogPost
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
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var Comment[]
     */
    private $comments = [];

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
    public function setAuthor(Author $author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     * @param Comment $comment
     */
    public function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
    }

    /**
     * @param Comment $commentToRemove
     */
    public function removeComment(Comment $commentToRemove): void
    {
        $this->comments = array_diff($this->comments, [$commentToRemove]);
    }

    /**
     * @return Comment[]
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @param Comment[] $comments
     */
    public function setComments(array $comments)
    {
        $this->comments = $comments;
    }

    public static function loadDocumentMetadata(DocumentMetadata $metadata)
    {
        $metadata
            ->addIdField('id')
            ->addDocumentField('author', Author::class, '_author')
            ->addStringField('title', '_title')
            ->addStringField('content', '_content')
            ->addDateField('createdAt', 'created_at')
            ->addCollectionField(
                'comments',
                new DocumentField('comment', Comment::class),
                '_comments'
            );
    }
}
