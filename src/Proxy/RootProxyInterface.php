<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface RootProxyInterface extends ProxyInterface
{
    public function update(): UpdateBuilderInterface;
}
