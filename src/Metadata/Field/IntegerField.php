<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class IntegerField extends AbstractFieldMetadata
{
    use IncreaserDecreaserTrait;

    public function getType(): string
    {
        return 'int';
    }

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $this->generateIncreaser($documentGenerator);
        $this->generateDecreaser($documentGenerator);

        parent::generateDocument($documentGenerator);
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
    {
        $this->generateIncreaserProxy($proxyGenerator);
        $this->generateDecreaserProxy($proxyGenerator);

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
