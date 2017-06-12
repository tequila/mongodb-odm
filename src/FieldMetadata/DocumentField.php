<?php

namespace Tequila\MongoDB\ODM\FieldMetadata;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Generator\DocumentGenerator;
use Tequila\MongoDB\ODM\Generator\ProxyGenerator;

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

    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $documentGenerator->addUse($this->documentClass);

        parent::generateDocument($documentGenerator);
    }

    public function getType(): string
    {
        return $this->documentClass;
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse($this->getDocumentProxyClass($proxyGenerator));
        $proxyGenerator->addUse($this->documentClass);

        parent::generateProxy($proxyGenerator);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        $code = <<<'EOT'
isset($dbData['_pathInDocument']) || $dbData['_pathInDocument'] = $this->getPathInDocument('{{dbField}}');
$objectData = null === $dbData 
    ? null 
    : \Tequila\MongoDB\applyTypeMap($dbData, [
        'root' => {{proxyClass}}::class, 
        'document' => 'array'
    ]);
EOT;

        $proxyClass = $this->getDocumentProxyClass($proxyGenerator);
        $proxyShortName = substr($proxyClass, strrpos($proxyClass, '\\'));

        return self::compileCode($code, [
            'dbField' => $this->dbFieldName,
            'proxyClass' => ltrim($proxyShortName, '\\'),
        ]);
    }

    protected function createProperty(): PropertyGenerator
    {
        $property = parent::createProperty();
        $property->setDocBlock('@var \\'.ltrim($this->documentClass, '\\'));

        return $property;
    }

    private function getDocumentProxyClass(ProxyGenerator $proxyGenerator): string
    {
        return $proxyGenerator->getFactory()->getGenerator($this->documentClass, false)->getProxyClass();
    }
}
