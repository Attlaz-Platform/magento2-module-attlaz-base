<?php

declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Message\ManagerInterface;

class ProjectEnvironment implements OptionSourceInterface
{
    /** @var Data */
    private Data $dataHelper;
    /** @var ManagerInterface */
    private ManagerInterface $messageManager;

    /**
     * @param Data $dataHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(Data $dataHelper, ManagerInterface $messageManager)
    {
        $this->dataHelper = $dataHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray()
    {
        //TODO: should we cache this?
        $result = [];

        if ($this->canFetchData()) {

            try {
                $projectEnvironments = $this->dataHelper->getClient()->getProjectEnvironmentEndpoint()->getProjectEnvironments($this->dataHelper->getProjectIdentifier());
                if (count($projectEnvironments) !== 0) {
                    $result[] = [
                        'value' => '',
                        'label' => __('--Please Select--'),
                    ];
                }
                foreach ($projectEnvironments as $projectEnvironment) {
                    $result[] = [
                        'value' => $projectEnvironment->id,
                        'label' => $projectEnvironment->name . ' [' . $projectEnvironment->id . ']',
                    ];
                }

            } catch (\Throwable $exception) {
                $msg = 'Unable to fetch project environments: ' . $exception->getMessage();
                $this->messageManager->addErrorMessage($msg);
            }

        }

        return $result;
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
        return $this->dataHelper->getClient() !== null;
    }
}
