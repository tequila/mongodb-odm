<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface ProxyInterface
{
    public function getRootDocument(): RootDocumentInterface;

    public function getPathInDocument(string $dbFieldName): string;

    public function getRealClass(): string;
}
