<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\ODM\Proxy\RootProxyInterface;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;
use Tequila\MongoDB\WriteModelInterface;

trait RootDocumentTrait
{
    use DocumentManagerAwareTrait;
    use UpdateBuilderTrait;
    use RealClassTrait;

    /**
     * @var bool
     */
    private $addedToBulk = false;

    /**
     * @return mixed
     */
    abstract public function getMongoId();

    /**
     * @return RootProxyInterface|$this
     */
    public function getRootDocument(): RootProxyInterface
    {
        /* @var RootProxyInterface $this */
        return $this;
    }

    /**
     * @param string $dbFieldName
     *
     * @return string
     */
    public function getPathInDocument(string $dbFieldName): string
    {
        return $dbFieldName;
    }

    /**
     * @return UpdateBuilderInterface
     */
    public function update(): UpdateBuilderInterface
    {
        if (!$this->addedToBulk) {
            /* @var WriteModelInterface|UpdateBuilderInterface $this */
            $this->documentManager->getBulkWriteBuilder(parent::class)->add($this);
            $this->addedToBulk = true;
        }

        return $this;
    }

    /**
     * @param BulkWrite $bulkWrite
     */
    public function writeToBulk(BulkWrite $bulkWrite)
    {
        $bulkWrite->update(['_id' => $this->getMongoId()], $this->mongoDbUpdate, $this->mongoDbOptions);
    }
}
