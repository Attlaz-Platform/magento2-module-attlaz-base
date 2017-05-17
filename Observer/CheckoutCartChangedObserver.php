<?php
declare(strict_types=1);

namespace Attlaz\Base\Observer;

use Attlaz\Base\Helper\ProductHelper;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use \Magento\Catalog\Model\ProductFactory;
use  \Psr\Log\LoggerInterface;

class CheckoutCartChangedObserver implements ObserverInterface
{
    private $productHelper;
    private $logger;
    /** @var \Magento\Catalog\Model\Product */
    private $productFactory;

    public function __construct(ProductHelper $productHelper, LoggerInterface $logger, ProductFactory $productFactory)
    {
        $this->productHelper = $productHelper;
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

    private function setCustomPriceToItem($price, $item)
    {
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
    }

    private function updateSingleProductFromQuote(Observer $observer)
    {
        $product = $observer->getData('product');
        $this->productHelper->updateProductWithExternalData($product);

        $quoteItem = $observer->getData('quote_item');
        $price = $this->getUpdatedPriceForProduct($product, $quoteItem->getQty());
        $this->setCustomPriceToItem($price, $quoteItem);
    }

    private function updateAllProductsInCart(Observer $observer)
    {
        $cart = $observer->getData('cart');
        if ($cart) {
            $items = $cart->getItems();
            if ($items) {
                foreach ($items as $item) {
                    $product = $this->getLoadProduct($item->getProductId());
                    $this->productHelper->updateProductWithExternalData($product);

                    $price = $this->getUpdatedPriceForProduct($product, $item->getQty());
                    $this->setCustomPriceToItem($price, $item);
                }
            }
        }
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
