<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use DateTimeInterface;
use MongoDB\BSON\UTCDateTime;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class DateField extends AbstractFieldMetadata
{
    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $documentGenerator->addUse(UTCDateTime::class);
        $documentGenerator->addUse(DateTimeInterface::class);

        parent::generateDocument($documentGenerator);
    }

    public function getType(): string
    {
        return DateTimeInterface::class;
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse(UTCDateTime::class);
        $proxyGenerator->addUse(DateTimeInterface::class);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(): string
    {
        return '$dbData = $objectData instanceof DateTimeInterface ? new UTCDateTime($objectData) : $objectData;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = $dbData instanceof UTCDateTime ? $dbData->toDateTime() : $dbData;';
    }
}
