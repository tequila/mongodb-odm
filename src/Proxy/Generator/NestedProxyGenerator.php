<?php

namespace Tequila\MongoDB\ODM\Proxy\Generator;

use MongoDB\BSON\Unserializable;
use Tequila\MongoDB\ODM\Proxy\NestedProxyInterface;
use Tequila\MongoDB\ODM\Proxy\Traits\NestedProxyTrait;
use Zend\Code\Generator\MethodGenerator;

class NestedProxyGenerator extends AbstractGenerator
{
    /**
     * @return string
     */
    public function getProxyClass(): string
    {
        return $this->proxyNamespace.'\\'.$this->getDocumentClass().'NestedProxy';
    }

    protected function createBsonUnserializeMethod(): MethodGenerator
    {
        $method = new MethodGenerator('doBsonUnserialize');
        $methodBody = $this->buildUnserializationDefaultsCode();

        foreach ($this->metadata->getFieldsMetadata() as $field) {
            $code = strtr($field->getUnserializationCode($this), [
                '$objectData' => '$objectData[\''.$field->getPropertyName().'\']',
                '$dbData' => sprintf('$this->_mongoDbData[\'%s\']', $field->getDbFieldName()),
                '$pathInDocument' => sprintf('$this->getPathInDocument(\'%s\')', $field->getDbFieldName()),
                '$rootProxy' => '$this->getRootProxy()',
            ]);

            $methodBody .= PHP_EOL.$code;
        }

        $methodBody .= PHP_EOL.'parent::bsonUnserialize($objectData);';
        $methodBody .= PHP_EOL.'$this->_mongoDbData = null;';

        $method->setBody($methodBody);

        return $method;
    }

    protected function getInterfaces(): array
    {
        return [
            NestedProxyInterface::class,
            Unserializable::class,
        ];
    }

    protected function getTraits(): array
    {
        return [NestedProxyTrait::class];
    }

    private function buildUnserializationDefaultsCode(): string
    {
        $lines = [];
        foreach ($this->metadata->getFieldsMetadata() as $fieldMetadata) {
            $lines[] = "'{$fieldMetadata->getDbFieldName()}' => null,";
        }

        $code = <<<'EOT'
$defaults = [
%s
]; 
$this->_mongoDbData += $defaults;
$objectData = [];
EOT;

        return sprintf($code, implode("    \n", $lines));
    }
}
