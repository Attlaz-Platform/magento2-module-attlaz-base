<?php

namespace Attlaz\Base\Block\CatalogInventory\Stockqty;

use Attlaz\Base\Helper\CustomerHelper;
use Attlaz\Base\Helper\Data;
use Attlaz\Base\Helper\RealTime\RealTimeRenderHelper;
use Attlaz\Base\Helper\RealTimeInfo\RealTimeInfoHelper;
use Attlaz\Base\Helper\RealTimeInfo\StockHelper;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

class DefaultStockqty extends \Magento\CatalogInventory\Block\Stockqty\DefaultStockqty
{
    private $customerHelper;
    private $realtimeRenderHelper;
    private $realTimeInfoHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        StockStateInterface $stockState,
        StockRegistryInterface $stockRegistry,

        CustomerHelper $customerHelper,
        RealTimeInfoHelper $realTimeInfoHelper,
        RealTimeRenderHelper $realtimeRenderHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $stockState, $stockRegistry, $data);
        $this->_isScopePrivate = true;
        $this->customerHelper = $customerHelper;
        $this->realTimeInfoHelper = $realTimeInfoHelper;
        $this->realtimeRenderHelper = $realtimeRenderHelper;
    }

    public function isMsgVisible()
    {
        if ($this->getProduct()
                 ->getTypeId() === 'configurable') {
            //return false;
        }

        return true;
    }

    public function getProduct()
    {
        $product = $this->getData('product');
        if ($product === null) {
            $product = parent::getProduct();
        }

        return $product;
    }

    public function getTemplate()
    {
        $template = parent::getTemplate();
        $template = $this->forceTemplateFromCatalogInventoryModule($template);

        return $template;
    }

    private function forceTemplateFromCatalogInventoryModule($template): string
    {
        if ($template !== '') {
            if (strpos($template, '::') === false) {
                $template = 'Magento_CatalogInventory::' . $template;
            }
        }

        return $template;
    }

    public function getProductStockQty($product, string $location = 'base')
    {
        if ($product->hasData(StockHelper::FIELD_TEMP_STOCK_INFO)) {
            $stockInfo = $product->getData(StockHelper::FIELD_TEMP_STOCK_INFO);
            if (isset($stockInfo[$location])) {
                $stockLocationInfo = $stockInfo[$location];

                return $stockLocationInfo['qty'];
            }
        }

        return parent::getProductStockQty($product);
    }

    protected function _toHtml()
    {
        $html = '';
        if ($this->customerHelper->shouldDisplayStockInfo()) {
            if (!$this->realTimeInfoHelper->useRealTimeStock() || $this->isRealTimeRender()) {
                $html = parent::_toHtml();
            } else {
                if ($this->customerHelper->shouldDisplayStockBeforeRealTimeUpdate()) {
                    $html = parent::_toHtml();
                }

                $productId = intval($this->getProduct()
                                         ->getId());

                $html = $this->realtimeRenderHelper->renderRealTimeProductStockInfo($productId, $this, [], $html);
            }
        }

        return $html;
    }

    private function isRealTimeRender(): bool
    {
        return $this->hasData(Data::BLOCK_DATA_FLAG_CONTAINS_REAL_TIME_DATA);
    }

}
