<?php

namespace Tequila\MongoDB\ODM\Tests\Functional\Proxy;

use function MongoDB\apply_type_map_to_document;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;
use PHPUnit\Framework\TestCase;
use DateTime;
use Tequila\MongoDB\ODM\Metadata\Factory\StaticMethodAwareFactory;
use Tequila\MongoDB\ODM\Proxy\NestedProxyInterface;
use Tequila\MongoDB\ODM\Proxy\Factory\GeneratorFactory;
use Tequila\MongoDB\ODM\Tests\Stubs\Author;
use Tequila\MongoDB\ODM\Tests\Stubs\BlogPost;
use Tequila\MongoDB\ODM\Tests\Stubs\Comment;

class SerializationUnserializationTest extends TestCase
{
    public function testSerialization()
    {
        $author1 = new Author();
        $author1->setFirstName('Petr');
        $author1->setLastName('Buchyn');
        $author1->setEmails(['petrbuchyn@gmail.com', 'unreal_email@yahoo.com']);

        $author2 = new Author();
        $author2->setFirstName('Fictional');
        $author2->setLastName('Guy');
        $author2->setEmails(['fictionalGuy@gmail.com', 'guy@mail.com']);

        $comment1 = new Comment();
        $comment1->setAuthor($author1);
        $comment1->setCreatedAt(new DateTime());
        $comment1->setContent('Wow, seems like nested serialization works!');

        $comment2 = new Comment();
        $comment2->setAuthor($author2);
        $comment2->setCreatedAt(new DateTime());
        $comment2->setContent('Looks like event second comment was serialized properly!');

        $blogPost = new BlogPost();
        $blogPost->setAuthor($author1);
        $blogPost->setTitle('Serialization proof-of-concept test');
        $blogPost->setContent('This blog post must be unserialized to BlogPostRootProxy instance.');
        $blogPost->setCreatedAt(new DateTime());
        $blogPost->setComments([$comment1, $comment2]);

        $data = apply_type_map_to_document(
            $blogPost,
            ['document' => 'array', 'root' => 'array', 'array' => 'array']
        );

        $this->assertInstanceOf(ObjectID::class, $data['_id']);
        $this->assertSame($blogPost->getTitle(), $data['_title']);
        $this->assertSame($blogPost->getContent(), $data['_content']);
        $this->assertInstanceOf(UTCDateTime::class, $data['created_at']);

        $this->assertInstanceOf(ObjectID::class, $data['_author']['_id']);
        $this->assertSame($author1->getFirstName(), $data['_author']['first_name']);
        $this->assertSame($author1->getLastName(), $data['_author']['last_name']);
        $this->assertSame($author1->getEmails(), $data['_author']['_emails']);

        $this->assertInstanceOf(ObjectID::class, $data['_comments'][0]['_id']);
        $this->assertInstanceOf(UTCDateTime::class, $data['_comments'][0]['created_at']);
        $this->assertSame($comment1->getContent(), $data['_comments'][0]['_content']);
        $this->assertInstanceOf(ObjectID::class, $data['_comments'][0]['_author']['_id']);
        $this->assertSame($author1->getFirstName(), $data['_comments'][0]['_author']['first_name']);
        $this->assertSame($author1->getLastName(), $data['_comments'][0]['_author']['last_name']);
        $this->assertSame($author1->getEmails(), $data['_comments'][0]['_author']['_emails']);

        $this->assertInstanceOf(ObjectID::class, $data['_comments'][1]['_id']);
        $this->assertInstanceOf(UTCDateTime::class, $data['_comments'][1]['created_at']);
        $this->assertSame($comment2->getContent(), $data['_comments'][1]['_content']);
        $this->assertInstanceOf(ObjectID::class, $data['_comments'][1]['_author']['_id']);
        $this->assertSame($author2->getFirstName(), $data['_comments'][1]['_author']['first_name']);
        $this->assertSame($author2->getLastName(), $data['_comments'][1]['_author']['last_name']);
        $this->assertSame($author2->getEmails(), $data['_comments'][1]['_author']['_emails']);

        return $data;
    }

    /**
     * @depends testSerialization
     *
     * @param array $blogPostSerialized
     *
     * @return BlogPost
     */
    public function testUnserialization(array $blogPostSerialized)
    {
        $metadataFactory = new StaticMethodAwareFactory();
        $proxyFactory = new GeneratorFactory(
            __DIR__.'/../../Stubs/Proxy',
            'TequilaODMFunctionalTests',
            $metadataFactory
        );

        $blogPostProxyClass = $proxyFactory->getProxyClass(BlogPost::class);
        $authorProxyClass = $proxyFactory->getProxyClass(Author::class, false);
        $commentProxyClass = $proxyFactory->getProxyClass(Comment::class, false);

        /** @var BlogPost $blogPost */
        $blogPost = apply_type_map_to_document(
            $blogPostSerialized,
            ['root' => $blogPostProxyClass, 'document' => 'array']
        );
        $this->assertInstanceOf($blogPostProxyClass, $blogPost);

        $this->assertSame((string) $blogPostSerialized['_id'], (string) $blogPost->getId());
        $this->assertSame($blogPostSerialized['_title'], $blogPost->getTitle());
        $this->assertSame($blogPostSerialized['_content'], $blogPost->getContent());
        $this->assertInstanceOf(DateTime::class, $blogPost->getCreatedAt());

        /** @var Author|NestedProxyInterface $author */
        $author = $blogPost->getAuthor();
        $this->assertInstanceOf($authorProxyClass, $author);
        $this->assertSame((string) $blogPostSerialized['_author']['_id'], (string) $author->getId());
        $this->assertSame($blogPostSerialized['_author']['first_name'], $author->getFirstName());
        $this->assertSame($blogPostSerialized['_author']['last_name'], $author->getLastName());
        $this->assertSame($blogPostSerialized['_author']['_emails'], iterator_to_array($author->getEmails()));
        $this->assertSame($blogPost, $author->getRootProxy());

        /** @var Comment[]|NestedProxyInterface[] $comments */
        $comments = $blogPost->getComments();
        $this->assertCount(2, $comments);
        $commentsSerialized = $blogPostSerialized['_comments'];

        foreach ($comments as $i => $comment) {
            $this->assertInstanceOf($commentProxyClass, $comment);
            $this->assertSame((string) $commentsSerialized[$i]['_id'], (string) $comments[$i]->getId());
            $this->assertSame($commentsSerialized[$i]['_content'], $comments[$i]->getContent());
            $this->assertInstanceOf(DateTime::class, $comments[$i]->getCreatedAt());
            $this->assertSame($blogPost, $comments[$i]->getRootProxy());

            $author = $comments[$i]->getAuthor();
            $this->assertInstanceOf($authorProxyClass, $author);
            $this->assertSame((string) $commentsSerialized[$i]['_author']['_id'], (string) $author->getId());
            $this->assertSame($commentsSerialized[$i]['_author']['first_name'], $author->getFirstName());
            $this->assertSame($commentsSerialized[$i]['_author']['last_name'], $author->getLastName());
            $this->assertSame($commentsSerialized[$i]['_author']['_emails'], iterator_to_array($author->getEmails()));
            $this->assertSame($blogPost, $author->getRootProxy());
        }

        return $blogPost;
    }
}
