<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface ProxyInterface
{
    public function getRealClass(): string;
}
