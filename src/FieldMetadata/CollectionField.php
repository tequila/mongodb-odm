<?php

namespace Tequila\MongoDB\ODM\FieldMetadata;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Generator\DocumentGenerator;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Generator\ProxyGenerator;
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

        $collectionItemParam = new ParameterGenerator(
            StringUtil::camelize($this->itemMetadata->getPropertyName(), false)
        );
        $collectionItemParam->setType($this->itemMetadata->getType());

        $adder = new MethodGenerator('add'.StringUtil::camelize($this->itemMetadata->getPropertyName()));
        $adder->setParameter($collectionItemParam);
        $adder->setBody(
            sprintf('$this->%s[] = $%s;', $this->getPropertyName(), $collectionItemParam->getName())
        );

        $remover = new MethodGenerator('remove'.StringUtil::camelize($this->itemMetadata->getPropertyName()));
        $remover->setParameter($collectionItemParam);
        $removerBody = <<<'EOT'
foreach ($this->{{property}} as $key => $item) {
    if (${{param}} === $item) {
        $this->{{property}}[$key] = null;
    }
}
EOT;
        $removerBody = self::compileCode($removerBody, [
            'property' => $this->getPropertyName(),
            'param' => $collectionItemParam->getName(),
        ]);

        $remover->setBody($removerBody);

        $documentGenerator->addMethod($adder);
        $documentGenerator->addMethod($remover);

        parent::generateDocument($documentGenerator);
    }

    public function generateProxy(ProxyGenerator $proxyGenerator)
    {
        $proxyGenerator->addUse(InvalidArgumentException::class);

        $this->generateAdderProxy($proxyGenerator);
        $this->generateRemoverProxy($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getType(): string
    {
        return 'array';
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
        $itemUnserializationCode = $this->itemMetadata->getUnserializationCode($proxyGenerator);
        $itemUnserializationCode = strtr($itemUnserializationCode, [
            '$dbData' => '$item',
            '$objectData' => '$unserializedItem',
        ]);

        if ($this->itemMetadata instanceof DocumentField) {
            $code = <<<'EOT'
$dbData = (array) $dbData;
$objectData = [];
foreach ($dbData as $key => $item) {
    $item['_pathInDocument'] = $this->getPathInDocument('{{dbField}}').'.'.$key;
    {{itemUnserializationCode}}
    $objectData[$key] = $unserializedItem;
}
EOT;
        } else {
            $code = <<<'EOT'
$dbData = (array) $dbData;
$objectData = [];
foreach ($dbData as $key => $item) {
    {{itemUnserializationCode}}
    $objectData[$key] = $unserializedItem;
}
EOT;
        }

        return self::compileCode($code, [
            'itemUnserializationCode' => $itemUnserializationCode,
            'dbField' => $this->dbFieldName,
        ]);
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
        $property->setDocBlock('@var '.$itemType.'[]');

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
$this->getRootDocument()->push($this->getPathInDocument('{{dbField}}'), ${{param}});
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
            $itemClassMetadata = $proxyGenerator->getMetadataFactory()->getClassMetadata($itemClassName);
            $idField = $itemClassMetadata->getPrimaryKeyField();
            $itemReflection = new \ReflectionClass($itemClassName);
            if (
                $itemReflection->hasProperty($idField->getPropertyName())
                && $itemReflection->getProperty($idField->getPropertyName())->isPublic()
            ) {
                $idRetrievingCode = '${{param}}->'.$idField->getPropertyName();
            } elseif (
                $itemReflection->hasMethod($getterMethod = 'get'.StringUtil::camelize($idField->getPropertyName()))
                && $itemReflection->getMethod($getterMethod)->isPublic()
            ) {
                $idRetrievingCode = '${{param}}->'.$getterMethod.'()';
            } else {
                throw new LogicException(
                    sprintf(
                        'Mongo id cannot be retrieved from %s. This class must contain public property %s or public method %s()',
                        $proxyGenerator->getMetadata()->getDocumentClass(),
                        $idField->getPropertyName(),
                        $getterMethod
                    )
                );
            }

            $idRetrievingCode = self::compileCode($idRetrievingCode, ['param' => $paramName]);

            $code = <<<'EOT'
/** @var {{proxyClass}} ${{param}} */
if (null === {{idRetrievingCode}}) {
    throw new InvalidArgumentException(
        'Cannot remove new {{itemClass}} instance using {{documentClass}}::{{method}}(), {{itemClass}} instance must have an id.'
    );
}

parent::{{method}}(${{param}});
$this->getRootDocument()->pull(
    $this->getPathInDocument('{{dbField}}'), 
    ['_id' => {{idRetrievingCode}}]
);
EOT;
            $proxyClass = $proxyGenerator->getFactory()->getGenerator($itemClassName, false)->getProxyClass();
            $proxyClassParts = explode('\\', $proxyClass);
            $params['proxyClass'] = end($proxyClassParts);
            $params['documentClass'] = $proxyGenerator->getDocumentClass();
            $params['itemClass'] = $this->itemMetadata->getDocumentClass();
            $params['idRetrievingCode'] = $idRetrievingCode;
        } else {
            $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootDocument()->pull($this->getPathInDocument('{{dbField}}'), ${{param}});
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
