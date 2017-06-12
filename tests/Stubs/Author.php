<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use Tequila\MongoDB\ODM\ClassMetadata;
use Tequila\MongoDB\ODM\FieldMetadata\StringField;

class Author
{
    public static function loadClassMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addStringField('firstName', 'first_name')
            ->addStringField('lastName', 'last_name')
            ->addCollectionField(
                new StringField('email'),
                'emails',
                '_emails'
            );
    }
}
