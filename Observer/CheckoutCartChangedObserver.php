<?php
declare(strict_types=1);

namespace Attlaz\Base\Observer;

use Attlaz\Base\Helper\RealTimeInfo\RealTimeInfoHelper;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CheckoutCartChangedObserver implements ObserverInterface
{
    private $realTimeInfoHelper;
    private $logger;
    /** @var \Magento\Catalog\Model\Product */
    private $productFactory;

    public function __construct(RealTimeInfoHelper $realTimeInfoHelper, LoggerInterface $logger, ProductFactory $productFactory)
    {
        $this->realTimeInfoHelper = $realTimeInfoHelper;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
    }

    private function getLoadProduct($id)
    {
        return $this->productFactory->create()
                                    ->load($id);
    }

    private function getUpdatedPriceForProduct($product, $qty)
    {
        return $product->getFinalPrice($qty);
    }

    private function setCustomPriceToItem($price, \Magento\Quote\Model\Quote\Item $item)
    {
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
    }

    private function updateSingleProductFromQuote(Observer $observer)
    {
        $product = $observer->getData('product');
        $this->realTimeInfoHelper->updateProductWithExternalData($product);

        $quoteItem = $observer->getData('quote_item');

        $price = $this->getUpdatedPriceForProduct($product, $quoteItem->getQty());
        $this->setCustomPriceToItem($price, $quoteItem);
    }

    private function updateAllProductsInCart(Observer $observer)
    {
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getData('cart');
        if ($cart) {
            $items = $cart->getItems();
            if ($items) {
                /** @var \Magento\Quote\Model\Quote\Item $item */
                foreach ($items as $item) {
                    try {
                        $productId = $this->getProductId($item);

                        $product = $this->getLoadProduct($productId);
                        $this->realTimeInfoHelper->updateProductWithExternalData($product);

                        $price = $this->getUpdatedPriceForProduct($product, $item->getQty());
                        $this->setCustomPriceToItem($price, $item);
                    } catch (\Throwable $ex) {
                        $this->logger->error($ex->getMessage());
                    }
                }
            }
        }
    }

    private function getProductId(\Magento\Quote\Model\Quote\Item $item)
    {
        if ($item->getProductType() === 'configurable') {
            $buyRequest = $item->getOptionByCode('info_buyRequest');

            try {
                // $data = unserialize(trim($buyRequest->getValue()));
                //Magento 2.2 => decode
                $data = \json_decode($buyRequest->getValue(), true);

                if (isset($data['selected_configurable_option'])) {
                    return $data['selected_configurable_option'];
                }
            } catch (\Throwable $ex) {
                $this->logger->error($ex->getMessage());
            }
        } elseif ($item->getProductType() === 'simple') {
            return $item->getProduct()
                        ->getId();
        }
        throw  new \Exception('Unable to determine item id');
    }

    public function execute(Observer $observer)
    {
        try {
            $eventName = $observer->getEvent()
                                  ->getName();
            switch ($eventName) {
                case 'checkout_cart_product_add_after':
                    $this->updateSingleProductFromQuote($observer);
                    break;
                case 'checkout_cart_update_items_after':
                    $this->updateAllProductsInCart($observer);
                    break;
            }
        } catch (\Exception $ex) {
            $this->logger->error($ex->getMessage());
        }
    }
}
