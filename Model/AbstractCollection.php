<?php
declare(strict_types=1);

namespace Attlaz\Base\Model;

abstract class AbstractCollection implements \Iterator, \Countable
{

    protected $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    public function getById($id)
    {
        $id = $this->formatId($id);
        if (isset($this->collection[$id])) {
            return $this->collection[$id];
        }

        return null;
    }

    protected function formatId($id): string
    {
        return strtolower((string)$id);
    }

    public function getIds()
    {
        return array_keys($this->collection);
    }

    public function current()
    {
        return current($this->collection);
    }

    public function next()
    {
        return next($this->collection);
    }

    public function key()
    {
        return key($this->collection);
    }

    public function valid()
    {
        return !!current($this->collection);
    }

    public function rewind()
    {
        reset($this->collection);
    }

    public function count()
    {
        return count($this->collection);
    }
}
