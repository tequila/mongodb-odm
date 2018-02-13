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
        $methodName = 'increase'.StringUtil::camelize($this->getPropertyName(), true);
        $method = new MethodGenerator($methodName);
        $param = new ParameterGenerator('number', $this->getType());
        $method->setParameter($param);
        $body = sprintf(
            '$this->%s += abs($%s);',
            $this->getPropertyName(),
            $param->getName()
        );
        $body .= str_repeat(PHP_EOL, 2).'return $this;';
        $method->setBody($body);
        $documentGenerator->addMethod($method);
    }

    protected function generateDecreaser(DocumentGenerator $documentGenerator)
    {
        $methodName = 'decrease'.StringUtil::camelize($this->getPropertyName(), true);
        $method = new MethodGenerator($methodName);
        $param = new ParameterGenerator('number', $this->getType());
        $method->setParameter($param);
        $body = sprintf(
            '$this->%s -= abs($%s);',
            $this->getPropertyName(),
            $param->getName()
        );
        $body .= str_repeat(PHP_EOL, 2).'return $this;';
        $method->setBody($body);
        $documentGenerator->addMethod($method);
    }

    protected function generateIncreaserProxy(AbstractGenerator $proxyGenerator)
    {
        $methodName = 'increase'.StringUtil::camelize($this->propertyName);
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
$this->getRootProxy()->increment($this->getPathInDocument('{{dbFieldName}}'), abs(${{paramName}}));

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

    protected function generateDecreaserProxy(AbstractGenerator $proxyGenerator)
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
}
