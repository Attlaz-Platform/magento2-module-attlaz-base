<?php
declare(strict_types=1);

namespace Attlaz\Base\Helper;

use Attlaz\Client;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class Data
{

    const EXTERNAL_ID_FIELD = 'attlaz_external_id';
    const SYNC_TIME_FIELD = 'attlaz_sync_time';
    const BLOCK_DATA_FLAG_CONTAINS_REAL_TIME_DATA = '_realtime';

    protected $scopeConfig;
    private $client;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function hasClientConfiguration(): bool
    {


        $endpoint = $this->scopeConfig->getValue('attlaz/api/endpoint', ScopeInterface::SCOPE_STORE, null);
        $clientId = $this->scopeConfig->getValue('attlaz/api/client_id', ScopeInterface::SCOPE_STORE, null);
        $clientSecret = $this->scopeConfig->getValue('attlaz/api/client_secret');

        return (!empty($endpoint) && !empty($clientId) && !empty($clientSecret));
    }

    public function getClient(): ?Client
    {
        if (is_null($this->client) && $this->hasClientConfiguration()) {

            $endpoint = $this->getApiEndpoint();
            $clientId = $this->getApiClientId();
            $clientSecret = $this->getApiClientSecret();

            $this->client = new Client($endpoint, $clientId, $clientSecret);
        }

        return $this->client;
    }

    public function getApiEndpoint(): string
    {
        return $this->scopeConfig->getValue('attlaz/api/endpoint');
    }

    public function getApiClientId(): string
    {
        return $this->scopeConfig->getValue('attlaz/api/client_id');
    }

    public function getApiClientSecret(): string
    {
        return $this->scopeConfig->getValue('attlaz/api/client_secret');
    }

    public function getTaskIdentifier(string $task): string
    {
        return $this->scopeConfig->getValue($this->formatTaskIdentifierConfigPath($task));
    }

    public function hasTaskIdentifier(string $task): bool
    {
        return !empty($this->scopeConfig->getValue($this->formatTaskIdentifierConfigPath($task)));
    }

    private function formatTaskIdentifierConfigPath(string $task): string
    {
        return 'attlaz/tasks/' . $task . '_key';
    }

    public function getProjectIdentifier(): string
    {
        return $this->scopeConfig->getValue('attlaz/general/project');
    }

    public function hasProjectIdentifier(): bool
    {
        return !empty($this->scopeConfig->getValue('attlaz/general/project'));
    }

    public function getProjectEnvironmentIdentifier(): string
    {
        return $this->scopeConfig->getValue('attlaz/general/environment');
    }

    public function hasProjectEnvironmentIdentifier(): bool
    {
        $projectEnvironmentId = $this->scopeConfig->getValue('attlaz/general/environment');

        return !empty($projectEnvironmentId);
    }

    /**
     * TODO: refactor following to different external id helper
     */

    private static function parseExternalId(string $externalId, string $key = 'external_id'): string
    {
        $externalId = trim($externalId);
        if (substr($externalId, 0, 1) === '{') {
            $externalIdObject = json_decode($externalId, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($externalIdObject[$key])) {
                    return $externalIdObject[$key];
                }
            } else {
                throw new \Exception('Unable to parse external id (' . $externalId . ', ' . json_last_error() . ')');
            }
        }
        if (strpos($externalId, ':') !== false) {
            throw new \Exception('Unable to parse external id (' . $externalId . ')');
        }

        return $externalId;
    }

    public static function getExternalId(DataObject $dataObject): string
    {
        $externalId = $dataObject->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId !== null && $externalId !== false) {
            return self::parseExternalId($externalId);
        }

        return '';
    }

    public static function hasExternalId(DataObject $dataObject): bool
    {
        $hasExternalId = $dataObject->hasData(Data::EXTERNAL_ID_FIELD);
        if (!$hasExternalId) {
            return false;
        }
        $externalId = $dataObject->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId === null || $externalId === false || trim($externalId) === '') {
            return false;
        }

        return true;
    }
}
