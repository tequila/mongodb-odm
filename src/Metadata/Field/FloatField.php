<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class FloatField extends AbstractFieldMetadata
{
    use IncreaserDecreaserTrait;

    public function getType(): string
    {
        return 'float';
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
    {
        $this->generateIncreaser($proxyGenerator);
        $this->generateDecreaser($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(): string
    {
        return '$dbData = null === $objectData ? null : (float) $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (float) $dbData;';
    }
}
