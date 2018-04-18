<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use DateTimeInterface;
use MongoDB\BSON\UTCDateTime;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

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

    /**
     * @param AbstractGenerator $proxyGenerator
     * @throws \ReflectionException
     */
    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse(UTCDateTime::class);
        $proxyGenerator->addUse(DateTimeInterface::class);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(): string
    {
        return '$dbData = $objectData instanceof DateTimeInterface 
                    ? new UTCDateTime($objectData) 
                    : $objectData;';
    }

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return '$objectData = $dbData instanceof UTCDateTime ? $dbData->toDateTime() : $dbData;';
    }
}
