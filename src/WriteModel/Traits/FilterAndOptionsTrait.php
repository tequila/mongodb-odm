<?php

namespace Tequila\MongoDB\ODM\WriteModel\Traits;

use Tequila\MongoDB\DocumentInterface;

trait FilterAndOptionsTrait
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @return array
     */
    private function getFilter()
    {
        return ['_id' => $this->getDocument()->getId()];
    }

    /**
     * @return DocumentInterface
     */
    abstract public function getDocument();
}
