<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;

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
     * @var string
     */
    protected $propertyDefaultValue;

    /**
     * @param string      $propertyName
     * @param string|null $dbFieldName
     * @param string      $defaultValue
     */
    public function __construct(string $propertyName, string $dbFieldName = null, string $defaultValue = 'null')
    {
        $this->propertyName = $propertyName;
        $this->dbFieldName = $dbFieldName ?: $propertyName;
        $this->propertyDefaultValue = $defaultValue;
    }

    /**
     * @param ProxyGenerator $proxyGenerator
     */
    public function generateProxy(ProxyGenerator $proxyGenerator): void
    {
        $this->generateSetter($proxyGenerator);
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

    public function getPropertyDefaultValue(): string
    {
        return $this->propertyDefaultValue;
    }

    protected function generateSetter(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'set'.StringUtil::camelize($this->propertyName);
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

        $code = <<<'EOT'
parent::{{methodName}}(${{paramName}});
$this->getRootDocument()->set($this->getPathInDocument('{{dbFieldName}}'), ${{paramName}});
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
}
