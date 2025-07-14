<?php
declare(strict_types=1);

namespace Attlaz\Base\Logger\Handler;

use Attlaz\AttlazMonolog\Handler\AttlazHandler;
use Attlaz\Base\Helper\Data;
use Attlaz\Model\Log\LogStreamId;
use Monolog\Handler\AbstractHandler;
use Monolog\Level;
use Monolog\LogRecord;

class AttlazMagentoLogHandler extends AbstractHandler
{

    /** @var AttlazHandler|null */
    private AttlazHandler|null $handler = null;
    /** @var bool */
    private bool $initialized = false;
    private bool|null $isActive = null;

    /**
     * @param Data $dataHelper
     */
    public function __construct(
        private readonly Data $dataHelper
    )
    {
        parent::__construct(Level::Info, true);
    }

    /**
     * @inheritDoc
     */
    public function handle(LogRecord $record): bool
    {
        if (!$this->initialized) {
            $minLogLevel = $this->dataHelper->getMinLogLevel();
            $this->setLevel($minLogLevel);
        }

        if ($record->level < $this->level) {
            return false;
        }

        if (!$this->isActive()) {
            return false;
        }

        if (!$this->initialized) {
            $this->initialize();
        }

        if ($this->isRecordFiltered($record)) {
            return false;
        }

        if ($this->handler !== null) {
            return $this->handler->handle($record);
        }
        return true;
    }

    public function isRecordFiltered(LogRecord $record): bool
    {
        $logIgnoreRules = $this->dataHelper->getLogFilterIgnoreRules();
        foreach ($logIgnoreRules as $filterRule) {
            if (!is_array($filterRule) || count($filterRule) === 1) {
                $filterRule = ['message', is_array($filterRule) ? $filterRule[0] : $filterRule];
            }

            [$key, $pattern] = $filterRule;

            if (is_array($key)) {
                $value = array_reduce(
                    $key,
                    static function ($arr, $key) {
                        return $arr[$key] ?? null;
                    },
                    $record
                );
            } else {
                $value = $record[$key] ?? null;
            }

            if (preg_match($pattern, (string)$value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Initialize handler
     *
     * @return void
     */
    private function initialize(): void
    {
        $this->initialized = true;
        $client = null;

        if ($this->isActive()) {

            try {
                $client = $this->dataHelper->getClient();
            } catch (\Throwable $ex) {
                return;
            }
            $dataHelper = $this->dataHelper;
            if ($client !== null) {
                $level = Level::Debug;
                $bubble = true;

                //try {
                $logStreamId = new LogStreamId($this->dataHelper->getLogStreamId());
                $handler = new AttlazHandler($client, $logStreamId, $level, $bubble);

                $this->handler = $handler;
                //            } catch (\Throwable $ex) {
                //
                //            }

            }

        }
    }

    /**
     * Determine if handler is active
     *
     * @return bool
     */
    private function isActive(): bool
    {
        if (is_null($this->isActive)) {
            $this->isActive = $this->dataHelper->hasProjectIdentifier() && $this->dataHelper->hasProjectEnvironmentIdentifier() && $this->dataHelper->hasLogStream();
        }

        return $this->isActive;
    }
}
