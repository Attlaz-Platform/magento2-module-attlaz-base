<?php

declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Message\ManagerInterface;

class Project implements OptionSourceInterface
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
                $projects = $this->dataHelper->getClient()->getProjectEndpoint()->getProjects();

                if (count($projects) !== 0) {
                    $result[] = [
                        'value' => '',
                        'label' => __('--Please Select--'),
                    ];
                }

                foreach ($projects as $project) {
                    $result[] = [
                        'value' => $project->id,
                        'label' => $project->name . ' [' . $project->id . ']',
                    ];
                }
            } catch (\Throwable $ex) {
                $this->messageManager->addErrorMessage('Unable to fetch projects: ' . $ex->getMessage());
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
        return $this->dataHelper->getClient() !== null;
    }
}
