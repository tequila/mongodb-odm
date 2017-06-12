<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use Tequila\MongoDB\ODM\ClassMetadata;
use Tequila\MongoDB\ODM\FieldMetadata\DocumentField;

class BlogPost
{
    public static function loadClassMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addDocumentField(Author::class, 'author', '_author')
            ->addStringField('title', '_title')
            ->addStringField('content', '_content')
            ->addDateField('createdAt', 'created_at')
            ->addCollectionField(
                new DocumentField(Comment::class, 'comment'),
                'comments',
                '_comments'
            );
    }
}
