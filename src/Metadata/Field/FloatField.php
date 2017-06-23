<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

class FloatField extends AbstractFieldMetadata
{
    use IncreaserDecreaserTrait;

    public function getType(): string
    {
        return 'float';
    }

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $this->generateIncreaser($documentGenerator);
        $this->generateDecreaser($documentGenerator);

        parent::generateDocument($documentGenerator);
    }

    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $this->generateIncreaserProxy($proxyGenerator);
        $this->generateDecreaserProxy($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(): string
    {
        return '$dbData = null === $objectData ? null : (float) $objectData;';
    }

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (float) $dbData;';
    }
}
