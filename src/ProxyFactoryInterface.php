<?php

namespace Tequila\MongoDB\ODM;

interface ProxyFactoryInterface
{
    public function getProxyClass(string $documentClass, bool $asRootDocument = true);
}
