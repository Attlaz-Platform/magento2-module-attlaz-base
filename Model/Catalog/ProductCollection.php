<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Catalog;

class ProductCollection implements \Iterator
{

    private $products;

    public function __construct()
    {
        $this->products = [];
    }

    public function addProduct(Product $product)
    {
        $id = $this->formatId($product->id);
        $this->products[$id] = $product;
    }

    private function formatId($id): string
    {
        return strtolower((string)$id);
    }

    /**
     * @param $id
     * @return Product
     */
    public function getById($id)
    {
        $id = $this->formatId($id);
        if (isset($this->products[$id])) {
            return $this->products[$id];
        }

        return null;
    }

    public function getIds()
    {
        return array_keys($this->products);
    }

    public function current(): Product
    {
        return current($this->products);

    }

    public function next()
    {
        return next($this->products);
    }

    public function key()
    {
        return key($this->products);
    }

    public function valid()
    {
        return !!current($this->products);
    }

    public function rewind()
    {
        reset($this->products);
    }
}
