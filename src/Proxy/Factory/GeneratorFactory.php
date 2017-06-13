<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

use Tequila\MongoDB\ODM\Code\FileGenerator;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;

class GeneratorFactory extends AbstractFactory
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var ProxyGenerator[]
     */
    private $rootGeneratorsCache = [];

    /**
     * @var ProxyGenerator[]
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
     * @return ProxyGenerator
     */
    public function getGenerator(string $documentClass, bool $isRootProxy = true): ProxyGenerator
    {
        $cache = $isRootProxy ? $this->rootGeneratorsCache : $this->nestedGeneratorsCache;
        if (!array_key_exists($documentClass, $cache)) {
            $cache[$documentClass] = new ProxyGenerator(
                $documentClass,
                $this->metadataFactory,
                $this,
                $this->proxiesNamespace,
                $isRootProxy
            );
        }

        if ($isRootProxy) {
            $this->rootGeneratorsCache = $cache;
        } else {
            $this->nestedGeneratorsCache = $cache;
        }

        return $cache[$documentClass];
    }

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
}
