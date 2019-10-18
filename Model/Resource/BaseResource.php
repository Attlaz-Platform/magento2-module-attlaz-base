<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Resource;

use Attlaz\Base\Helper\Data;
use Attlaz\Client;
use Attlaz\Model\TaskExecutionResult;
use Psr\Log\LoggerInterface;

class BaseResource
{
    protected $logger;
    protected $dataHelper;
    private $client;

    private $projectKey;
    private $environmentKey;

    public function __construct(Data $dataHelper, LoggerInterface $logger)
    {
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    public function getClient(): Client
    {
        if (is_null($this->client)) {
            if (!$this->dataHelper->hasClientConfiguration()) {
                throw new \Exception('Client configuration not filled in');
            }
            $endpoint = $this->dataHelper->getApiEndpoint();
            $clientId = $this->dataHelper->getApiClientId();
            $clientSecret = $this->dataHelper->getApiClientSecret();

            $this->client = new Client($endpoint, $clientId, $clientSecret);
        }

        return $this->client;
    }

    public function executeTask(
        string $taskIdentifier,
        array $arguments = []
    ): TaskExecutionResult {
        return $this->getClient()
                    ->scheduleTask($taskIdentifier, $arguments, $this->dataHelper->getProjectEnvironmentIdentifier());
    }

}

