<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class StringField extends AbstractFieldMetadata
{
    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$dbData = null === $objectData ? null : (string) $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (string) $dbData;';
    }

    public function getTypeHint(): string
    {
        return 'string';
    }

    public function getReturnType(): string
    {
        return '?string';
    }
}
