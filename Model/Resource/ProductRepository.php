<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Resource;

use Attlaz\Model\Catalog\Product;
use Attlaz\Model\Catalog\ProductCollection;
use Attlaz\Model\Catalog\ProductPrice;
use Attlaz\Model\Catalog\ProductStock;
use Attlaz\Model\Catalog\ProductStockLocation;

class ProductRepository
{

    public function fetchProduct(string $productId, string $customerId): Product
    {

        $products = $this->fetchProducts([$productId], $customerId);

        return $products->getById($productId);

    }

    public function fetchProducts(array $productIds, string $customerId): ProductCollection
    {
        $productIds = array_unique($productIds);

        //
        $totalProducts = new ProductCollection();

        try {

            $products = $this->getExternalData($productIds, $customerId);

            if ($products !== null) {

                /** @var Product $product */
                foreach ($products as $product) {

                    $totalProducts->addProduct($product);
                }
            }
        } catch (\Exception $ex) {


        }

        return $totalProducts;
    }

    private function getExternalData(array $productIds, string $customerId): ProductCollection
    {

        $result = new ProductCollection();

        foreach ($productIds as $productId) {
            $p = new Product();
            $p->id = $productId;

            //Price
            $price = new ProductPrice();
            $price->baseExcl = 15;
            $price->baseIncl = $price->baseExcl * 1.21;
            $price->endExcl = 10;
            $price->endIncl = $price->endExcl * 1.21;
            $p->addPrice(1, $price);

            //Stock
            $stock = new ProductStock(new ProductStockLocation(0, 'base'));
            $stock->setStock(10);
            $p->addStock($stock);

            $result->addProduct($p);
        }

        return $result;

    }

}
