<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerType implements OptionSourceInterface
{
    public const TYPE_ALL = 0;
    public const TYPE_NONE = 1;
    public const TYPE_AUTHENTICATED = 2;
    public const TYPE_LINKED = 3;

    /**
     * Return array of options as value-label pairs
     *
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
