<?php
declare(strict_types=1);

namespace Attlaz\Base\Model\Resource;

use Attlaz\Client;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class BaseResource
{
    protected $logger;
    protected $scopeConfig;
    private $client;

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
            $branch = $this->scopeConfig->getValue('attlaz/general/branch');

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

    public function getBranch(): string
    {
        return $this->scopeConfig->getValue('attlaz/general/branch');
    }

}
