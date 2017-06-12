<?php

namespace Tequila\MongoDB\ODM\FieldMetadata;

use Tequila\MongoDB\ODM\Generator\ProxyGenerator;

class StringField extends AbstractFieldMetadata
{
    public function getType(): string
    {
        return 'string';
    }

    public function getSerializationCode(): string
    {
        return '$dbData = null === $objectData ? null : (string) $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (string) $dbData;';
    }
}
