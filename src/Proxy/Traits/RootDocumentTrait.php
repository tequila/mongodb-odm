<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use MongoDB\Operation\BulkWrite;
use Tequila\MongoDB\ODM\Proxy\RootProxyInterface;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;
use Tequila\MongoDB\ODM\WriteModelInterface;

trait RootDocumentTrait
{
    use DocumentManagerAwareTrait;
    use UpdateBuilderTrait;
    use RealClassTrait;

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
        static $addedToBulk = false;
        if (!$addedToBulk) {
            /* @var WriteModelInterface|UpdateBuilderInterface $this */
            $this->documentManager->getBulkWriteBuilder(parent::class)->addWriteModel($this);
            $addedToBulk = true;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            BulkWrite::UPDATE_ONE => [
                ['_id' => $this->getMongoId()],
                $this->mongoDbUpdate,
                $this->mongoDbOptions,
            ],
        ];
    }
}
