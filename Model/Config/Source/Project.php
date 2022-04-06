<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;

class Project implements \Magento\Framework\Data\OptionSourceInterface
{
    private Data $dataHelper;
    private \Magento\Framework\Message\ManagerInterface $messageManager;

    public function __construct(Data $dataHelper, \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->dataHelper = $dataHelper;
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
                $this->messageManager->addErrorMessage('Unable to fetch projects: ' . $ex->getMessage());
            }

        }

        return $result;
    }

    private function canFetchData(): bool
    {
        return !\is_null($this->dataHelper->getClient());
    }
}
