<?php

namespace Tequila\MongoDB\ODM\Metadata\Factory;

use Tequila\MongoDB\ODM\Metadata\ClassMetadata;

interface MetadataFactoryInterface
{
    /**
     * @param string $documentClass
     *
     * @return ClassMetadata
     */
    public function getClassMetadata($documentClass): ClassMetadata;
}
