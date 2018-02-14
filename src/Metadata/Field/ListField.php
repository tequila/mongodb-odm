<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\DocumentInterface;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class ListField extends AbstractArrayField
{
    public function generateDocument(DocumentGenerator $documentGenerator)
    {
        if ($this->itemMetadata instanceof DocumentField) {
            $documentGenerator->addUse($this->itemMetadata->getDocumentClass());
        }

        $camelizedItemPropertyName = StringUtil::camelize($this->itemMetadata->getPropertyName());
        $itemParam = new ParameterGenerator(lcfirst($camelizedItemPropertyName));
        $itemParam->setType($this->itemMetadata->getType());

        // TODO refactor adders and removers functionality:
        // We should use "collections" for consistent updates in documents and proxies
        $adder = new MethodGenerator('add'.$camelizedItemPropertyName);
        $adder->setParameter($itemParam);
        $adderBody = sprintf('$this->%s[] = $%s;', $this->getPropertyName(), $itemParam->getName());
        $adderBody .= str_repeat(PHP_EOL, 2);
        $adderBody .= 'return $this;';
        $adder->setBody($adderBody);

        $remover = new MethodGenerator('remove'.$camelizedItemPropertyName);
        $remover->setParameter($itemParam);
        $removerBody = <<<'EOT'
if (!is_array($this->{{property}})) {
    throw new \LogicException('Field {{property}} must be an array.');
}

foreach ($this->{{property}} as $key => ${{item}}) {
    if (${{param}} === ${{item}}) {
        $this->{{property}}[$key] = null;
    }
}

return $this;
EOT;

        $removerBody = self::compileCode($removerBody, [
            'property' => $this->getPropertyName(),
            'param' => $itemParam->getName(),
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
        $params = [];

        $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->push($this->getPathInDocument('{{dbField}}'), ${{param}});

return $this;
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
            $proxyGenerator->addUse(DocumentInterface::class);
            $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->pull(
    $this->getPathInDocument('{{dbField}}'), 
    ${{param}} instanceof DocumentInterface ? ['_id' => ${{param}}->getMongoId()] : ${{param}}
);

return $this;
EOT;
        } else {
            $code = <<<'EOT'
parent::{{method}}(${{param}});
$this->getRootProxy()->pull($this->getPathInDocument('{{dbField}}'), ${{param}});

return $this;
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
