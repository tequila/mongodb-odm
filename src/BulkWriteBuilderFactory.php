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
        if (!isset($this->builders[$namespace])) {
            $this->builders[$namespace] = new BulkWriteBuilder();
        }

        return $this->builders[$namespace];
    }
}