<?php

namespace Tequila\MongoDB\ODM\Tests\Functional\Proxy;

use function MongoDB\apply_type_map_to_document;
use DateTime;
use MongoDB\Operation\BulkWrite;
use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\ODM\Metadata\Factory\StaticMethodAwareFactory;
use Tequila\MongoDB\ODM\Proxy\Factory\GeneratorFactory;
use Tequila\MongoDB\ODM\Tests\Stubs\Author;
use Tequila\MongoDB\ODM\Tests\Stubs\BlogPost;
use Tequila\MongoDB\ODM\Tests\Stubs\Comment;

class UpdateBuilderTest extends TestCase
{
    public function testUpdate()
    {
        $proxy = $this->getBlogPostProxy();
        $dm = $this->createMock(DocumentManager::class);
        $proxy->setManager($dm);
        $proxy->setTitle('Updated title');
        $this->assertSame('Updated title', $proxy->getTitle());
        $bulkOp = $proxy->toArray();
        $this->assertSame(BulkWrite::UPDATE_ONE, key($bulkOp));
        $bulkOp = current($bulkOp);
        $this->assertSame(['_id' => $proxy->getId()], $bulkOp[0]);
        $this->assertSame(['$set' => ['_title' => 'Updated title']], $bulkOp[1]);
        $this->assertSame([], $bulkOp[2]);
    }

    /**
     * @return \TequilaODMFunctionalTests\Tequila\MongoDB\ODM\Tests\Stubs\BlogPostProxy
     */
    private function getBlogPostProxy()
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

        $metadataFactory = new StaticMethodAwareFactory();
        $proxyFactory = new GeneratorFactory(
            __DIR__.'/../../Stubs/Proxy',
            'TequilaODMFunctionalTests',
            $metadataFactory
        );
        $proxyClass = $proxyFactory->getProxyClass(BlogPost::class);

        /** @var \TequilaODMFunctionalTests\Tequila\MongoDB\ODM\Tests\Stubs\BlogPostProxy $proxy */
        $proxy = apply_type_map_to_document(
            $blogPost,
            ['root' => $proxyClass, 'document' => 'array', 'array' => 'array']
        );

        return $proxy;
    }
}