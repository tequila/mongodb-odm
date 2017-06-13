<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;

class FloatField extends AbstractFieldMetadata
{
    public function generateProxy(ProxyGenerator $proxyGenerator): void
    {
        $this->generateIncreaser($proxyGenerator);
        $this->generateDecreaser($proxyGenerator);

        parent::generateProxy($proxyGenerator);
    }

    public function getSerializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$target = null === $value ? null : (float) $value;';
    }

    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string
    {
        return '$objectData = null === $dbData ? null : (float) $dbData;';
    }

    public function getTypeHint(): string
    {
        return 'float';
    }

    public function getReturnType(): string
    {
        return '?float';
    }

    protected function generateIncreaser(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'increase'.StringUtil::camelize($this->propertyName);
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
$this->getRootDocument()->increment($this->getPathInDocument('{{dbFieldName}}'), abs(${{paramName}}));
EOT;
        $code = self::compileCode($code, [
            'methodName' => $methodName,
            'paramName' => current($method->getParameters())->getName(),
            'dbFieldName' => $this->dbFieldName,
        ]);

        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }

    protected function generateDecreaser(ProxyGenerator $proxyGenerator)
    {
        $methodName = 'decrease'.StringUtil::camelize($this->propertyName);
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
$this->getRootDocument()->increment($this->getPathInDocument('{{dbFieldName}}'), -abs(${{paramName}}));
EOT;
        $code = self::compileCode($code, [
            'methodName' => $methodName,
            'paramName' => current($method->getParameters())->getName(),
            'dbFieldName' => $this->dbFieldName,
        ]);

        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }
}
