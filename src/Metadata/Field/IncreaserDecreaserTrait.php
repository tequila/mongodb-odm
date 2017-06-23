<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

trait IncreaserDecreaserTrait
{
    protected function generateIncreaser(DocumentGenerator $documentGenerator)
    {
        $paramName = StringUtil::camelize($this->getPropertyName(), false);
        $method = new MethodGenerator('increase'.ucfirst($paramName));
        $param = new ParameterGenerator($paramName, $this->getType());
        $method->setParameter($param);
        $body = sprintf('$this->%s += abs($%s);', $this->getPropertyName(), $paramName);
        $method->setBody($body);
        $documentGenerator->addMethod($method);
    }

    protected function generateDecreaser(DocumentGenerator $documentGenerator)
    {
        $paramName = StringUtil::camelize($this->getPropertyName(), false);
        $method = new MethodGenerator('decrease'.ucfirst($paramName));
        $param = new ParameterGenerator($paramName, $this->getType());
        $method->setParameter($param);
        $body = sprintf('$this->%s -= abs($%s);', $this->getPropertyName(), $paramName);
        $method->setBody($body);
        $documentGenerator->addMethod($method);
    }

    protected function generateIncreaserProxy(AbstractGenerator $proxyGenerator)
    {
        $methodName = 'increase'.StringUtil::camelize($this->propertyName);
        if (!$proxyGenerator->getDocumentReflection()->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it had not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName
                )
            );

            return;
        }

        $methodReflection = $proxyGenerator->getDocumentReflection()->getMethod($methodName);
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
$this->getRootProxy()->increment($this->getPathInDocument('{{dbFieldName}}'), abs(${{paramName}}));
EOT;
        $code = self::compileCode($code, [
            'methodName' => $methodName,
            'paramName' => current($method->getParameters())->getName(),
            'dbFieldName' => $this->dbFieldName,
        ]);

        $method->setBody($code);

        $proxyGenerator->addMethod($method);
    }

    protected function generateDecreaserProxy(AbstractGenerator $proxyGenerator)
    {
        $methodName = 'decrease'.StringUtil::camelize($this->propertyName);
        if (!$proxyGenerator->getDocumentReflection()->hasMethod($methodName)) {
            $proxyGenerator->addError(
                sprintf(
                    'Document class %s does not contain method %s, therefore it have not been generated in proxy.',
                    $proxyGenerator->getDocumentClass(),
                    $methodName
                )
            );

            return;
        }

        $methodReflection = $proxyGenerator->getDocumentReflection()->getMethod($methodName);
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
$this->getRootProxy()->increment($this->getPathInDocument('{{dbFieldName}}'), -abs(${{paramName}}));
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
