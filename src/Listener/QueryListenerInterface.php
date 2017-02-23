<?php

namespace Tequila\MongoDB\ODM\Listener;

use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\Server;

interface QueryListenerInterface
{
    public function beforeQuery(Server $server, $namespace, $filter, $options);

    public function afterQuery(Server $server, $namespace, $filter, $options, CursorInterface $cursor);
}