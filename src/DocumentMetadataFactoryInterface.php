<?php

namespace Tequila\MongoDB\ODM;

interface DocumentMetadataFactoryInterface
{
    /**
     * @param string $documentClass
     *
     * @return DocumentMetadata
     */
    public function getDocumentMetadata($documentClass);
}
