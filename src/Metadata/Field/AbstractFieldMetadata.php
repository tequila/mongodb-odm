<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

abstract class AbstractFieldMetadata implements FieldMetadataInterface
{
    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string
     */
    protected $dbFieldName;

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     */
    public function __construct(string $propertyName, string $dbFieldName = null)
    {
        $this->propertyName = $propertyName;
        $this->dbFieldName = $dbFieldName ?: $propertyName;
    }

    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        $documentGenerator->addProperty($this->createProperty());
        $documentGenerator->addMethod($this->createGetter());
        $documentGenerator->addMethod($this->createSetter());
    }

    /**
     * @param AbstractGenerator $proxyGenerator
     */
    public function generateProxy(AbstractGenerator $proxyGenerator)
    {
        $this->generateSetterProxy($proxyGenerator);
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getDbFieldName(): string
    {
        return $this->dbFieldName;
    }

    /**
     * @return string
     */
    public function getSerializationCode(): string
    {
        return '$dbData = $objectData;';
    }

    /**
     * @param AbstractGenerator $proxyGenerator
     *
     * @return string
     */
    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string
    {
        return '$objectData = $dbData;';
    }

    /**
     * @return PropertyGenerator
     */
    protected function createProperty(): PropertyGenerator
    {
        $property = new PropertyGenerator($this->propertyName);
        $property->setVisibility(PropertyGenerator::VISIBILITY_PRIVATE);
        $type = $this->getType();
        if (!self::isInternalType($type) && false !== strpos($type, '\\')) {
            $type = '\\'.$type;
        }

        $property->setDocBlock('@var '.$type);

        return $property;
    }

    /**
     * @return MethodGenerator
     */
    protected function createGetter(): MethodGenerator
    {
        $method = new MethodGenerator('get'.StringUtil::camelize($this->propertyName));
        $method->setReturnType('?'.$this->getType());
        $method->setBody(sprintf('return $this->%s;', $this->propertyName));

        return $method;
    }

    protected function createSetter(): MethodGenerator
    {
        $method = new MethodGenerator('set'.StringUtil::camelize($this->propertyName));
        $paramName = StringUtil::camelize($this->propertyName, false);
        $param = new ParameterGenerator($paramName);
        $param->setType($this->getType());
        $method->setParameter($param);
        $code = sprintf('$this->%s = $%s;', $this->propertyName, $paramName);
        $code .= str_repeat(PHP_EOL, 2).'return $this;';
        $method->setBody($code);

        return $method;
    }

    protected function generateSetterProxy(AbstractGenerator $proxyGenerator)
    {
        $methodName = 'set'.StringUtil::camelize($this->propertyName);
        if (!$proxyGenerator->getReflection()->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it had not been generated in proxy.',
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
                    'Method %s::%s() must have 1 argument, but has %d arguments, therefore it had not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName,
                    count($methodReflection->getParameters())
                )
            );

            return;
        }

        $method = MethodGenerator::fromReflection($methodReflection);

        $code = <<<'EOT'
parent::{{methodName}}(${{paramName}});
$this->getRootProxy()->set($this->getPathInDocument('{{dbFieldName}}'), ${{paramName}});

return $this;
EOT;
        $code = self::compileCode($code, [
            'methodName' => $methodName,
            'paramName' => current($method->getParameters())->getName(),
            'dbFieldName' => $this->dbFieldName,
        ]);

        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }

    protected static function compileCode(string $code, array $params)
    {
        $placeholders = [];
        foreach ($params as $name => $value) {
            $placeholders['{{'.$name.'}}'] = $value;
        }

        return strtr($code, $placeholders);
    }

    private static function isInternalType(string $type): bool
    {
        static $internalTypes = [
            'int',
            'float',
            'string',
            'bool',
            'callable',
            'array',
            'iterable',
        ];

        return in_array($type, $internalTypes);
    }
}
