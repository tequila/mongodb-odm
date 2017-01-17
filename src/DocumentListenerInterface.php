<?php

namespace Tequila\MongoDB\ODM;

interface DocumentListenerInterface
{
    public function documentFetched(DocumentInterface $document);
}