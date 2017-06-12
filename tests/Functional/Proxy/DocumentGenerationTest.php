<?php

namespace Tequila\MongoDB\ODM\Tests\Functional\Proxy;

use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\ODM\DefaultMetadataFactory;
use Tequila\MongoDB\ODM\Generator\DocumentGenerator;
use Tequila\MongoDB\ODM\Tests\Stubs\Author;
use Tequila\MongoDB\ODM\Tests\Stubs\BlogPost;
use Tequila\MongoDB\ODM\Tests\Stubs\Comment;

class DocumentGenerationTest extends TestCase
{
    public function testDocumentGeneration()
    {
        $metadataFactory = new DefaultMetadataFactory();
        $classes = [BlogPost::class, Author::class, Comment::class];
        foreach ($classes as $class) {
            $metadata = $metadataFactory->getClassMetadata($class);
            $documentGenerator = new DocumentGenerator($metadata);
            $documentGenerator->generateClass();
        }

        return true;
    }
}
