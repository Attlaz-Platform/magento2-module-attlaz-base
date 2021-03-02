<?php
declare(strict_types=1);

namespace Attlaz\Base\Logger\Handler;

use Attlaz\AttlazMonolog\Handler\AttlazHandler;
use Attlaz\Base\Helper\Data;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;

class Attlaz extends AbstractHandler
{

    private $dataHelper;

    /** @var AttlazHandler */
    private $handler;
    private $initialized = false;

    public function __construct(Data $dataHelper)
    {
        parent::__construct(Logger::DEBUG, true);
        $this->dataHelper = $dataHelper;
    }


    public function handle(array $record): bool
    {
        if (!$this->dataHelper->hasLogBucket()) {
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
        $this->initialize = true;
        $client = null;

        if ($this->dataHelper->hasLogBucket()) {


            try {
                $client = $this->dataHelper->getClient();
            } catch (\Throwable $ex) {

            }
            if (!\is_null($client) && $this->dataHelper->hasProjectIdentifier() && $this->dataHelper->hasProjectEnvironmentIdentifier()) {
                $level = Logger::DEBUG;
                $bubble = true;


                //try {
                $handler = new AttlazHandler($client, $level, $bubble);
                $handler->setProject($this->dataHelper->getProjectIdentifier(), $this->dataHelper->getProjectEnvironmentIdentifier());

                $this->handler = $handler;
//            } catch (\Throwable $ex) {
//
//            }

            }

        }
    }
}
