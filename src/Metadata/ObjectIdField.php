<?php

namespace Tequila\MongoDB\ODM\Metadata;

use Tequila\MongoDB\ODM\Exception\LogicException;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;
use Tequila\MongoDB\ODM\Util\StringUtil;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;

class ObjectIdField extends RawField
{
    public function __construct($propertyName, bool $canBeGenerated = true, $dbFieldName = null, $defaultValue = 'null')
    {
        parent::__construct($propertyName, $dbFieldName, $defaultValue);
    }

    public function generateProxy(ProxyGenerator $proxyGenerator): void
    {
        parent::generateProxy($proxyGenerator);

        $this->generateGetMongoIdMethod($proxyGenerator);
        $this->generateSetMongoIdMethod($proxyGenerator);
    }

    public function getTypeHint(): string
    {
        return 'ObjectID';
    }

    public function getReturnType(): string
    {
        return '?ObjectID';
    }

    private function generateGetMongoIdMethod(ProxyGenerator $proxyGenerator)
    {
        $reflection = $proxyGenerator->getReflection();
        if (!$reflection->hasMethod('getMongoId')) {
            if (
                $reflection->hasProperty($this->propertyName)
                && !$reflection->getProperty($this->propertyName)->isPrivate()
            ) {
                $mongoIdCode = 'return $this->'.$this->propertyName.';';
            } elseif (
                $reflection->hasMethod($methodName = 'get'.StringUtil::camelize($this->propertyName))
                && !$reflection->getMethod($methodName)->isPrivate()
            ) {
                $mongoIdCode = 'return $this->'.$methodName.'();';
            } else {
                throw new LogicException(
                    sprintf(
                        'Mongo id cannot be retrieved from %s. This class must contain not private property %s or not private method %s()',
                        $proxyGenerator->getMetadata()->getDocumentClass(),
                        $this->propertyName,
                        $methodName
                    )
                );
            }

            $method = new MethodGenerator('getMongoId');
            $method->setBody($mongoIdCode);
            $proxyGenerator->addMethod($method);
        }
    }

    private function generateSetMongoIdMethod(ProxyGenerator $proxyGenerator)
    {
        $reflection = $proxyGenerator->getReflection();
        if (!$reflection->hasMethod('setMongoId')) {
            if (
                $reflection->hasProperty($this->propertyName)
                && !$reflection->getProperty($this->propertyName)->isPrivate()
            ) {
                $mongoIdCode = '$this->'.$this->propertyName.' = $mongoId;';
            } elseif (
                $reflection->hasMethod($methodName = 'set'.StringUtil::camelize($this->propertyName))
                && !$reflection->getMethod($methodName)->isPrivate()
            ) {
                $mongoIdCode = '$this->'.$methodName.'($mongoId);';
            } else {
                throw new LogicException(
                    sprintf(
                        'Mongo id cannot be set to %s. This class must contain not private property %s or not private method %s()',
                        $proxyGenerator->getMetadata()->getDocumentClass(),
                        $this->propertyName,
                        $methodName
                    )
                );
            }

            $method = new MethodGenerator('setMongoId');
            $method->setParameter(new ParameterGenerator('mongoId'));
            $method->setBody($mongoIdCode);
            $proxyGenerator->addMethod($method);
        }
    }
}
