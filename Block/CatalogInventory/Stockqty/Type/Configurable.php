<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Attlaz\Base\Block\CatalogInventory\Stockqty\Type;

use Attlaz\Base\Block\CatalogInventory\Stockqty\Composite;
use Magento\Catalog\Model\Product;

/**
 * Product stock qty block for configurable product type
 *
 * @api
 * @since 100.0.2
 */
class Configurable extends Composite
{
    /**
     * Retrieve child products
     *
     * @return Product[]
     */
    protected function _getChildProducts()
    {
        return $this->getProduct()
                    ->getTypeInstance()
                    ->getUsedProducts($this->getProduct());
    }

}
