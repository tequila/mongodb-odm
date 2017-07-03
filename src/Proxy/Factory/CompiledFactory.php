<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

use Tequila\MongoDB\ODM\DocumentManager;

class CompiledFactory extends AbstractFactory
{
    public function getProxyClass(DocumentManager $documentManager, string $documentClass): string
    {
        return $this->getProxyClassName($documentClass);
    }
}
