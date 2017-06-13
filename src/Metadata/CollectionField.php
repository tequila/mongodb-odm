<?php

namespace Tequila\MongoDB\ODM\Metadata;

use MongoDB\BSON\ObjectID;
use Tequila\MongoDB\ODM\Exception\InvalidArgumentException;
use Tequila\MongoDB\ODM\Proxy\ProxyGeneratorFactory;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;

class CollectionField extends AbstractFieldMetadata
{
    /**
     * @var FieldMetadataInterface
     */
    private $itemMetadata;

    /**
     * @var ProxyGeneratorFactory
     */
    private $proxyGeneratorFactory;

    /**
     * @param string                 $propertyName
     * @param FieldMetadataInterface $itemMetadata
     * @param string|null            $dbFieldName
     */
    public function __construct(string $propertyName, FieldMetadataInterface $itemMetadata, string $dbFieldName = null)
    {
        $this->itemMetadata = $itemMetadata;

        parent::__construct($propertyName, $dbFieldName, '[]');
    }

    public function generateProxy(ProxyGenerator $proxyGenerator): void
    {
        $this->generateAdder($proxyGenerator);
        $this->generateRemover($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        $itemSerializationCode = $this->itemMetadata->getSerializationCode($proxyGenerator);
        $itemSerializationCode = strtr($itemSerializationCode, [
            '$objectData' => '$item',
            '$dbData' => '$serializedItem',
        ]);
        $code = <<<'EOT'
$dbData = (array) $dbData;
foreach ($value as $key => $item) {
    {{itemSerializationCode}}
    $dbData[$key] = $serializedItem;
}
EOT;

        return self::compileCode($code, [
            'itemSerializationCode' => $itemSerializationCode,
        ]);
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        $itemUnserializationCode = $this->itemMetadata->getUnserializationCode($proxyGenerator);
        $itemUnserializationCode = strtr($itemUnserializationCode, [
            '$dbData' => '$item',
            '$objectData' => '$serializedItem',
        ]);

        if ($this->itemMetadata instanceof DocumentField) {
            $code = <<<'EOT'
$dbData = (array) $dbData;
$objectData = [];
foreach ($dbData as $key => $item) {
    $item['_pathInDocument'] = $this->getPathInDocument('{{dbField}}').'.'.$key;
    {{itemUnserializationCode}}
    $objectData[$key] = $serializedItem;
}
EOT;
        } else {
            $code = <<<'EOT'
$dbData = (array) $dbData;
$objectData = [];
foreach ($dbData as $key => $item) {
    {{itemUnserializationCode}}
    $objectData[$key] = $serializedItem;
}
EOT;
        }

        return self::compileCode($code, [
            'itemUnserializationCode' => $itemUnserializationCode,
            'dbField' => $this->dbFieldName,
        ]);
    }

    public function getTypeHint(): string
    {
        return 'array';
    }

    public function getReturnType(): string
    {
        return '?string';
    }

    /**
     * @param ProxyGeneratorFactory $factory
     */
    public function setProxyGeneratorFactory(ProxyGeneratorFactory $factory)
    {
        $this->proxyGeneratorFactory = $factory;

        if (method_exists($this->itemMetadata, 'setProxyGeneratorFactory')) {
            $this->itemMetadata->setProxyGeneratorFactory($factory);
        }
    }

    private function generateAdder(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'add'.StringUtil::camelize($this->itemMetadata->getPropertyName());
        if (!$proxyGenerator->getReflection()->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it have not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName
                )
            );

            return;
        }

        $methodReflection = $proxyGenerator->getReflection()->getMethod($methodName);
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

        $code = '';

        if ($this->itemMetadata instanceof DocumentField) {
            $code .= <<<'EOT'
/** @var {{proxyClass}} ${{paramName}} */
if (!${{paramName}}->getMongoId()) {
    ${{paramName}}->setMongoId(new ObjectID());
}
EOT;

            $proxyGenerator->getProxyClassGenerator()->addUse(ObjectID::class);
            $proxyClass = $this->proxyGeneratorFactory->getProxyClass(
                $this->itemMetadata->getDocumentClass(),
                false
            );
            $proxyClassParts = explode('\\', $proxyClass);
            $params['proxyClass'] = end($proxyClassParts);
        }

        $code .= PHP_EOL.<<<'EOT'
parent::{{methodName}}(${{paramName}});
$this->getRootDocument()->push($this->getPathInDocument('{{dbFieldName}}'), ${{paramName}});
EOT;

        $params += [
            'methodName' => $methodName,
            'paramName' => current($method->getParameters())->getName(),
            'dbFieldName' => $this->dbFieldName,
        ];

        $code = self::compileCode($code, $params);
        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }

    private function generateRemover(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'remove'.StringUtil::camelize($this->itemMetadata->getPropertyName());
        if (!$proxyGenerator->getReflection()->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it have not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName
                )
            );

            return;
        }

        $methodReflection = $proxyGenerator->getReflection()->getMethod($methodName);
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

        if ($this->itemMetadata instanceof DocumentField) {
            $code = <<<'EOT'
/** @var {{proxyClass}} ${{paramName}} */
if (!${{paramName}}->getMongoId()) {
    throw new InvalidArgumentException(
        'Trying to remove new {{itemClass}} instance using {{documentClass}}::{{methodName}}(). {{itemClass}} instance must have an id.'
    );
}

parent::{{methodName}}(${{paramName}});
$this->getRootDocument()->pull(
    $this->getPathInDocument('{{dbFieldName}}'), 
    ['_id' => ${{paramName}}->getMongoId()]
);
EOT;
            $proxyGenerator->getProxyClassGenerator()->addUse(InvalidArgumentException::class);

            $proxyGenerator->getProxyClassGenerator()->addUse(ObjectID::class);
            $proxyClass = $this->proxyGeneratorFactory->getProxyClass(
                $this->itemMetadata->getDocumentClass(),
                false
            );
            $proxyClassParts = explode('\\', $proxyClass);
            $params['proxyClass'] = end($proxyClassParts);
            $params['documentClass'] = $proxyGenerator->getDocumentClass();
            $params['itemClass'] = $this->itemMetadata->getDocumentClass();
        } else {
            $code = <<<'EOT'
parent::{{methodName}}(${{paramName}});
$this->getRootDocument()->pull($this->getPathInDocument('{{dbFieldName}}'), ${{paramName}});
EOT;
        }

        $params += [
            'methodName' => $methodName,
            'paramName' => current($method->getParameters())->getName(),
            'dbFieldName' => $this->dbFieldName,
        ];
        $method->setBody(self::compileCode($code, $params));

        $proxyGenerator->addMethod($method);
    }
}
