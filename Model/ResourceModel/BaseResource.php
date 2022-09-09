<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\ResourceModel;

use Attlaz\Base\Helper\Data;
use Attlaz\Client;
use Attlaz\Model\TaskExecutionResult;
use Psr\Log\LoggerInterface;

class BaseResource
{
    /** @var LoggerInterface */
    protected LoggerInterface $logger;
    /** @var Data */
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
    public function getClient(): ?Client
    {
        return $this->dataHelper->getClient();
    }

    /**
     * Execute task
     *
     * @param string $taskIdentifier
     * @param array $arguments
     * @return TaskExecutionResult
     * @throws \Exception
     */
    public function executeTask(string $taskIdentifier, array $arguments = []): TaskExecutionResult
    {
        $client = $this->dataHelper->getClient();
        if ($client === null) {
            throw new \ErrorException('Unable to execute task: Attlaz connection not configured');
        }
        $environmentIdentifier = $this->dataHelper->getProjectEnvironmentIdentifier();
        return $client->requestTaskExecution($taskIdentifier, $arguments, $environmentIdentifier);
    }
}
