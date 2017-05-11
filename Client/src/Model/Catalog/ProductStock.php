<?php
declare(strict_types=1);

namespace Attlaz\Model\Catalog;

class ProductStock
{

    private $location, $stock = 0, $data = [];

    public function __construct(ProductStockLocation $location)
    {
        $this->setLocation($location);
    }

    public function getLocation(): ProductStockLocation
    {
        return $this->location;
    }

    public function setLocation(ProductStockLocation $location)
    {
        $this->location = $location;
    }

    public function setStock(int $stock)
    {
        $this->stock = $stock;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
