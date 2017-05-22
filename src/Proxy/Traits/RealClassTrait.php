<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

trait RealClassTrait
{
    public function getRealClass(): string
    {
        return parent::class;
    }
}
