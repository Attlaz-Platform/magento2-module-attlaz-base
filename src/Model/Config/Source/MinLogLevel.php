<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

class MinLogLevel implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        $logLevels = Logger::getLevels();
        foreach ($logLevels as $level => $value) {
            $result[] = [
                'value' => $value,
                'label' => $level,
            ];
        }
        
        return $result;
    }
}
