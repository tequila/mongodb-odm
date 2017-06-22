<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\DocumentInterface;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Proxy\AbstractCollection;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyValueGenerator;

class CollectionField extends AbstractFieldMetadata
{
    /**
     * @var FieldMetadataInterface
     */
    private $itemMetadata;

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

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        if ($this->itemMetadata instanceof DocumentField) {
            $documentGenerator->addUse($this->itemMetadata->getDocumentClass());
        }

        $camelizedItemPropertyName = StringUtil::camelize($this->itemMetadata->getPropertyName());
        $adderParam = new ParameterGenerator(lcfirst($camelizedItemPropertyName));
        $adderParam->setType($this->itemMetadata->getType());

        $adder = new MethodGenerator('add'.$camelizedItemPropertyName);
        $adder->setParameter($adderParam);
        $adder->setBody(
            sprintf('$this->%s[] = $%s;', $this->getPropertyName(), $adderParam->getName())
        );

        $remover = new MethodGenerator('remove'.$camelizedItemPropertyName);
        $removerParam = new ParameterGenerator(lcfirst($camelizedItemPropertyName).'ToRemove');
        $remover->setParameter($removerParam);
        if ($this->itemMetadata instanceof DocumentField) {
            $removerBody = <<<'EOT'
foreach ($this->{{property}} as $key => ${{item}}) {
    if (${{param}} === ${{item}} || ${{param}} === ${{item}}->getMongoId()) {
        $this->{{property}}[$key] = null;
    }
}
EOT;
        } else {
            $removerBody = <<<'EOT'
foreach ($this->{{property}} as $key => ${{item}}) {
    if (${{param}} === ${{item}}) {
        $this->{{property}}[$key] = null;
    }
}
EOT;
        }

        $removerBody = self::compileCode($removerBody, [
            'property' => $this->getPropertyName(),
            'param' => $removerParam->getName(),
            'item' => lcfirst($camelizedItemPropertyName),
        ]);

        $remover->setBody($removerBody);

        if ($this->itemMetadata instanceof DocumentField) {
            $itemGetter = new MethodGenerator('get'.$camelizedItemPropertyName);
            $itemGetterParam = new ParameterGenerator(lcfirst($camelizedItemPropertyName).'Id');
            $itemGetter->setParameter($itemGetterParam);
            $itemGetter->setReturnType($this->itemMetadata->getType());
            $itemGetterBody = <<<'EOT'
foreach ($this->{{property}} as ${{item}}) {
    if (${{item}}->getMongoId() === ${{param}}) {
        return ${{item}};
    }
}

throw new InvalidArgumentException(
    sprintf('{{itemAlias}} with primary key "%s" is not found.', (string) ${{param}})
);
EOT;
            $documentGenerator->addUse(InvalidArgumentException::class);
            $itemGetterBody = self::compileCode($itemGetterBody, [
                'property' => $this->getPropertyName(),
                'param' => $itemGetterParam->getName(),
                'item' => lcfirst($camelizedItemPropertyName),
                'itemAlias' => $camelizedItemPropertyName,
            ]);
            $itemGetter->setBody($itemGetterBody);

            $documentGenerator->addMethod($itemGetter);
        }

        $documentGenerator->addMethod($adder);
        $documentGenerator->addMethod($remover);

        parent::generateDocument($documentGenerator);
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
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
$objectData = (array) $objectData;
$dbData = [];
foreach ($objectData as $key => $item) {
    {{itemSerializationCode}}
    $dbData[$key] = $serializedItem;
}
EOT;

        return self::compileCode($code, ['itemSerializationCode' => $itemSerializationCode]);
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        $code = <<<'EOT'
$objectData = new class($dbData, $rootProxy, $pathInDocument) extends AbstractCollection {

    public function offsetGet($index)
    {
        static $unserializedDocuments = [];
        if (!array_key_exists($index, $unserializedDocuments)) {
            {{itemUnserializationCode}}

            $unserializedDocuments[$index] = null;
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

    private function generateAdderProxy(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'add'.StringUtil::camelize($this->itemMetadata->getPropertyName());
        $reflection = $proxyGenerator->getDocumentReflection();
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
        if (1 !== count($methodReflection->getParameters())) {
            $proxyGenerator->addError(
                sprintf(
                    'Method %s::%s() must have 1 argument, but has %d arguments, therefore it have not been generated in proxy.',
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
$this->getRootProxy()->push($this->getPathInDocument('{{dbField}}'), ${{param}});
EOT;

        $params += [
            'method' => $methodName,
            'param' => current($method->getParameters())->getName(),
            'dbField' => $this->dbFieldName,
        ];

        $code = self::compileCode($code, $params);
        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }

    private function generateRemoverProxy(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'remove'.StringUtil::camelize($this->itemMetadata->getPropertyName());
        $reflection = $proxyGenerator->getDocumentReflection();
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
        if (1 !== count($methodReflection->getParameters())) {
            $proxyGenerator->addError(
                sprintf(
                    'Method %s::%s() must have 1 argument, but has %d arguments, therefore it have not been generated in proxy.',
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

        if ($this->itemMetadata instanceof DocumentField) {
            $itemClassName = $this->itemMetadata->getDocumentClass();

            $proxyGenerator->addUse(DocumentInterface::class);
            $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->pull(
    $this->getPathInDocument('{{dbField}}'), 
    ${{param}} instanceof DocumentInterface ? ['_id' => ${{param}}->getMongoId()] : ${{param}}
);
EOT;
            $proxyClass = $proxyGenerator->getFactory()->getGenerator($itemClassName, false)->getProxyClass();
            $proxyClassParts = explode('\\', $proxyClass);
            $params['proxyClass'] = end($proxyClassParts);
            $params['itemClass'] = $this->itemMetadata->getDocumentClass();
        } else {
            $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->pull($this->getPathInDocument('{{dbField}}'), ${{param}});
EOT;
        }

        $params += [
            'method' => $methodName,
            'param' => $paramName,
            'dbField' => $this->dbFieldName,
        ];

        $method->setBody(self::compileCode($code, $params));

        $proxyGenerator->addMethod($method);
    }
}
