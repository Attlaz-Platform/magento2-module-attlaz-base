<?php
declare(strict_types=1);

namespace Attlaz\Base\Pricing\Render;

use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\Element\Template;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    public function __construct(Template\Context $context, SaleableInterface $saleableItem, PriceInterface $price, RendererPool $rendererPool, array $data = [])
    {

        parent::__construct($context, $saleableItem, $price, $rendererPool, $data);
        $this->_isScopePrivate = true;
    }

    protected function getCacheLifetime()
    {
        //Make sure this is never cached (TODO: make this depends on settings, maybe its possible to cache it when no custom price should be loaded)
        return null;
    }
}
