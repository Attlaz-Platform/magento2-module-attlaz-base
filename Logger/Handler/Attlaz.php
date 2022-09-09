<?php
declare(strict_types=1);

namespace Attlaz\Base\Logger\Handler;

use Attlaz\AttlazMonolog\Handler\AttlazHandler;
use Attlaz\Base\Helper\Data;
use Attlaz\Model\Log\LogStreamId;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class Attlaz extends AbstractHandler
{
    /** @var Data */
    private Data $dataHelper;
    /** @var AttlazHandler|null */
    private ?AttlazHandler $handler = null;
    /** @var bool */
    private bool $initialized = false;

    /**
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        parent::__construct(Logger::INFO, true);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $record): bool
    {
        if (!$this->initialized) {
            $minLogLevel = $this->dataHelper->getMinLogLevel();
            $this->setLevel($minLogLevel);
        }

        if ($record['level'] < $this->level) {
            return false;
        }

        if (!$this->isActive()) {
            return false;
        }

        if (!$this->initialized) {
            $this->initialize();
        }

        if ($this->handler !== null) {
            return $this->handler->handle($record);
        }
        return true;
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
                $level = Logger::DEBUG;
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
        if (!$this->dataHelper->hasProjectIdentifier()) {
            return false;
        }
        if (!$this->dataHelper->hasProjectEnvironmentIdentifier()) {
            return false;
        }
        if (!$this->dataHelper->hasLogStream()) {
            return false;
        }
        return true;
    }
}
