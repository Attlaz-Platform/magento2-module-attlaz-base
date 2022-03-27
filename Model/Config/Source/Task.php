<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Model\Resource\BaseResource;

class Task implements \Magento\Framework\Data\OptionSourceInterface
{
    private $dataHelper;
    private $baseResource;
    private $messageManager;

    public function __construct(Data $dataHelper, BaseResource $baseResource, \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->dataHelper = $dataHelper;
        $this->baseResource = $baseResource;
        $this->messageManager = $messageManager;
    }

    /**
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
                    if ($task->state !== 'active') {
                    }
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

    private function canFetchData(): bool
    {
        return !\is_null($this->dataHelper->getClient()) && $this->dataHelper->hasProjectIdentifier() && $this->dataHelper->hasProjectEnvironmentIdentifier();
    }
}
