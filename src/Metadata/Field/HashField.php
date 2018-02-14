<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class HashField extends AbstractArrayField
{
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
$dbData = (object)$dbData;
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
        $this->array[$index] = (array)$this->array[$index];
        
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

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        if ($this->itemMetadata instanceof DocumentField) {
            $documentGenerator->addUse($this->itemMetadata->getDocumentClass());
        }

        $camelizedItemPropertyName = StringUtil::camelize($this->itemMetadata->getPropertyName());
        $keyParam = new ParameterGenerator('key');
        $itemParam = new ParameterGenerator(lcfirst($camelizedItemPropertyName));
        $itemParam->setType($this->itemMetadata->getType());

        $adder = new MethodGenerator('add'.$camelizedItemPropertyName);
        $adder->setParameter($keyParam);
        $adder->setParameter($itemParam);
        $adderBody = sprintf(
            '$this->%s[$%s] = $%s;',
            $this->getPropertyName(),
            $keyParam->getName(),
            $itemParam->getName()
        );
        $adderBody .= str_repeat(PHP_EOL, 2);
        $adderBody .= 'return $this;';
        $adder->setBody($adderBody);

        $remover = new MethodGenerator('remove'.$camelizedItemPropertyName);
        $remover->setParameter($keyParam);
        $removerBody = <<<'EOT'
if (!is_array($this->{{property}})) {
    throw new \LogicException('Field {{property}} must be an array.');
}        

if (!array_key_exists(${{param}}, $this->{{property}})) {
    throw new \InvalidArgumentException(
        'Field "{{property}}" does not contain key '.${{param}}.'.'
    );
}
unset($this->{{property}}[${{param}}]);

return $this;
EOT;

        $removerBody = self::compileCode($removerBody, [
            'property' => $this->getPropertyName(),
            'param' => $keyParam->getName(),
            'item' => '_'.lcfirst($camelizedItemPropertyName),
        ]);

        $remover->setBody($removerBody);

        $documentGenerator->addMethod($adder);
        $documentGenerator->addMethod($remover);

        parent::generateDocument($documentGenerator);
    }

    protected function generateAdderProxy(AbstractGenerator $proxyGenerator)
    {
        $methodName = 'add'.StringUtil::camelize($this->itemMetadata->getPropertyName());
        $reflection = $proxyGenerator->getReflection();
        if (!$reflection->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it had not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName
                )
            );

            return;
        }

        $methodReflection = $reflection->getMethod($methodName);
        if (2 !== count($methodReflection->getParameters())) {
            $proxyGenerator->addError(
                sprintf(
                    'Method %s::%s() must have 2 arguments, but has %d arguments, therefore it had not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName,
                    count($methodReflection->getParameters())
                )
            );

            return;
        }

        $method = MethodGenerator::fromReflection($methodReflection);
        $params = [];

        $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->set(
    $this->getPathInDocument('{{dbField}}').'.'.${{keyParam}}, 
    ${{valueParam}}
);

return $this;
EOT;

        $methodParametrs = $method->getParameters();
        $params += [
            'method' => $methodName,
            'keyParam' => array_shift($methodParametrs)->getName(),
            'valueParam' => array_shift($methodParametrs)->getName(),
            'dbField' => $this->dbFieldName,
        ];

        $code = self::compileCode($code, $params);
        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }

    protected function generateRemoverProxy(AbstractGenerator $proxyGenerator)
    {
        $methodName = 'remove'.StringUtil::camelize($this->itemMetadata->getPropertyName());
        $reflection = $proxyGenerator->getReflection();
        if (!$reflection->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it had not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName
                )
            );

            return;
        }

        $methodReflection = $reflection->getMethod($methodName);
        if (2 !== count($methodReflection->getParameters())) {
            $proxyGenerator->addError(
                sprintf(
                    'Method %s::%s() must have 2 arguments, but has %d arguments, therefore it have not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName,
                    count($methodReflection->getParameters())
                )
            );

            return;
        }

        $method = MethodGenerator::fromReflection($methodReflection);
        $paramName = current($method->getParameters())->getName();
        $params = [];

        $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->unsetField(
    $this->getPathInDocument('{{dbField}}').'.'.${{param}}
);

return $this;
EOT;

        $params += [
            'method' => $methodName,
            'param' => $paramName,
            'dbField' => $this->dbFieldName,
        ];

        $method->setBody(self::compileCode($code, $params));

        $proxyGenerator->addMethod($method);
    }
}
