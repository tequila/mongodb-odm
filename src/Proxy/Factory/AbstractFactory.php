<?php

namespace Tequila\MongoDB\ODM\Proxy\Factory;

abstract class AbstractFactory implements ProxyFactoryInterface
{
    /**
     * @var string
     */
    protected $proxiesNamespace;

    /**
     * @var string
     */
    protected $proxiesDir;

    /**
     * @param string $proxiesDir
     * @param string $proxiesNamespace
     */
    public function __construct(string $proxiesDir, string $proxiesNamespace)
    {
        $this->proxiesDir = rtrim($proxiesDir, '/');
        $this->proxiesNamespace = trim($proxiesNamespace, '\\');

        spl_autoload_register(function (string $proxyClass) {
            if (substr($proxyClass, 0, strlen($this->proxiesNamespace)) !== $this->proxiesNamespace) {
                return;
            }

            require $this->getProxyFileName($proxyClass);
        });
    }

    protected function getProxyClassName(string $documentClass): string
    {
        return $this->proxiesNamespace.'\\'.$documentClass.'Proxy';
    }

    protected function getProxyFileName(string $proxyClass)
    {
        $parts = explode('\\', substr($proxyClass, strlen($this->proxiesNamespace) + 1));
        $fileName = array_pop($parts).'.php'; // delete class name - $parts must contain only namespace parts
        $relativePath = implode('/', $parts);
        $proxyDir = $this->proxiesDir.'/'.$relativePath;
        //is_dir($proxyDir) || mkdir($proxyDir, 0777, true);

        return $proxyDir.'/'.$fileName;
    }
}
