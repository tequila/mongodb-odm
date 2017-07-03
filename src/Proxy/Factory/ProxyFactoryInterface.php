<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

use Tequila\MongoDB\ODM\DocumentManager;

interface ProxyFactoryInterface
{
    public function getProxyClass(DocumentManager $documentManager, string $documentClass): string;
}
