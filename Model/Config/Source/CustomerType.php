<?php

namespace Attlaz\Base\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;

class CustomerType implements ArrayInterface
{
    const TYPE_ALL = 0;
    const TYPE_AUTHENTICATED = 1;
    const TYPE_LINKED = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {

        return [
            [
                'value' => self::TYPE_ALL,
                'label' => __('All'),
            ],
            [
                'value' => self::TYPE_AUTHENTICATED,
                'label' => __('Authenticated (with or without external id)'),
            ],
            [
                'value' => self::TYPE_LINKED,
                'label' => __('Authenticated and linked'),
            ],
        ];
    }
}
