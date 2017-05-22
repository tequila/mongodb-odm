<?php

namespace Tequila\MongoDB\ODM\Proxy\Traits;

use Tequila\MongoDB\ODM\Proxy\RootDocumentInterface;
use Tequila\MongoDB\ODM\Proxy\UpdateBuilderInterface;

trait ProxyTrait
{
    use RealClassTrait;

    /**
     * @var RootDocumentInterface
     */
    private $rootDocument;

    /**
     * @var string
     */
    private $pathInDocument;

    /**
     * @return RootDocumentInterface|UpdateBuilderInterface
     */
    public function getRootDocument(): RootDocumentInterface
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
