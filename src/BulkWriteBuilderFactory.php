<?php

namespace Tequila\MongoDB\ODM;

class BulkWriteBuilderFactory
{
    /**
     * @var BulkWriteBuilder[]
     */
    private $builders = [];

    /**
     * @param DocumentManager $documentManager
     * @param string $collectionName
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder(DocumentManager $documentManager, string $collectionName)
    {
        if (!array_key_exists($collectionName, $this->builders)) {
            $this->builders[$collectionName] = new BulkWriteBuilder($documentManager, $collectionName);
        }

        return $this->builders[$collectionName];
    }

    /**
     * @return BulkWriteBuilder[]
     */
    public function getBulkWriteBuilders(): array
    {
        return $this->builders;
    }
}
