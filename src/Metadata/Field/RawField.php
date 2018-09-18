<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

class RawField extends AbstractFieldMetadata
{
    public function getType(): string
    {
        return 'mixed';
    }

    public function getSerializationCode(): string
    {
        return '$dbData = $objectData;';
    }

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return '$objectData = $dbData;';
    }
}