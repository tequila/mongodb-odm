<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use Tequila\MongoDB\ODM\Proxy\RootProxyInterface;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;

trait ProxyTrait
{
    use RealClassTrait;

    /**
     * @var RootProxyInterface
     */
    private $rootDocument;

    /**
     * @var string
     */
    private $pathInDocument;

    /**
     * @return RootProxyInterface|UpdateBuilderInterface
     */
    public function getRootDocument(): RootProxyInterface
    {
        return $this->rootDocument;
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
     * @param array $data
     */
    public function extractPathInDocument(array $data)
    {
        $this->pathInDocument = $data['_pathInDocument'];
    }
}
