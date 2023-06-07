<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\ResourceModel;

use Attlaz\Base\Helper\Data;
use Attlaz\Client;
use Attlaz\Model\FlowRunRequestResponse;
use Psr\Log\LoggerInterface;

class BaseResource
{

    protected LoggerInterface $logger;

    protected Data $dataHelper;

    /**
     * @param Data $dataHelper
     * @param LoggerInterface $logger
     */
    public function __construct(Data $dataHelper, LoggerInterface $logger)
    {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * Get Attlaz client
     *
     * @return Client|null
     */
    public function getClient(): Client|null
    {
        return $this->dataHelper->getClient();
    }

    /**
     * Execute task
     *
     * @param string $flowId
     * @param array $arguments
     * @return FlowRunRequestResponse
     * @throws \Exception
     */
    public function requestFlowRun(string $flowId, array $arguments = []): FlowRunRequestResponse
    {
        $client = $this->dataHelper->getClient();
        if ($client === null) {
            throw new \ErrorException('Unable to request flow run: Attlaz connection not configured');
        }
        $environmentIdentifier = $this->dataHelper->getProjectEnvironmentIdentifier();
        return $client->getFlowEndpoint()->requestRunFlow($flowId, $arguments, $environmentIdentifier);
    }
}
