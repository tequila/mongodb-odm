<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface RootDocumentInterface
{
    public function update(): UpdateBuilderInterface;

    public function getRealClass(): string;
}
