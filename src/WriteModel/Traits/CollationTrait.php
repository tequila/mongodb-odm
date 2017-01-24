<?php

namespace Tequila\MongoDB\ODM\WriteModel\Traits;

trait CollationTrait
{
    /**
     * @param array|object $collation
     * @return $this
     */
    public function collation($collation)
    {
        $this->options['collation'] = $collation;

        return $this;
    }
}