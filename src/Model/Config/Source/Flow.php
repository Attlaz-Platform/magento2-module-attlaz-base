<?php

declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Message\ManagerInterface;

class Flow implements OptionSourceInterface
{


    /**
     * @param Data $dataHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly Data             $dataHelper,
        private readonly ManagerInterface $messageManager
    )
    {
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        try {
            //TODO: should we cache this?
            $result = [];
            $result[] = [
                'value' => '',
                'label' => __('--Please Select--'),
            ];
            if ($this->canFetchData()) {
                $flows = $this->dataHelper->getClient()->getFlowEndpoint()->getFlows($this->dataHelper->getProjectIdentifier());

                foreach ($flows as $flow) {
                    $label = $flow->name . ' (' . $flow->id . ')';
                    //                    if ($task->state !== 'active') {
                    //                    }
                    $result[] = [
                        'value' => $flow->id,
                        'label' => $label,
                    ];
                }
            }
            return $result;
        } catch (\Throwable $ex) {
            $this->messageManager->addErrorMessage('Unable to fetch flows: ' . $ex->getMessage());
        }
        return [];
    }

    /**
     * Determine if we can fetch data
     *
     * @return bool
     */
    private function canFetchData(): bool
    {
        if (!$this->dataHelper->hasProjectIdentifier()) {
            return false;
        }
        if (!$this->dataHelper->hasProjectEnvironmentIdentifier()) {
            return false;
        }
        return $this->dataHelper->getClient() !== null;
    }
}
