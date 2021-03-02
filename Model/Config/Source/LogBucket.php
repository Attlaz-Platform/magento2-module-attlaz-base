<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Model\Resource\BaseResource;
use Magento\Framework\Option\ArrayInterface;

class LogBucket implements ArrayInterface
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
//                $projectEnvironments = $this->dataHelper->getClient()
//                    ->getProjectEnvironments($this->dataHelper->getProjectIdentifier());
                $logBuckets = [];
                if (count($logBuckets) !== 0) {
                    $result[] = [
                        'value' => '',
                        'label' => __('--Please Select--'),
                    ];
                }
                foreach ($logBuckets as $logBucket) {
                    $result[] = [
                        'value' => $logBucket->id,
                        'label' => $logBucket->name . ' [' . $logBucket->id . ']',
                    ];
                }

            } catch (\Throwable $exception) {
                $this->messageManager->addErrorMessage('Unable to fetch log buckets: ' . $exception->getMessage());
            }

        }

        return $result;
    }

    private function canFetchData(): bool
    {
        return !\is_null($this->dataHelper->getClient()) && $this->dataHelper->hasProjectIdentifier();
    }
}
