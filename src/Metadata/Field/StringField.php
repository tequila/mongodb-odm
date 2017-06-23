<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

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

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (string) $dbData;';
    }
}
