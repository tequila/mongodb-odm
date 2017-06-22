<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use Tequila\MongoDB\ODM\Proxy\RootProxyInterface;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;

trait NestedProxyTrait
{
    use RealClassTrait;

    /**
     * @var RootProxyInterface
     */
    private $rootProxy;

    /**
     * @var string
     */
    private $pathInDocument;

    /**
     * @var array
     */
    private $_mongoDbData;

    /**
     * @return RootProxyInterface|UpdateBuilderInterface
     */
    public function getRootProxy(): RootProxyInterface
    {
        return $this->rootProxy;
    }

    /**
     * @param RootProxyInterface $rootProxy
     */
    public function setRootProxy(RootProxyInterface $rootProxy)
    {
        $this->rootProxy = $rootProxy;
    }

    /**
     * @param string $dbFieldName
     *
     * @return string
     */
    public function getPathInDocument(string $dbFieldName): string
    {
        return $this->pathInDocument.'.'.$dbFieldName;
    }

    /**
     * @param string $pathInDocument
     */
    public function setPathInDocument(string $pathInDocument)
    {
        $this->pathInDocument = $pathInDocument;
    }

    public function bsonUnserialize(array $data)
    {
        $this->_mongoDbData = $data;
    }
}
