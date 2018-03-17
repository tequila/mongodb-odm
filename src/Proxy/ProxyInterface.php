<?php

namespace Tequila\MongoDB\ODM\Proxy;

use MongoDB\BSON\Unserializable;

interface ProxyInterface extends Unserializable
{
    public function getRealClass(): string;
}
