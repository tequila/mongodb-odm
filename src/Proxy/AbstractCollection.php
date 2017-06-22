<?php

namespace Tequila\MongoDB\ODM\Proxy;

use ArrayAccess;
use Iterator;

abstract class AbstractCollection implements Iterator, ArrayAccess
{
    /**
     * @var array
     */
    protected $array;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var RootProxyInterface
     */
    protected $root;

    /**
     * @var int
     */
    protected $position;

    public function __construct(array $array, RootProxyInterface $root, string $path)
    {
        $this->array = array_values($array);
        $this->path = $path;
        $this->root = $root;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function key()
    {
        return $this->position;
    }

    public function current()
    {
        return $this[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return array_key_exists($this->position, $this->array);
    }

    public function offsetSet($index, $value)
    {
        $this->array[$index] = $value;
    }

    public function offsetUnset($index)
    {
        $this->array[$index] = null;
    }

    public function offsetExists($index)
    {
        return array_key_exists($index, $this->array);
    }
}
