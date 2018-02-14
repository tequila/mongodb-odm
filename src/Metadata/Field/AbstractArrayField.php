<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Proxy\AbstractCollection;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Zend\Code\Generator\PropertyValueGenerator;

abstract class AbstractArrayField extends AbstractFieldMetadata
{
    /**
     * @var FieldMetadataInterface
     */
    protected $itemMetadata;

    /**
     * @param AbstractGenerator $generator
     *
     * @return mixed
     */
    abstract protected function generateAdderProxy(AbstractGenerator $generator);

    /**
     * @param AbstractGenerator $generator
     *
     * @return mixed
     */
    abstract protected function generateRemoverProxy(AbstractGenerator $generator);

    /**
     * @param FieldMetadataInterface $itemMetadata
     * @param string                 $propertyName
     * @param string|null            $dbFieldName
     */
    public function __construct(FieldMetadataInterface $itemMetadata, string $propertyName, string $dbFieldName = null)
    {
        $this->itemMetadata = $itemMetadata;

        parent::__construct($propertyName, $dbFieldName);
    }

    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse(InvalidArgumentException::class);
        $proxyGenerator->addUse(AbstractCollection::class);
        if ($this->itemMetadata instanceof DocumentField) {
            $proxyGenerator->addUse($this->itemMetadata->getDocumentClass());
        }

        $this->generateAdderProxy($proxyGenerator);
        $this->generateRemoverProxy($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getType(): string
    {
        return 'iterable';
    }

    public function getSerializationCode(): string
    {
        $itemSerializationCode = $this->itemMetadata->getSerializationCode();
        $itemSerializationCode = strtr($itemSerializationCode, [
            '$objectData' => '$item',
            '$dbData' => '$serializedItem',
        ]);

        $code = <<<'EOT'
$objectData = is_iterable($objectData) ? $objectData : [];
$dbData = [];
foreach ($objectData as $key => $item) {
    {{itemSerializationCode}}
    $dbData[$key] = $serializedItem;
}
EOT;

        return self::compileCode($code, ['itemSerializationCode' => $itemSerializationCode]);
    }

    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        $code = <<<'EOT'
$objectData = new class((array) $dbData, $rootProxy, $pathInDocument) extends AbstractCollection {

    private $unserializedDocuments = [];

    public function offsetGet($index)
    {        
        if (!array_key_exists($index, $this->unserializedDocuments)) {
            {{itemUnserializationCode}}

            $this->unserializedDocuments[$index] = null;
        }
        
        return $this->array[$index];
    }
};
EOT;

        $itemUnserializationCode = $this->itemMetadata->getUnserializationCode($proxyGenerator);
        $itemUnserializationCode = strtr($itemUnserializationCode, [
            '$dbData' => '$this->array[$index]',
            '$objectData' => '$this->array[$index]',
            '$pathInDocument' => '$this->path.\'.\'.$index',
            '$rootProxy' => '$this->root',
        ]);

        return self::compileCode($code, ['itemUnserializationCode' => $itemUnserializationCode]);
    }

    protected function createProperty(): PropertyGenerator
    {
        $property = parent::createProperty();
        $property->setDefaultValue(
            [],
            PropertyValueGenerator::TYPE_ARRAY_SHORT,
            PropertyValueGenerator::OUTPUT_SINGLE_LINE
        );

        $itemType = $this->itemMetadata instanceof DocumentField
            ? ('\\'.ltrim($this->itemMetadata->getDocumentClass(), '\\'))
            : $this->itemMetadata->getType();
        $property->setDocBlock('@var '.$itemType.'[]|iterable');

        return $property;
    }
}
