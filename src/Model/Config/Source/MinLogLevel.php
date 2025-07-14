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
            ['value' => Level::Debug->name, 'label' => 'Debug'],
            ['value' => Level::Info->name, 'label' => 'Info'],
            ['value' => Level::Notice->name, 'label' => 'Notice'],
            ['value' => Level::Warning->name, 'label' => 'Warning'],
            ['value' => Level::Error->name, 'label' => 'Error'],
            ['value' => Level::Critical->name, 'label' => 'Critical'],
            ['value' => Level::Alert->name, 'label' => 'Alert'],
            ['value' => Level::Emergency->name, 'label' => 'Emergency'],
        ];
    }
}
