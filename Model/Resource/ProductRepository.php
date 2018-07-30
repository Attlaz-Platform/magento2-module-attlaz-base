<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Resource;

use Attlaz\Base\Model\Catalog\Product;
use Attlaz\Base\Model\Catalog\ProductCollection;
use Attlaz\Base\Model\Catalog\ProductPrice;
use Attlaz\Base\Model\Catalog\ProductStock;
use Attlaz\Base\Model\Catalog\ProductStockLocation;

class ProductRepository extends BaseResource
{

    public function fetchProduct(string $productId, string $customerId): Product
    {
        $products = $this->fetchProducts([$productId], $customerId);

        $product = $products->getById($productId);
        if (\is_null($product)) {
            throw new \Exception('No product data for product "' . $productId . '"');
        }

        return $product;
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
            $this->logger->error($ex->getMessage(), ['ex' => $ex]);
        }

        return $totalProducts;
    }

    private function getExternalData(array $productIds, string $customerId): ProductCollection
    {
        $result = new ProductCollection();

        $productInfo = $this->getProductInfo($productIds, $customerId);

        foreach ($productInfo as $productData) {
            if (!isset($productData['id'])) {
                $this->logger->warning('Inalid response', $productData);
            } else {
                $productId = $productData['id'];
                $productInfoPrices = $productData['prices'];
                $productInfoStock = $productData['stock'];

                $p = new Product();
                $p->id = $productId;

                foreach ($productInfoPrices as $qty => $productInfoPrice) {
                    $price = new ProductPrice();

                    $price->endExcl = $this->parsePrice((string)$productInfoPrice['finalExcl']);
                    $price->endIncl = $this->parsePrice((string)$productInfoPrice['finalIncl']);
                    $price->baseExcl = $this->parsePrice((string)$productInfoPrice['baseExcl']);
                    $price->baseIncl = $this->parsePrice((string)$productInfoPrice['baseIncl']);

                    $p->addPrice($qty, $price);
                }

                foreach ($productInfoStock as $locationCode => $stockLocation) {
                    $stock = new ProductStock(new ProductStockLocation(0, $locationCode));
                    $stock->setStock($stockLocation['stock']);
                    $stock->setData($stockLocation['data']);
                    $p->addStock($stock);
                }

                $result->addProduct($p);
            }
        }

        return $result;
    }

    private function getProductInfo(array $productCodes, string $customerId): array
    {
        try {
            $result = $this->executeTaskByCommand('getRealTimeProductInfo', [

                "externalProductIds" => $productCodes,
                "externalCustomerId" => $customerId,

            ]);

            return $result->result;
        } catch (\Exception $ex) {
            $this->logger->warning($ex->getMessage());

            return [];
        }
    }

    private function parsePrice(string $input): float
    {
        $output = str_replace(",", ".", $input);
        $output = floatval($output);

        return $output;
    }

}
