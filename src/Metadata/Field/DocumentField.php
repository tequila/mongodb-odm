<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\NestedProxyInterface;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

class DocumentField extends AbstractFieldMetadata
{
    /**
     * @var string
     */
    private $documentClass;

    /**
     * @param string      $documentClass
     * @param string      $propertyName
     * @param string|null $dbFieldName
     */
    public function __construct(string $documentClass, string $propertyName, string $dbFieldName = null)
    {
        $this->documentClass = $documentClass;

        parent::__construct($propertyName, $dbFieldName);
    }

    /**
     * @return string
     */
    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    /**
     * @param DocumentGenerator $documentGenerator
     */
    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $documentGenerator->addUse($this->documentClass);

        parent::generateDocument($documentGenerator);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->documentClass;
    }

    /**
     * @param AbstractGenerator $proxyGenerator
     *
     * @throws \ReflectionException
     */
    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse($proxyGenerator->getOtherProxyClass($this->documentClass));
        $proxyGenerator->addUse($this->documentClass);
        $proxyGenerator->addUse(NestedProxyInterface::class);

        parent::generateProxy($proxyGenerator);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        $code = <<<'EOT'
$objectData = null === $dbData 
    ? null 
    : \MongoDB\apply_type_map_to_document($dbData, [
        'root' => {{proxyClass}}::class, 
        'document' => 'array'
    ]);
if ($objectData instanceof \Tequila\MongoDB\ODM\Proxy\NestedProxyInterface) {
    $objectData->setPathInDocument($pathInDocument);
    $objectData->setRootProxy($rootProxy);
    $objectData->doBsonUnserialize();
}
EOT;

        $proxyClass = $proxyGenerator->getOtherProxyClass($this->documentClass);
        $proxyShortName = substr($proxyClass, strrpos($proxyClass, '\\'));

        return self::compileCode($code, ['proxyClass' => ltrim($proxyShortName, '\\')]);
    }

    protected function createProperty(): PropertyGenerator
    {
        $property = parent::createProperty();
        $property->setDocBlock('@var \\'.ltrim($this->documentClass, '\\'));

        return $property;
    }
}
