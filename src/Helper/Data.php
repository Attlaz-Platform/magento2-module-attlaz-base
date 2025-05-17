<?php
declare(strict_types=1);

namespace Attlaz\Base\Helper;

use Attlaz\Client;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use function Safe\json_decode;

class Data
{
    /** @var string */
    public const EXTERNAL_ID_FIELD = 'attlaz_external_id';
    /** @var string */
    public const SYNC_TIME_FIELD = 'attlaz_sync_time';
    /** @var string */
    public const SYNC_STATUS_FIELD = 'attlaz_sync_status';
    /** @var string */
    public const SYNC_ID_FIELD = 'attlaz_sync_id';
    /** @var string */
    public const BLOCK_DATA_FLAG_CONTAINS_REAL_TIME_DATA = '_realtime';
    /** @var ScopeConfigInterface */
    protected ScopeConfigInterface $scopeConfig;
    /** @var Client|null */
    private ?Client $client = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig, private \Magento\Framework\Module\ModuleList\Loader $moduleLoader)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Determine if object has external identifier
     *
     * @param DataObject $dataObject
     * @return bool
     */
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

    /**
     * Get object external identifier
     *
     * @param DataObject $dataObject
     * @return string
     * @throws \Exception
     */
    public static function getExternalId(DataObject $dataObject): string
    {
        $externalId = $dataObject->getData(Data::EXTERNAL_ID_FIELD);
        if ($externalId !== null && $externalId !== false) {
            return self::parseExternalId($externalId);
        }
        return '';
    }

    /**
     * Parse external identifier
     *
     * TODO: refactor following to different external id helper
     *
     * @param string $externalId
     * @param string $key
     * @return string
     * @throws \Exception
     */
    private static function parseExternalId(string $externalId, string $key = 'external_id'): string
    {
        $externalId = trim($externalId);
        if (substr($externalId, 0, 1) === '{') {
            $externalIdObject = json_decode($externalId, true, 512, JSON_THROW_ON_ERROR);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (isset($externalIdObject[$key])) {
                    return $externalIdObject[$key];
                }
            } else {
                $msg = 'Unable to parse external id (' . $externalId . ', ' . json_last_error() . ')';
                throw new \ErrorException($msg);
            }
        }
        if (strpos($externalId, ':') !== false) {
            throw new \ErrorException('Unable to parse external id (' . $externalId . ')');
        }

        return $externalId;
    }

    /**
     * Determine if client is configured
     *
     * @return bool
     */
    public function hasClientConfiguration(): bool
    {
        if (empty($this->getApiEndpoint())) {
            return false;
        }
        if (!empty($this->getApiToken())) {
            return true;
        }
        return !empty($this->getApiClientId()) && !empty($this->getApiClientSecret());
    }

    /**
     * Get Attlaz client
     *
     * @return Client|null
     */
    public function getClient(): Client|null
    {
        if ($this->client === null && $this->hasClientConfiguration()) {

            $this->client = new Client();
            $this->client->setEndPoint($this->getApiEndpoint());

            $token = $this->getApiToken();
            if (!empty($token)) {
                $this->client->authWithToken($token);
            } else {
                $clientId = $this->getApiClientId();
                $clientSecret = $this->getApiClientSecret();

                $this->client->authWithClient($clientId, $clientSecret);
            }
        }

        return $this->client;
    }

    /**
     * Get API endpoint
     *
     * @return string|null
     */
    public function getApiEndpoint(): string|null
    {
        //$this->scopeConfig->getValue('attlaz/api/endpoint', ScopeInterface::SCOPE_STORE, null)
        return $this->scopeConfig->getValue('attlaz/api/endpoint');
    }

    /**
     * Get API client id
     *
     * @return string|null
     */
    public function getApiClientId(): string|null
    {
        return $this->scopeConfig->getValue('attlaz/api/client_id');
    }

    /**
     * Get API client secret
     *
     * @return string|null
     */
    public function getApiClientSecret(): string|null
    {
        return $this->scopeConfig->getValue('attlaz/api/client_secret');
    }

    /**
     * Get API token
     *
     * @return string|null
     */
    public function getApiToken(): string|null
    {
        return $this->scopeConfig->getValue('attlaz/api/token');
    }

    /**
     * Get task identifier
     *
     * @param string $task
     * @return string
     */
    public function getFlowIdentifier(string $task): string
    {
        return $this->scopeConfig->getValue($this->formatFlowIdentifierConfigPath($task));
    }

    /**
     * Determine if flow identifier is configured
     *
     * @param string $flow
     * @return bool
     */
    public function hasFlowIdentifier(string $flow): bool
    {
        return !empty($this->scopeConfig->getValue($this->formatFlowIdentifierConfigPath($flow)));
    }

    /**
     * Get project identifier
     *
     * @return string
     */
    public function getProjectIdentifier(): string
    {
        return $this->scopeConfig->getValue('attlaz/general/project');
    }

    /**
     * Determine if project identifier is configured
     *
     * @return bool
     */
    public function hasProjectIdentifier(): bool
    {
        return !empty($this->scopeConfig->getValue('attlaz/general/project'));
    }

    /**
     * Get project environment identifier
     *
     * @return string
     */
    public function getProjectEnvironmentIdentifier(): string
    {
        return $this->scopeConfig->getValue('attlaz/general/environment');
    }

    /**
     * Determine if project environment identifier is configured
     *
     * @return bool
     */
    public function hasProjectEnvironmentIdentifier(): bool
    {
        return !empty($this->scopeConfig->getValue('attlaz/general/environment'));
    }

    /**
     * Determine if log stream is configured
     *
     * @return bool
     */
    public function hasLogStream(): bool
    {
        try {
            return !empty($this->scopeConfig->getValue('attlaz/logging/logstream'));
        } catch (\Throwable $ex) {
            return false;
        }
    }

    /**
     * Get log stream id
     *
     * @return string
     */
    public function getLogStreamId(): string
    {
        return $this->scopeConfig->getValue('attlaz/logging/logstream');
    }

    /**
     * Get minimum log level
     *
     * @return int
     */
    public function getMinLogLevel(): int
    {
        $key = null;
        try {
            $key = $this->scopeConfig->getValue('attlaz/logging/minloglevel');
        } catch (\Throwable $ex) {
            return 200;
        }

        if (empty($key)) {
            return 200;
        }
        return (int)$key;
    }

    public function getLogFilterIgnoreRules(): array
    {

        try {
            $ignoreRules = $this->scopeConfig->getValue('attlaz/logging/ignore_rules');
        } catch (\Throwable $ex) {
            return [];
        }

        if (empty($ignoreRules)) {
            return [];
        }
        $ignoreRules = explode(PHP_EOL, $ignoreRules);
        return array_filter($ignoreRules);
    }

    public function getModuleVersion(): string
    {
        $modules = $this->moduleLoader->load();
        if (isset($modules['Attlaz_Base'])) {
            return $modules['Attlaz_Base']['setup_version'];
        }
        return '[Unknown]';
    }

    /**
     * Format flow identifier configuration path
     *
     * @param string $flow
     * @return string
     */
    private function formatFlowIdentifierConfigPath(string $flow): string
    {
        return 'attlaz/tasks/' . $flow . '_key';
    }
}
