<?php

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ProjectEnvironment implements ArrayInterface
{
    private $baseResource;

    public function __construct(\Attlaz\Base\Model\Resource\BaseResource $baseResource)
    {
        $this->baseResource = $baseResource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        //TODO: should we cache this?
        $result = [];
        if ($this->baseResource->hasClientConfiguration() && !empty($this->baseResource->getProjectIdentifier())) {
            $projectEnvironments = $this->baseResource->getClient()
                                                      ->getProjectEnvironments($this->baseResource->getProjectIdentifier());

            $result[] = [
                'value' => '',
                'label' => '',
            ];

            foreach ($projectEnvironments as $projectEnvironment) {
                $result[] = [
                    'value' => $projectEnvironment->id,
                    'label' => $projectEnvironment->name,
                ];
            }
        }

        return $result;
    }
}
