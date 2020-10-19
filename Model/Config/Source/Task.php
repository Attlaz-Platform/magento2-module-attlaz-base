<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Model\Resource\BaseResource;
use Magento\Framework\Option\ArrayInterface;

class Task implements ArrayInterface
{
    private $dataHelper;
    private $baseResource;

    public function __construct(Data $dataHelper, BaseResource $baseResource)
    {
        $this->dataHelper = $dataHelper;
        $this->baseResource = $baseResource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
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
    }

    private function canFetchData(): bool
    {
        return !\is_null($this->dataHelper->getClient()) && $this->dataHelper->hasProjectIdentifier() && $this->dataHelper->hasProjectEnvironmentIdentifier();
    }
}
