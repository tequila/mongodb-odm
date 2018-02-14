<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

use Tequila\MongoDB\ODM\Code\FileGenerator;
use Tequila\MongoDB\ODM\Metadata\Factory\MetadataFactoryInterface;
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
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @param string                   $proxiesDir
     * @param string                   $proxiesNamespace
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct($proxiesDir, $proxiesNamespace, MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
        parent::__construct($proxiesDir, $proxiesNamespace);
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyClass(string $documentClass): string
    {
        return $this->generateProxyClass($documentClass);
    }

    /**
     * @param string $documentClass
     *
     * @return string
     */
    public function generateProxyClass(string $documentClass): string
    {
        if (array_key_exists($documentClass, $this->proxyClassNames)) {
            return $this->proxyClassNames[$documentClass];
        }

        $proxyClassName = $this->getProxyClassName($documentClass);
        $proxyGenerator = $this->getGenerator($documentClass);
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
     * @param string $documentClass
     *
     * @return AbstractGenerator
     */
    private function getGenerator(string $documentClass): AbstractGenerator
    {
        if (!array_key_exists($documentClass, $this->generatorsCache)) {
            $metadata = $this->metadataFactory->getClassMetadata($documentClass);
            $this->generatorsCache[$documentClass] = $metadata->isNested()
                ? new NestedProxyGenerator($metadata, $this, $this->proxiesNamespace)
                : new RootProxyGenerator($metadata, $this, $this->proxiesNamespace);
        }

        return $this->generatorsCache[$documentClass];
    }
}
