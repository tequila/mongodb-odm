<?php

namespace Tequila\MongoDB\ODM\Proxy\Generator;

use Tequila\MongoDB\ODM\DocumentManagerAwareInterface;
use Tequila\MongoDB\ODM\Proxy\RootProxyInterface;
use Tequila\MongoDB\ODM\Proxy\Traits\RootProxyTrait;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;
use Tequila\MongoDB\ODM\WriteModelInterface;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class RootProxyGenerator extends AbstractGenerator
{
    /**
     * @return string
     */
    public function getProxyClass(): string
    {
        return $this->proxyNamespace.'\\'.$this->getDocumentClass().'RootProxy';
    }

    /**
     * @return MethodGenerator
     */
    protected function createBsonUnserializeMethod(): MethodGenerator
    {
        $method = new MethodGenerator('bsonUnserialize');
        $method->setParameter(new ParameterGenerator('dbData', 'array'));

        $methodBody = $this->buildUnserializationDefaultsCode();

        foreach ($this->metadata->getFieldsMetadata() as $field) {
            $code = strtr($field->getUnserializationCode($this), [
                '$objectData' => '$objectData[\''.$field->getPropertyName().'\']',
                '$dbData' => sprintf('$dbData[\'%s\']', $field->getDbFieldName()),
                '$pathInDocument' => "'{$field->getDbFieldName()}'",
                '$rootProxy' => '$this->getRootProxy()',
            ]);

            $methodBody .= PHP_EOL.$code;
        }

        $methodBody .= PHP_EOL.'parent::bsonUnserialize($objectData);';

        $method->setBody($methodBody);

        return $method;
    }

    protected function getInterfaces(): array
    {
        return [
            RootProxyInterface::class,
            UpdateBuilderInterface::class,
            DocumentManagerAwareInterface::class,
            WriteModelInterface::class,
        ];
    }

    protected function getTraits(): array
    {
        return [RootProxyTrait::class];
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
$dbData += $defaults;
$objectData = [];
EOT;

        return sprintf($code, implode("    \n", $lines));
    }
}