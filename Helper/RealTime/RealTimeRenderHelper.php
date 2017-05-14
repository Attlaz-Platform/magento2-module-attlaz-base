<?php
declare(strict_types=1);

namespace Attlaz\Base\Helper\RealTime;

use Attlaz\Base\Model\Resource\RealTimeInfoRequest;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Helper\AbstractHelper;

class RealTimeRenderHelper extends AbstractHelper
{

    public function renderRealTimeProductStockInfo(int $productId, AbstractBlock $block, array $data = [], string $originalHtml = ''): string
    {
        return $this->renderRealTimeProductInfo(RealTimeInfoRequest::TYPE_STOCK, $productId, $block, $data, $originalHtml);
    }

    public function renderRealTimeProductPriceInfo(int $productId, AbstractBlock $block, array $data = [], string $originalHtml = ''): string
    {
        return $this->renderRealTimeProductInfo(RealTimeInfoRequest::TYPE_PRICE, $productId, $block, $data, $originalHtml);
    }

    private function renderRealTimeProductInfo(string $type, int $productId, AbstractBlock $block, array $data = [], string $originalHtml = ''): string
    {

        $template = null;
        if ($block instanceof Template) {
            $template = $block->getTemplate();
        }
        $blockClass = get_class($block);

        $request = new RealTimeInfoRequest($productId, $blockClass, $template, $data, $type);

        $jsonRequest = json_encode($request);
        $request = base64_encode($jsonRequest);

//TODO: maybe store the request data in session, and load it by the key
        return '<div data-update-realtime="' . $request . '">' . $originalHtml . '</div>';
    }
}
