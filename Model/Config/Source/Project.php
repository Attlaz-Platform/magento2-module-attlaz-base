<?php

namespace Attlaz\Base\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Project implements ArrayInterface
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
        if ($this->baseResource->hasClientConfiguration()) {
            $projects = $this->baseResource->getClient()
                                           ->getProjects();

            $result[] = [
                'value' => '',
                'label' => '',
            ];
            foreach ($projects as $project) {
                $result[] = [
                    'value' => $project->id,
                    'label' => $project->name,
                ];
            }
        }

        return $result;
    }
}
