<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

use Tequila\MongoDB\ODM\Code\FileGenerator;
use Tequila\MongoDB\ODM\DocumentManager;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\NestedProxyGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\RootProxyGenerator;

class GeneratorFactory extends AbstractFactory
{
    /**
     * @var AbstractGenerator[]
     */
    private $generatorsCache = [];

    /**
     * @var array
     */
    private $proxyClassNames = [];

    /**
     * @param DocumentManager $documentManager
     * @param string          $documentClass
     *
     * @return string
     */
    public function getProxyClass(DocumentManager $documentManager, string $documentClass): string
    {
        if (array_key_exists($documentClass, $this->proxyClassNames)) {
            return $this->proxyClassNames[$documentClass];
        }

        $proxyClassName = $this->getProxyClassName($documentClass);
        $proxyGenerator = $this->getGenerator($documentManager, $documentClass);
        $classGenerator = $proxyGenerator->generateClass();
        $proxyFile = $this->getProxyFileName($proxyClassName);
        $proxyDir = dirname($proxyFile);
        is_dir($proxyDir) || mkdir($proxyDir, 0777, true);
        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);
        $code = $fileGenerator->generate();
        file_put_contents($proxyFile, $code);

        $this->proxyClassNames[$documentClass] = $proxyClassName;

        return $proxyClassName;
    }

    /**
     * @param DocumentManager $documentManager
     * @param string          $documentClass
     *
     * @return AbstractGenerator
     */
    private function getGenerator(DocumentManager $documentManager, string $documentClass): AbstractGenerator
    {
        if (!array_key_exists($documentClass, $this->generatorsCache)) {
            $metadata = $documentManager->getMetadata($documentClass);
            $this->generatorsCache[$documentClass] = $metadata->isNested()
                ? new NestedProxyGenerator($documentManager, $metadata, $this, $this->proxiesNamespace)
                : new RootProxyGenerator($documentManager, $metadata, $this, $this->proxiesNamespace);
        }

        return $this->generatorsCache[$documentClass];
    }
}
