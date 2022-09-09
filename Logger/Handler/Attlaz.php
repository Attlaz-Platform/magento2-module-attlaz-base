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

    private Data $dataHelper;
    private ?AttlazHandler $handler = null;
    private bool $initialized = false;

    public function __construct(Data $dataHelper)
    {
        parent::__construct(Logger::INFO, true);
        $this->dataHelper = $dataHelper;
    }


    public function handle(array $record): bool
    {
        if ($record['level'] < $this->level) {
            return false;
        }


        if (!$this->dataHelper->hasLogStream()) {
            return false;
        }

        if (!$this->initialized) {
            $this->initialize();
        }

        if (!\is_null($this->handler)) {
            return $this->handler->handle($record);
        }
        return true;
    }


    private function initialize(): void
    {
        $this->initialized = true;
        $client = null;

        $minLogLevel = $this->dataHelper->getMinLogLevel();
        $this->setLevel($minLogLevel);

        if ($this->dataHelper->hasLogStream()) {


            try {
                $client = $this->dataHelper->getClient();
            } catch (\Throwable $ex) {

            }
            if (!\is_null($client) && $this->dataHelper->hasProjectIdentifier() && $this->dataHelper->hasProjectEnvironmentIdentifier()) {
                $level = Logger::DEBUG;
                $bubble = true;


                //try {
                $handler = new AttlazHandler($client, new LogStreamId($this->dataHelper->getLogStreamId()), $level, $bubble);


                $this->handler = $handler;
//            } catch (\Throwable $ex) {
//
//            }

            }

        }
    }
}
