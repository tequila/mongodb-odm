<?php

namespace Tequila\MongoDB\ODM\Metadata;

use DateTime;
use MongoDB\BSON\UTCDateTime;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class DateField extends AbstractFieldMetadata
{
    public function generateProxy(ProxyGenerator $proxyGenerator): void
    {
        $proxyGenerator->getProxyClassGenerator()->addUse(UTCDateTime::class);
        $proxyGenerator->getProxyClassGenerator()->addUse(DateTime::class);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$dbData = $objectData instanceof DateTime ? new UTCDateTime($objectData) : $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = $dbData instanceof UTCDateTime ? $dbData->toDateTime() : $dbData;';
    }

    public function getTypeHint(): string
    {
        return 'DateTime';
    }

    public function getReturnType(): string
    {
        return '?DateTime';
    }
}
