<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

use Tequila\MongoDB\ODM\Code\FileGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;
use Tequila\MongoDB\ODM\Proxy\Generator\NestedProxyGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\RootProxyGenerator;

class GeneratorFactory extends AbstractFactory
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var RootProxyGenerator[]
     */
    private $rootGeneratorsCache = [];

    /**
     * @var NestedProxyGenerator[]
     */
    private $nestedGeneratorsCache = [];

    /**
     * @var array
     */
    private $proxyClassNames = [];

    /**
     * @param string                   $proxiesDir
     * @param string                   $proxiesNamespace
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(
        string $proxiesDir,
        string $proxiesNamespace,
        MetadataFactoryInterface $metadataFactory
    ) {
        parent::__construct($proxiesDir, $proxiesNamespace);
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param string $documentClass
     * @param bool   $isRootProxy
     *
     * @return AbstractGenerator
     */
    public function getGenerator(string $documentClass, bool $isRootProxy = true): AbstractGenerator
    {
        return $isRootProxy
            ? $this->getRootGenerator($documentClass)
            : $this->getNestedGenerator($documentClass);
    }

    /**
     * @param string $documentClass
     * @param bool   $isRootProxy
     *
     * @return string
     */
    public function getProxyClass(string $documentClass, bool $isRootProxy = true): string
    {
        $proxyClassName = parent::getProxyClass($documentClass, $isRootProxy);
        if (array_key_exists($proxyClassName, $this->proxyClassNames)) {
            return $this->proxyClassNames[$proxyClassName];
        }

        $proxyGenerator = $this->getGenerator($documentClass, $isRootProxy);
        $classGenerator = $proxyGenerator->generateClass();
        $proxyFile = $this->getProxyFileName($proxyClassName);
        $proxyDir = dirname($proxyFile);
        is_dir($proxyDir) || mkdir($proxyDir, 0777, true);
        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);
        $code = $fileGenerator->generate();
        file_put_contents($proxyFile, $code);

        $this->proxyClassNames[$proxyClassName] = null;

        return $proxyClassName;
    }

    private function getRootGenerator(string $documentClass): RootProxyGenerator
    {
        if (!array_key_exists($documentClass, $this->rootGeneratorsCache)) {
            $this->rootGeneratorsCache[$documentClass] = new RootProxyGenerator(
                $documentClass,
                $this->metadataFactory,
                $this,
                $this->proxiesNamespace
            );
        }

        return $this->rootGeneratorsCache[$documentClass];
    }

    private function getNestedGenerator(string $documentClass): NestedProxyGenerator
    {
        if (!array_key_exists($documentClass, $this->nestedGeneratorsCache)) {
            $this->nestedGeneratorsCache[$documentClass] = new NestedProxyGenerator(
                $documentClass,
                $this->metadataFactory,
                $this,
                $this->proxiesNamespace
            );
        }

        return $this->nestedGeneratorsCache[$documentClass];
    }
}
