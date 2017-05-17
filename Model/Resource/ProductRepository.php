<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Resource;

use Attlaz\Model\Catalog\Product;
use Attlaz\Model\Catalog\ProductCollection;
use Attlaz\Model\Catalog\ProductPrice;
use Attlaz\Model\Catalog\ProductStock;
use Attlaz\Model\Catalog\ProductStockLocation;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;

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

        $productInfo = $this->getProductInfo($productIds, $customerId);

        // var_dump($prices);
        foreach ($productIds as $productId) {


            if (isset($productInfo[$productId])) {

                $productData = $productInfo[$productId];
                $p = new Product();
                $p->id = $productId;

                $productInfoPrices = $productData['price'];
                $productInfoStock = $productData['stock'];

                $price = new ProductPrice();

                $price->endExcl = $this->parsePrice((string)$productInfoPrices['endExcl']);
                $price->endIncl = $this->parsePrice((string)$productInfoPrices['endIncl']);
                $price->baseExcl = $this->parsePrice((string)$productInfoPrices['endExcl']);
                $price->baseIncl = $this->parsePrice((string)$productInfoPrices['endIncl']);

//                var_dump($price->endExcl);
//                var_dump($price->endIncl);
//
//                die();

                $p->addPrice(1, $price);

                //Stock
//                $stock = new ProductStock(new ProductStockLocation(0, 'base'));
//                $stock->setStock($productInfoStock['qty']);
//                $p->addStock($stock);

                $result->addProduct($p);
            }

        }

        return $result;

    }

    private function getProductInfo(array $productCodes, string $customerId): array
    {
        //TODO: remove all of this!
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://api.attlaz.com/',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $headers = [
            'Auth'   => 'ar(534_|#[',
            'Branch' => $scopeConfig->getValue('general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
        ];

        $data = [
            'action'     => 'catalog/product/realtimeinfo',
            'parameters' => [
                'external_ids' => $productCodes,
                'customer_id'  => $customerId,
            ],
        ];

        $body = \json_encode($data);
        $request = new Request('POST', 'https://api.attlaz.com/', $headers, $body);

        $response = $client->send($request, ['timeout' => 10]);

        $response = $response->getBody()
                             ->getContents();

        $data = \json_decode($response, true);

        return $data['response'];

    }

    private function parsePrice(string $input): float
    {
        $output = str_replace(",", ".", $input);
        $output = floatval($output);

        return $output;
    }

}
