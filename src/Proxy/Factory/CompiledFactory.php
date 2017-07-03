<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

class CompiledFactory extends AbstractFactory
{
    public function getProxyClass(string $documentClass): string
    {
        return $this->getProxyClassName($documentClass);
    }
}
