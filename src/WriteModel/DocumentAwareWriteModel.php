<?php

namespace Tequila\MongoDB\ODM\WriteModel;

use Tequila\MongoDB\ODM\DocumentInterface;
use Tequila\MongoDB\WriteModelInterface;

abstract class DocumentAwareWriteModel implements WriteModelInterface
{
    /**
     * @var DocumentInterface
     */
    private $document;

    /**
     * @param DocumentInterface $document
     */
    public function __construct(DocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }
}