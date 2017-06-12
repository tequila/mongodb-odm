<?php

namespace Tequila\MongoDB\ODM\FieldMetadata;

use Tequila\MongoDB\ODM\IncreaserDecreaserTrait;
use Tequila\MongoDB\ODM\Generator\ProxyGenerator;

class IntegerField extends AbstractFieldMetadata
{
    use IncreaserDecreaserTrait;

    public function getType(): string
    {
        return 'int';
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
    {
        $this->generateIncreaser($proxyGenerator);
        $this->generateDecreaser($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(): string
    {
        return '$dbData = null === $objectData ? null : (int) $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (int) $dbData;';
    }
}
