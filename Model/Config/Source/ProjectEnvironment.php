<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Model\Resource\BaseResource;
use Magento\Framework\Option\ArrayInterface;

class ProjectEnvironment implements ArrayInterface
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
            $projectEnvironments = $this->baseResource->getClient()
                                                      ->getProjectEnvironments($this->dataHelper->getProjectIdentifier());

            foreach ($projectEnvironments as $projectEnvironment) {
                $result[] = [
                    'value' => $projectEnvironment->id,
                    'label' => $projectEnvironment->name,
                ];
            }
        }

        return $result;
    }

    private function canFetchData(): bool
    {
        return $this->dataHelper->hasClientConfiguration() && $this->dataHelper->hasProjectIdentifier();
    }
}
