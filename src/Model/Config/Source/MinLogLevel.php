<?php

declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Level;

class MinLogLevel implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => Level::Debug->value . '', 'label' => 'Debug'],
            ['value' => Level::Info->value . '', 'label' => 'Info'],
            ['value' => Level::Notice->value . '', 'label' => 'Notice'],
            ['value' => Level::Warning->value . '', 'label' => 'Warning'],
            ['value' => Level::Error->value . '', 'label' => 'Error'],
            ['value' => Level::Critical->value . '', 'label' => 'Critical'],
            ['value' => Level::Alert->value . '', 'label' => 'Alert'],
            ['value' => Level::Emergency->value . '', 'label' => 'Emergency'],
        ];
    }
}
