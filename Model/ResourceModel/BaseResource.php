<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\ResourceModel;

use Attlaz\Base\Helper\Data;
use Attlaz\Client;
use Attlaz\Model\TaskExecutionResult;
use Psr\Log\LoggerInterface;

class BaseResource
{
    protected LoggerInterface $logger;
    protected Data $dataHelper;

    public function __construct(Data $dataHelper, LoggerInterface $logger)
    {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    public function getClient(): ?Client
    {
        return $this->dataHelper->getClient();
    }

    public function executeTask(
        string $taskIdentifier,
        array  $arguments = []
    ): TaskExecutionResult
    {
        $client = $this->dataHelper->getClient();
        if (\is_null($client)) {
            throw new \Exception('Unable to execute task: Attlaz connection not configured');

        }
        return $client->requestTaskExecution($taskIdentifier, $arguments, $this->dataHelper->getProjectEnvironmentIdentifier());

    }

}

