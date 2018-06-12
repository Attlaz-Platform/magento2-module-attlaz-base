<?php

namespace Attlaz\Base\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Attlaz extends \Magento\Framework\View\Element\Template
{
    private $storeManager;

    public function __construct(Context $context, StoreManagerInterface $storeManager, array $data = [])
    {
        parent::__construct($context, $data);

        $this->storeManager = $storeManager;
    }

    public function getEndPointUrl()
    {
        return $this->getUrl('attlaz/realtime/productinfo');
    }

}
