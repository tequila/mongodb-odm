<?php

namespace Tequila\MongoDB\ODM\WriteModel\Traits;

trait UpsertTrait
{
    /**
     * @return $this
     */
    public function upsert()
    {
        $this->options['upsert'] = true;

        return $this;
    }
}