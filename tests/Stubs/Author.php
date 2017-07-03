<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\Metadata\Field\StringField;

class Author
{
    public static function loadClassMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->setNested(true)
            ->addStringField('firstName', 'first_name')
            ->addStringField('lastName', 'last_name')
            ->addCollectionField(
                new StringField('email'),
                'emails',
                '_emails'
            );
    }
}
