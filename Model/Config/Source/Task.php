<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Message\ManagerInterface;

class Task implements OptionSourceInterface
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
        try {
            //TODO: should we cache this?
            $result = [];
            $result[] = [
                'value' => '',
                'label' => __('--Please Select--'),
            ];
            if ($this->canFetchData()) {
                $tasks = $this->dataHelper->getClient()
                    ->getTasks($this->dataHelper->getProjectIdentifier());

                foreach ($tasks as $task) {
                    $label = $task->name . ' (' . $task->id . ')';
//                    if ($task->state !== 'active') {
//                    }
                    $result[] = [
                        'value' => $task->id,
                        'label' => $label,
                    ];
                }
            }
            return $result;
        } catch (\Throwable $ex) {
            $this->messageManager->addErrorMessage('Unable to fetch projects: ' . $ex->getMessage());
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
