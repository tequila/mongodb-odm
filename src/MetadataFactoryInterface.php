<?php

namespace Tequila\MongoDB\ODM;

interface MetadataFactoryInterface
{
    /**
     * @param string $documentClass
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($documentClass): ClassMetadata;
}
