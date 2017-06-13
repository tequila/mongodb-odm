<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use Tequila\MongoDB\ODM\Metadata\ClassMetadata;

class Comment
{
    public static function loadClassMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addDocumentField(Author::class, 'author', '_author')
            ->addDateField('createdAt', 'created_at')
            ->addStringField('content', '_content');
    }
}
