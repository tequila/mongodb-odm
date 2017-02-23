<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\BulkWrite;
use Tequila\MongoDB\CursorInterface;
use Tequila\MongoDB\ODM\Listener\QueryListenerInterface;
use Tequila\MongoDB\OperationListenerInterface;
use Tequila\MongoDB\Server;

class DelegatingOperationListener implements OperationListenerInterface
{
    /**
     * @var QueryListenerInterface[]
     */
    private $queryListeners = [];

    public function addQueryListener(QueryListenerInterface $queryListener)
    {
        $this->queryListeners[] = $queryListener;
    }

    public function beforeBulkWrite(Server $server, $namespace, BulkWrite $bulkWrite)
    {
    }

    public function afterBulkWrite(Server $server, $namespace, BulkWrite $bulkWrite)
    {
    }

    public function beforeCommand(Server $server, $databaseName, $commandOptions)
    {
    }

    public function afterCommand(Server $server, $databaseName, $commandOptions, CursorInterface $cursor)
    {
    }

    public function beforeQuery(Server $server, $namespace, $filter, $options)
    {
        foreach ($this->queryListeners as $listener) {
            $listener->beforeQuery($server, $namespace, $filter, $options);
        }
    }

    public function afterQuery(Server $server, $namespace, $filter, $options, CursorInterface $cursor)
    {
        foreach ($this->queryListeners as $listener) {
            $listener->afterQuery($server, $namespace, $filter, $options, $cursor);
        }
    }
}