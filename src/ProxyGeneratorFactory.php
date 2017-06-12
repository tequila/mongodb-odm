<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Code\FileGenerator;
use Tequila\MongoDB\ODM\Generator\ProxyGenerator;

class ProxyGeneratorFactory implements ProxyFactoryInterface
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
     * @var string
     */
    private $proxiesDir;

    /**
     * @var string
     */
    private $proxiesNamespace;

    /**
     * @var array
     */
    private $proxyClassNames = [];

    /**
     * @var bool
     */
    private $requireGeneratedFiles;

    /**
     * @param MetadataFactoryInterface $metadataFactory
     * @param string                   $proxiesDir
     * @param string                   $proxiesNamespace
     * @param bool                     $requireGeneratedFiles
     */
    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        string $proxiesDir,
        string $proxiesNamespace,
        bool $requireGeneratedFiles = false
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->proxiesDir = rtrim($proxiesDir, '/');
        $this->proxiesNamespace = rtrim($proxiesNamespace, '\\');
        $this->requireGeneratedFiles = $requireGeneratedFiles;
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

    public function getProxyClass(string $documentClass, bool $asRootDocument = true): string
    {
        $proxyGenerator = $this->getGenerator($documentClass, $asRootDocument);

        $proxyClassName = $proxyGenerator->getProxyClass();
        if (array_key_exists($proxyClassName, $this->proxyClassNames)) {
            return $this->proxyClassNames[$proxyClassName];
        }
        $classGenerator = $proxyGenerator->generateClass();

        $fileName = $classGenerator->getName().'.php';

        $parts = explode('\\', $documentClass);
        array_pop($parts); // delete class name - $parts must contain only namespace parts
        $relativePath = implode('/', $parts);
        $proxyDir = $this->proxiesDir.'/'.$relativePath;
        is_dir($proxyDir) || mkdir($proxyDir, 0777, true);
        $fullPath = $proxyDir.'/'.$fileName;

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);
        $code = $fileGenerator->generate();

        file_put_contents($fullPath, $code);

        $this->proxyClassNames[$proxyClassName] = true;

        if ($this->requireGeneratedFiles) {
            require_once $fullPath;
        }

        return $proxyClassName;
    }
}
