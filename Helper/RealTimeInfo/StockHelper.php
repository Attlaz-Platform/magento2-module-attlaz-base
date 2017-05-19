<?php
declare(strict_types=1);

namespace Attlaz\Base\Helper\RealTimeInfo;

use Attlaz\Base\Model\Catalog\Product as AttlazProduct;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\App\Helper\AbstractHelper;

class StockHelper extends AbstractHelper
{
    const FIELD_TEMP_STOCK_INFO = "realtime_stock_info";

    public function updateProductStockWithAttlazData(MagentoProduct $magentoProduct, AttlazProduct $linkProduct)
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
                'data'                        => $stock->getData(),
            ];
        }

        $magentoProduct->setData(self::FIELD_TEMP_STOCK_INFO, $stockInfo);
    }
}
