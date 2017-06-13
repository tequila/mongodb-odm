<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class BooleanField extends AbstractFieldMetadata
{
    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$dbData = null === $objectData ? null : (bool) $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (bool) $dbData;';
    }

    public function getTypeHint(): string
    {
        return 'bool';
    }

    public function getReturnType(): string
    {
        return '?bool';
    }
}
