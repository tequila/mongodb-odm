<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Proxy\ProxyGeneratorFactory;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

class DocumentField extends AbstractFieldMetadata
{
    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var ProxyGeneratorFactory
     */
    private $proxyGeneratorFactory;

    /**
     * @param string      $propertyName
     * @param string      $documentClass
     * @param string|null $dbFieldName
     * @param mixed|null  $defaultValue
     */
    public function __construct(
        string $propertyName,
        string $documentClass,
        string $dbFieldName = null,
        string $defaultValue = 'null'
    ) {
        $this->documentClass = $documentClass;

        parent::__construct($propertyName, $dbFieldName, $defaultValue);
    }

    /**
     * @return string
     */
    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$dbData = null === $objectData ? null : $objectData->bsonSerialize();';
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
    : \Tequila\MongoDB\applyTypeMap($dbData, ['root' => {{proxyClass}}::class, 'document' => 'array']);
EOT;

        $proxyClass = $this->proxyGeneratorFactory->getProxyClass($this->documentClass, false);
        $containingClassReflection = $proxyGenerator->getReflection();
        $thisClassGenerator = $this->proxyGeneratorFactory->getGenerator($this->documentClass);
        $thisClassReflection = $thisClassGenerator->getReflection();
        $proxyGenerator->getProxyClassGenerator()->addUse($this->documentClass);
        if ($thisClassReflection->getNamespaceName() !== $containingClassReflection->getNamespaceName()) {
            $proxyGenerator->getProxyClassGenerator()->addUse($proxyClass);
        }
        $proxyShortName = substr($proxyClass, strrpos($proxyClass, '\\'));

        return self::compileCode($code, [
            'dbField' => $this->dbFieldName,
            'proxyClass' => ltrim($proxyShortName, '\\'),
        ]);
    }

    public function getTypeHint(): string
    {
        $parts = explode('\\', $this->documentClass);

        return end($parts);
    }

    public function getReturnType(): string
    {
        return '?'.$this->getTypeHint();
    }

    public function setProxyGeneratorFactory(ProxyGeneratorFactory $factory)
    {
        $this->proxyGeneratorFactory = $factory;
    }
}
