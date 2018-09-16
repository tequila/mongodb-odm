<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

class ObjectIdField extends AbstractFieldMetadata
{
    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $documentGenerator->addUse(ObjectID::class);
        $documentGenerator->addUse(InvalidArgumentException::class, 'MongoInvalidArgumentException');

        parent::generateDocument($documentGenerator);
    }

    /**
     * @param AbstractGenerator $proxyGenerator
     *
     * @throws \ReflectionException
     */
    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse(ObjectId::class);

        parent::generateProxy($proxyGenerator);
    }

    public function getType(): string
    {
        return 'string';
    }

    public function getSerializationCode(): string
    {
        return <<<'EOT'
if (null === $objectData) {
    $dbData = null;
} elseif (!$objectData instanceof ObjectId) {
    try {
        $dbData = new ObjectId((string)$objectData);
    } catch(MongoInvalidArgumentException $e) {
        $dbData = $objectData;
    }
} else {
    $dbData = $objectData;
}
EOT;
    }

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return <<<'EOT'
$objectData = null === $dbData ? null : (string) $dbData;
EOT;
    }
}
