<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Config\Source;

use Attlaz\Base\Helper\Data;

class LogStream implements \Magento\Framework\Data\OptionSourceInterface
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
                $logStreams = $this->dataHelper->getClient()->getLogEndpoint()->getLogStreams($this->dataHelper->getProjectIdentifier());


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

            } catch (\Throwable $exception) {
                $this->messageManager->addErrorMessage('Unable to fetch log log streams: ' . $exception->getMessage());
            }

        }

        return $result;
    }

    private function canFetchData(): bool
    {
        return !\is_null($this->dataHelper->getClient()) && $this->dataHelper->hasProjectIdentifier();
    }
}
