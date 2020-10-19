<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Model\Resource\BaseResource;
use Magento\Framework\Option\ArrayInterface;

class Project implements ArrayInterface
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
        //TODO: should we cache this?
        $result = [];

        if ($this->canFetchData()) {

            try {
                $projects = $this->dataHelper->getClient()
                    ->getProjects();

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
                $this->messageManager->addErrorMessage('Unable to fetch projects: ' . $exception->getMessage());
            }

        }

        return $result;
    }

    private function canFetchData(): bool
    {
        return !\is_null($this->dataHelper->getClient());
    }
}
