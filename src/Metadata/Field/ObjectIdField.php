<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

class ObjectIdField extends AbstractFieldMetadata
{
    /**
     * @var bool
     */
    private $generateIfNotSet;

    /**
     * @param string $propertyName
     * @param string $dbFieldName
     * @param bool   $generateIfNotSet
     */
    public function __construct(string $propertyName, string $dbFieldName = null, bool $generateIfNotSet = false)
    {
        $this->generateIfNotSet = $generateIfNotSet;
        parent::__construct($propertyName, $dbFieldName);
    }

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $documentGenerator->addUse(ObjectID::class);

        parent::generateDocument($documentGenerator);
    }

    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse(ObjectID::class);

        parent::generateProxy($proxyGenerator);
    }

    public function getType(): string
    {
        return ObjectID::class;
    }

    public function getSerializationCode(): string
    {
        if ($this->generateIfNotSet) {
            $code = <<<'EOT'
$objectData = $objectData ?? new ObjectID();
$dbData = $objectData;
EOT;
        } else {
            $code = parent::getSerializationCode();
        }

        return $code;
    }
}
