<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\Manager;

class BulkWriteBuilderFactory
{
    /**
     * @var BulkWriteBuilder[]
     */
    private $builders = [];

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $namespace
     * @return BulkWriteBuilder
     */
    public function getBulkWriteBuilder($namespace)
    {
        if (!array_key_exists($namespace, $this->builders)) {
            $this->builders[$namespace] = new BulkWriteBuilder($this->manager, $namespace);
        }

        return $this->builders[$namespace];
    }
}