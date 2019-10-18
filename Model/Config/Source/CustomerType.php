<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CustomerType implements ArrayInterface
{
    const TYPE_ALL = 0;
    const TYPE_NONE = 1;
    const TYPE_AUTHENTICATED = 2;
    const TYPE_LINKED = 3;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_ALL,
                'label' => __('All customers'),
            ],
            [
                'value' => self::TYPE_NONE,
                'label' => __('No customers'),
            ],
            [
                'value' => self::TYPE_AUTHENTICATED,
                'label' => __('Authenticated customers with or without external id'),
            ],
            [
                'value' => self::TYPE_LINKED,
                'label' => __('Authenticated customers with external id'),
            ],
        ];
    }
}
