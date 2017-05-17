<?php

namespace Attlaz\Base\Helper\RealTimeInfo;

use Attlaz\Base\Helper\CustomerHelper;
use Attlaz\Base\Helper\Data;
use Attlaz\Model\Catalog\Product as AttlazProduct;
use Attlaz\Model\Catalog\ProductCollection as AttlazProductCollection;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection as MagentoProductCollection;
use Magento\Framework\App\Helper\Context;
use Attlaz\Base\Model\Resource\ProductRepository;

class RealTimeInfoHelper extends Data
{

    /** @var CustomerHelper */
    private $customerHelper;
    private $productRepository;

    private $priceHelper;
    private $stockHelper;

    public function __construct(Context $context, CustomerHelper $customerHelper, ProductRepository $productRepository, PriceHelper $priceHelper, StockHelper $stockHelper)
    {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->productRepository = $productRepository;

        $this->priceHelper = $priceHelper;
        $this->stockHelper = $stockHelper;
    }

    public function updateProductWithExternalData(MagentoProduct $magentoProduct, $customerExternalId = null)
    {

        if ($this->shouldUpdateProductWithRealTimeInfo()) {

            if (!$customerExternalId && $this->customerHelper->hasCurrentCustomerExternalId()) {
                $customerExternalId = $this->customerHelper->getCurrentCustomerExternalId();
            }

            if ($customerExternalId) {

                try {

                    $productExternalId = $this->getExternalId($magentoProduct);
                    $attlazProductData = $this->productRepository->fetchProduct($productExternalId, $customerExternalId);

                    var_dump($attlazProductData);
                    die();
                    if ($attlazProductData) {
                        $this->appendExternalDataToProduct($magentoProduct, $attlazProductData);

                    } else {
                        echo 'No Attlaz data for externalId (product): ' . $productExternalId;
                    }
                } catch (\Throwable $ex) {
                    $this->_logger->error('Unable to update product with real time info: ' . $ex->getMessage());
                }
            }
        }
    }

    public function updateProductCollectionWithExternalData(MagentoProductCollection $magentoProducts)
    {


        if ($this->shouldUpdateProductWithRealTimeInfo() && $this->customerHelper->hasCurrentCustomerExternalId()) {
            try {
                $customerExternalId = $this->customerHelper->getCurrentCustomerExternalId();
                $externalProductIds = $this->getExternalIds($magentoProducts);

                $attlazProducts = $this->productRepository->fetchProducts($externalProductIds, $customerExternalId);

                $this->appendExternalDataToProductCollection($magentoProducts, $attlazProducts);
            } catch (\Throwable $ex) {
                $this->_logger->error('Unable to update product collection with real time info: ' . $ex->getMessage());
            }

        }

    }

    //<editor-fold desc="Append external data">

    private function appendExternalDataToProductCollection(MagentoProductCollection $magentoProducts, AttlazProductCollection $attlazProducts)
    {


        foreach ($magentoProducts as $magentoProduct) {
            $externalId = $this->getExternalId($magentoProduct);
            $attlazProduct = $attlazProducts->getById($externalId);

            if ($attlazProduct !== null) {

                $this->appendExternalDataToProduct($magentoProduct, $attlazProduct);

            } else {

                $this->_logger->warning('No Attlaz data for externalId: ' . $externalId . ' (available: ' . implode(', ', $attlazProducts->getIds()) . ')');
                //throw new \Exception('No Attlaz product in collection for product (Magento product id: ' . $magentoProduct->getId() . ' - Magento product external id: ' . $externalId . ')');
            }

        }
    }

    private function appendExternalDataToProduct(MagentoProduct $magentoProduct, AttlazProduct $attlazProduct)
    {
        $this->priceHelper->updateProductPriceWithAttlazData($magentoProduct, $attlazProduct);
        if ($this->useRealTimeStock()) {
            $this->stockHelper->updateProductStockWithAttlazData($magentoProduct, $attlazProduct);
        }

    }

    //</editor-fold>

    private function getExternalIds(MagentoProductCollection $magentoProducts): array
    {
        $externalProductIds = [];
        foreach ($magentoProducts as $product) {
            $externalProductId = $this->getExternalId($product);
            if ($externalProductId !== '' && !in_array($externalProductId, $externalProductIds)) {
                $externalProductIds[] = $externalProductId;
            }
        }

        return $externalProductIds;
    }

    public function useRealTimeStock(): bool
    {

        //TODO: debug
        return true;

        $value = intval($this->scopeConfig->getValue('attlaz/catalog/show_realtime_stock'));
        if ($value === 1) {
            return true;
        }

        return false;
    }

    public function useRealTimePrices(): bool
    {
        return true;
    }

    private function shouldUpdateProductWithRealTimeInfo(): bool
    {
        return $this->useRealTimeStock() || $this->useRealTimePrices();
    }

}
