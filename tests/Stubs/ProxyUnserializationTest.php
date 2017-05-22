<?php

namespace Tequila\MongoDB\ODM\Tests\Stubs;

use DateTime;
use MongoDB\BSON\UTCDateTime;
use PHPUnit\Framework\TestCase;
use function Tequila\MongoDB\applyTypeMap;
use Tequila\MongoDB\ODM\DefaultMetadataFactory;
use Tequila\MongoDB\ODM\Proxy\ProxyGeneratorFactory;
use Tequila\MongoDB\ODM\Proxy\ProxyInterface;

class ProxyUnserializationTest extends TestCase
{
    public function testNestedUnserialization()
    {
        $commentsSerialized = [
            [
                '_id' => 'comment_1',
                '_author' => [
                    '_id' => 'comment_author_1',
                    'first_name' => 'Petr',
                    'last_name' => 'Buchyn',
                    '_emails' => ['petrbuchyn@gmail.com', 'unreal_email@yahoo.com'],
                ],
                'created_at' => new UTCDateTime(),
                '_content' => 'Wow, seems like collection-nested this unserialization works!',
            ],
            [
                '_id' => 'comment_2',
                '_author' => [
                    '_id' => 'comment_author_2',
                    'first_name' => 'Fictional',
                    'last_name' => 'Guy',
                    '_emails' => ['fictionalGuy@gmail.com', 'guy@mail.com'],
                ],
                'created_at' => new UTCDateTime(),
                '_content' => 'Looks like event second comment was unserialized properly!',
            ],
        ];

        $blogPostSerialized = [
            '_id' => 'blogPost_1',
            '_author' => [
                '_id' => 'blogPost_author_1',
                'first_name' => 'Petr',
                'last_name' => 'Buchyn',
                '_emails' => ['petrbuchyn@gmail.com', 'unreal_email@yahoo.com'],
            ],
            '_title' => 'Unserialization proof-of-concept test',
            '_content' => 'This blog post must be unserialized to BlogPostRootProxy instance.',
            'created_at' => new UTCDateTime(),
            '_comments' => $commentsSerialized,
        ];

        $metadataFactory = new DefaultMetadataFactory();
        $proxyFactory = new ProxyGeneratorFactory(
            $metadataFactory,
            __DIR__.'/Proxy',
            'Tequila\MongoDB\ODM\Proxy',
            true
        );

        $blogPostProxyClass = $proxyFactory->getProxyClass(BlogPost::class);
        $authorProxyClass = $proxyFactory->getProxyClass(Author::class, false);
        $commentProxyClass = $proxyFactory->getProxyClass(Comment::class, false);

        $blogPost = applyTypeMap(
            $blogPostSerialized,
            ['root' => $blogPostProxyClass, 'document' => 'array']
        );
        $this->assertInstanceOf($blogPostProxyClass, $blogPost);

        $this->assertSame($blogPostSerialized['_id'], $blogPost->getId());
        $this->assertSame($blogPostSerialized['_id'], $blogPost->getMongoId());
        $this->assertSame($blogPostSerialized['_title'], $blogPost->getTitle());
        $this->assertSame($blogPostSerialized['_content'], $blogPost->getContent());
        $this->assertInstanceOf(DateTime::class, $blogPost->getCreatedAt());

        /** @var Author|ProxyInterface $author */
        $author = $blogPost->getAuthor();
        $this->assertInstanceOf($authorProxyClass, $author);
        $this->assertSame($blogPostSerialized['_author']['_id'], $author->getId());
        $this->assertSame($blogPostSerialized['_author']['first_name'], $author->getFirstName());
        $this->assertSame($blogPostSerialized['_author']['last_name'], $author->getLastName());
        $this->assertSame($blogPostSerialized['_author']['_emails'], $author->getEmails());
        $this->assertSame($blogPost, $author->getRootDocument());

        /** @var Comment[]|ProxyInterface[] $comments */
        $comments = $blogPost->getComments();
        $this->assertCount(2, $comments);

        foreach ($comments as $i => $comment) {
            $this->assertInstanceOf($commentProxyClass, $comment);
            $this->assertSame($commentsSerialized[$i]['_id'], $comments[$i]->getId());
            $this->assertSame($commentsSerialized[$i]['_content'], $comments[$i]->getContent());
            $this->assertInstanceOf(DateTime::class, $comments[$i]->getCreatedAt());
            $this->assertSame($blogPost, $comments[$i]->getRootDocument());

            $author = $comments[$i]->getAuthor();
            $this->assertInstanceOf($authorProxyClass, $author);
            $this->assertSame($commentsSerialized[$i]['_author']['_id'], $author->getId());
            $this->assertSame($commentsSerialized[$i]['_author']['first_name'], $author->getFirstName());
            $this->assertSame($commentsSerialized[$i]['_author']['last_name'], $author->getLastName());
            $this->assertSame($commentsSerialized[$i]['_author']['_emails'], $author->getEmails());
            $this->assertSame($blogPost, $author->getRootDocument());
        }
    }
}
