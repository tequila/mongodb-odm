<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface NestedProxyInterface extends ProxyInterface
{
    public function getPathInDocument(string $dbFieldName): string;

    public function getRootDocument(): RootProxyInterface;
}
