<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class RawField extends AbstractFieldMetadata
{
    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$target = $value;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = $dbData;';
    }

    public function getTypeHint(): string
    {
        return null;
    }

    public function getReturnType(): string
    {
        return null;
    }
}
