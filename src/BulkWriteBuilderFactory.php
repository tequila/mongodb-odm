<?php

namespace Tequila\MongoDB\ODM;

class BulkWriteBuilderFactory
{
    /**
     * @var BulkWriteBuilder[]
     */
    private $builders = [];

    /**
     * @param $namespace
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder($namespace)
    {
        if (!array_key_exists($namespace, $this->builders)) {
            $this->builders[$namespace] = new BulkWriteBuilder();
        }

        return $this->builders[$namespace];
    }
}