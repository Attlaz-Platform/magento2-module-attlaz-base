<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Resource;

use Attlaz\Client;
use Attlaz\Model\TaskExecutionResult;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class BaseResource
{
    protected $logger;
    protected $scopeConfig;
    private $client;

    private $projectKey;
    private $environmentKey;

    public function __construct(ScopeConfigInterface $scopeConfig, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    public function getClient(): Client
    {
        if (is_null($this->client)) {
            $endpoint = $this->scopeConfig->getValue('attlaz/api/endpoint');
            $clientId = $this->scopeConfig->getValue('attlaz/api/client_id');
            $clientSecret = $this->scopeConfig->getValue('attlaz/api/client_secret');

            if (empty($endpoint)) {
                throw new \Exception('Invalid endpoint configuration (empty)');
            }
            if (empty($clientId)) {
                throw new \Exception('Invalid client id configuration (empty)');
            }
            if (empty($clientSecret)) {
                throw new \Exception('Invalid client secret configuration (empty)');
            }

            $this->client = new Client($endpoint, $clientId, $clientSecret);
        }

        return $this->client;
    }

    public function hasClientConfiguration(): bool
    {
        $endpoint = $this->scopeConfig->getValue('attlaz/api/endpoint');
        $clientId = $this->scopeConfig->getValue('attlaz/api/client_id');
        $clientSecret = $this->scopeConfig->getValue('attlaz/api/client_secret');

        if (!empty($endpoint) && !empty($clientId) && !empty($clientSecret)) {
            return true;
        }

        return false;
    }

    public function executeTask(
        string $task,
        array $arguments = [],
        int $projectEnvironmentId = null
    ): TaskExecutionResult {
        return $this->getClient()
                    ->scheduleTask($task, $arguments, $this->getProjectEnvrionmentIdentifier());
    }

    public function getTaskIdentifier(string $task)
    {
        return $this->scopeConfig->getValue('attlaz/tasks/' . $task . '_key');
    }

    public function getProjectIdentifier(): string
    {
        return $this->scopeConfig->getValue('attlaz/general/project');
    }

    public function getProjectEnvironmentIdentifier(): int
    {
        $projectEnvironmentId = $this->scopeConfig->getValue('attlaz/general/environment');
        if (empty($projectEnvironmentId) || !\is_numeric($projectEnvironmentId)) {
            throw new \Exception('Invalid project environment');
        }

        return \intval($projectEnvironmentId);
    }

}

