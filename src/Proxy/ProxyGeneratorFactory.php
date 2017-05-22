<?php

namespace Tequila\MongoDB\ODM\Proxy;

use Tequila\MongoDB\ODM\DocumentMetadataFactoryInterface;
use Zend\Code\Generator\FileGenerator;

class ProxyGeneratorFactory
{
    /**
     * @var DocumentMetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var ProxyGenerator[]
     */
    private $generatorsCache = [];

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

    private $requireGeneratedFiles;

    /**
     * @param DocumentMetadataFactoryInterface $metadataFactory
     * @param string                           $proxiesDir
     * @param string                           $proxiesNamespace
     * @param bool                             $requireGeneratedFiles
     */
    public function __construct(
        DocumentMetadataFactoryInterface $metadataFactory,
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
     *
     * @return ProxyGenerator
     */
    public function getGenerator(string $documentClass): ProxyGenerator
    {
        if (!array_key_exists($documentClass, $this->generatorsCache)) {
            $this->generatorsCache[$documentClass] = new ProxyGenerator(
                $this->metadataFactory->getDocumentMetadata($documentClass),
                $this
            );
        }

        return $this->generatorsCache[$documentClass];
    }

    public function getProxyClass(string $documentClass, bool $asRootDocument = true): string
    {
        $proxyClassNamePostfix = $asRootDocument ? 'RootProxy' : 'Proxy';
        $proxyClassName = ltrim($documentClass, '\\').$proxyClassNamePostfix;
        $proxyClassName = $this->proxiesNamespace.'\\'.$proxyClassName;

        if (array_key_exists($proxyClassName, $this->proxyClassNames)) {
            return $proxyClassName;
        }

        $proxyGenerator = $this->getGenerator($documentClass);
        $classGenerator = $proxyGenerator->generateClass($asRootDocument);
        $classGenerator->setName($proxyClassName);

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
