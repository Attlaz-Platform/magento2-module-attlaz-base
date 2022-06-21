<?php
declare(strict_types=1);

namespace Attlaz\Base\Model;

abstract class AbstractCollection implements \Iterator, \Countable
{

    protected array $collection;

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

    public function getIds(): array
    {
        return array_keys($this->collection);
    }

    public function current(): mixed
    {
        return current($this->collection);
    }

    public function next(): void
    {
        next($this->collection);
    }

    public function key(): mixed
    {
        return key($this->collection);
    }

    public function valid(): bool
    {
        return !!current($this->collection);
    }

    public function rewind(): void
    {
        reset($this->collection);
    }

    public function count(): int
    {
        return count($this->collection);
    }
}
