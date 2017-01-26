<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\ODM\DocumentInterface;

interface DocumentListenerInterface
{
    public function documentFetched(DocumentInterface $document);
}