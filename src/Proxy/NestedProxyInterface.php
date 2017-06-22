<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface NestedProxyInterface extends ProxyInterface
{
    public function getPathInDocument(string $dbFieldName): string;

    public function setPathInDocument(string $pathInDocument);

    public function getRootProxy(): RootProxyInterface;

    public function setRootProxy(RootProxyInterface $rootProxy);

    public function doBsonUnserialize();
}
