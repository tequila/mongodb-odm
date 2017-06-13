<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

interface ProxyFactoryInterface
{
    public function getProxyClass(string $documentClass, bool $isRootProxy = true): string;
}
