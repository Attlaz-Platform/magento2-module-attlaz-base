<?php

namespace Attlaz\Base\Helper;

use Attlaz\Model\Catalog\Product as AttlazProduct;
use Attlaz\Model\Catalog\ProductCollection as AttlazProductCollection;
use Attlaz\Model\Catalog\ProductPrice;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection as MagentoProductCollection;
use Magento\Customer\Model\Group;
use Magento\Framework\App\Helper\Context;
use Attlaz\Base\Model\Resource\ProductRepository;

class ProductHelper extends Data
{

    const FIELD_TEMP_STOCK_INFO = "realtime_stock_info";

    private $productHelper;
    /** @var CustomerHelper */
    private $customerHelper;
    private $productRepository;

    public function __construct(Context $context, CustomerHelper $customerHelper, ProductRepository $productRepository)
    {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->productRepository = $productRepository;
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
        $this->updateProductPriceWithExternalData($magentoProduct, $attlazProduct);
        if ($this->useRealTimeStock()) {
            $this->updateProductStockWithExternalData($magentoProduct, $attlazProduct);
        }

    }

    //</editor-fold>

    private function updateProductPriceWithExternalData(MagentoProduct $magentoProduct, AttlazProduct $attlazProduct)
    {

        /** @var ProductPrice $basePrice */
        $basePrice = $attlazProduct->getPrice(1);
        if ($basePrice === null) {
            $this->_logger->warning('Unable to update price with Attlaz data: base price not available (Magento product id: ' . $magentoProduct->getId() . ')');

            return false;
        }

        //TODO: make this configuratable
        $forceNettoPriceWhenHigherThanNormal = true;

        if ($this->pricesIncludesTax()) {
            //If the netto price is higher than the normal price, force the netto price
            if ($basePrice->endIncl > $basePrice->baseIncl && $forceNettoPriceWhenHigherThanNormal) {
                $magentoProduct->setPrice($basePrice->endIncl);
                //$magentoProduct->setOriginalCustomPrice($basePrice->nettoIncl);

                $magentoProduct->setSpecialPrice($basePrice->endIncl);
                // $magentoProduct->setCustomPrice($basePrice->nettoIncl);

            } else {
                $magentoProduct->setPrice($basePrice->endIncl);
                //$magentoProduct->setOriginalCustomPrice($basePrice->normalIncl);

                $magentoProduct->setSpecialPrice($basePrice->endIncl);
                //  $magentoProduct->setCustomPrice($basePrice->nettoIncl);
            }

        } else {
            //If the netto price is higher than the normal price, force the netto price
            if ($basePrice->endExcl > $basePrice->baseExcl && $forceNettoPriceWhenHigherThanNormal) {
                $magentoProduct->setPrice($basePrice->endExcl);
                //  $magentoProduct->setOriginalCustomPrice($basePrice->nettoExcl);

                $magentoProduct->setSpecialPrice($basePrice->endExcl);
                //  $magentoProduct->setCustomPrice($basePrice->nettoExcl);
            } else {
                $magentoProduct->setPrice($basePrice->endExcl);
                // $magentoProduct->setOriginalCustomPrice($basePrice->normalExcl);

                $magentoProduct->setSpecialPrice($basePrice->endExcl);
                // $magentoProduct->setCustomPrice($basePrice->nettoExcl);
            }

        }

        $today = date("Y-m-d");

//        $startDate = '2015-07-06';
//        $endDate = '2015-07-06';

        $magentoProduct->setSpecialFromDate($today);
        $magentoProduct->setSpecialFromDateIsFormated(true);

// Sets the End Date
        $magentoProduct->setSpecialToDate($today);
        $magentoProduct->setSpecialToDateIsFormated(true);

        // $magentoProduct->setIsSuperMode(true);

        //Temporary fast solution
        $personalTierPrices = [];

        $tierPrices = $attlazProduct->getPriceTiers();

        if (count($tierPrices) > 1) {

            $i = 0;
            foreach ($tierPrices as $quantity) {
                $linkProductTierPrice = $attlazProduct->getPrice($quantity);
                $i++;

                $price = $linkProductTierPrice->endExcl;
                if ($this->pricesIncludesTax()) {
                    $price = $linkProductTierPrice->endIncl;
                } else {

                }

                //TODO: test if everyting is needed
                $personalTierPrices[] = [
                    "price_id"   => $i,
                    // "website_id"    => "all",
                    // "all_groups"    => "1",
                    "cust_group" => Group::CUST_GROUP_ALL,

                    "price"         => $price,
                    "price_qty"     => $quantity,
                    "website_price" => $price,
                    //                    "formated_price" => "TODO?",
                ];
            }

        }

        if (count($personalTierPrices) > 0) {
            $magentoProduct->setData('tier_price', $personalTierPrices);
        }
    }

    private function pricesIncludesTax(): bool
    {
        //TODO: load value from configuration
//        $priceIncludesTax = Mage::getStoreConfig('tax/calculation/price_includes_tax', Mage::app()
//                                                                                           ->getStore());
//        if ((int)$priceIncludesTax === 1) {
//            return true;
//        }

        return false;
    }

    private function updateProductStockWithExternalData(MagentoProduct $magentoProduct, AttlazProduct $linkProduct)
    {
//TODO: test implementation!
        $lok = $linkProduct->getStockLocations();

        // $current = $magentoProduct->getStockData();
        $stockInfo = [];
        foreach ($lok as $location) {
            $stock = $linkProduct->getStock($location);

            $qtyAvailable = $stock->getStock();
            $inStock = false;
            if ($qtyAvailable > 0) {
                $inStock = true;
            }
            $stockInfo[$location] = [
                'is_in_stock'                 => $inStock,
                'qty'                         => $qtyAvailable,
                'manage_stock'                => 1,
                'use_config_notify_stock_qty' => 0,
            ];
        }

        $magentoProduct->setData(self::FIELD_TEMP_STOCK_INFO, $stockInfo);

    }

    public function getExternalIds(MagentoProductCollection $magentoProducts): array
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
