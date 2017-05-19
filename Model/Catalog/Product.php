<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Catalog;

class Product implements \JsonSerializable
{
    public $id;
    private $prices, $stock;

    public function __construct()
    {
        $this->prices = [];
        $this->stock = [];

    }

    public function addPrice(int $quantity, ProductPrice $price)
    {
        if (isset($this->prices[$quantity])) {
            throw new \Exception('Price for quantity \'' . $quantity . '\' already defined');
        }
        $this->prices[$quantity] = $price;
    }

    public function getPrice(int $quantity)
    {
        $quantity = intval($quantity);
        $selectedPrice = null;
        $prices = array_reverse($this->prices, true);
        /** @var  ProductPrice $price */
        foreach ($prices as $priceQuantity => $price) {
            if ($priceQuantity <= $quantity) {
                return $price;
            }
        }

        return $selectedPrice;
    }

    public function addStock(ProductStock $stock)
    {
        $code = $stock->getLocation()
                      ->getCode();
        if (isset($this->stock[$code])) {
            throw new \Exception('Stock for location \'' . $code . '\' already defined');
        }
        $this->stock[$code] = $stock;
    }

    public function getStock(string $code = ProductStockLocation::BASE_LOCATION): ProductStock
    {
        if (isset($this->stock[$code])) {
            return $this->stock[$code];
        }
        throw new \Exception('Stock for location \'' . $code . '\' not defined');
    }

    public function hasStock(string $code = ProductStockLocation::BASE_LOCATION): bool
    {
        return isset($this->stock[$code]);
    }

    public function getStockLocations(): array
    {
        return array_keys($this->stock);
    }

    public function getPriceTiers(): array
    {
        return array_keys($this->prices);
    }

    function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}