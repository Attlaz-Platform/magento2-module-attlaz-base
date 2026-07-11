<?php

declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;
use Attlaz\Helper\LoadAllHelper;
use Attlaz\Model\CollectionResult;
use Attlaz\Model\CursorPagination;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Message\ManagerInterface;

class LogStream implements OptionSourceInterface
{

    /**
     * @param Data $dataHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        private readonly Data             $dataHelper,
        private readonly ManagerInterface $messageManager
    )
    {

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
                $client = $this->dataHelper->getClient();
                if ($client !== null) {
                    $logEndpoint = $client->getLogEndpoint();
                    $projectId = $this->dataHelper->getProjectIdentifier();
                    $logStreams = LoadAllHelper::loadAll(
                        fn(CursorPagination $pagination): CollectionResult => $logEndpoint->getLogStreams($projectId, $pagination),
                        fn($logStream): string => (string)$logStream->getId()
                    );

                    if (count($logStreams) !== 0) {
                        $result[] = [
                            'value' => '',
                            'label' => __('--Please Select--'),
                        ];
                    }
                    foreach ($logStreams as $logStream) {
                        $result[] = [
                            'value' => $logStream->getId(),
                            'label' => $logStream->getName() . ' [' . $logStream->getId() . ']',
                        ];
                    }
                }
            } catch (\Throwable $exception) {
                $this->messageManager->addErrorMessage('Unable to fetch log log streams: ' . $exception->getMessage());
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
        if (!$this->dataHelper->hasProjectIdentifier()) {
            return false;
        }
        return $this->dataHelper->getClient() !== null;
    }
}
