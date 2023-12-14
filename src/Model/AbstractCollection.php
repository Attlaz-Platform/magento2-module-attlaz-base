<?php

declare(strict_types=1);

namespace Attlaz\Base\Model;

abstract class AbstractCollection implements \Iterator, \Countable
{
    /** @var array */
    protected array $collection;

    /**
     */
    public function __construct()
    {
        $this->collection = [];
    }

    /**
     * Get by id
     *
     * @param string $id
     * @return mixed|null
     */
    public function getById($id)
    {
        $id = $this->formatId($id);
        if (isset($this->collection[$id])) {
            return $this->collection[$id];
        }

        return null;
    }

    /**
     * Format id
     *
     * @param string $id
     * @return string
     */
    protected function formatId($id): string
    {
        return strtolower((string)$id);
    }

    /**
     * Get ids
     *
     * @return array
     */
    public function getIds(): array
    {
        return array_keys($this->collection);
    }

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return current($this->collection);
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next(): void
    {
        next($this->collection);
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key(): mixed
    {
        return key($this->collection);
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return !!current($this->collection);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->collection);
    }

    /**
     * Count elements of an object
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }
}
