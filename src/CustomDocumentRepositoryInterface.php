<?php

namespace Tequila\MongoDB\ODM;

interface CustomDocumentRepositoryInterface
{
    /**
     * @return string
     */
    public static function getCollectionName();
}