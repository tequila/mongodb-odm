<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\ODM\DocumentsCollection;

interface CollectionListenerInterface
{
    public function collectionSelected(DocumentsCollection $collection);
}