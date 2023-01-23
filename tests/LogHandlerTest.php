<?php
declare(strict_types=1);

use Attlaz\Base\Helper\Data;
use Attlaz\Base\Logger\Handler\AttlazMagentoLogHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LogHandlerTest extends TestCase
{
    private function createLogRecord(int $level = Logger::WARNING, string $message = 'test', string $channel = 'test', array $context = []): array
    {
        return [
            'message'    => $message,
            'context'    => $context,
            'level'      => $level,
            'level_name' => Logger::getLevelName($level),
            'channel'    => $channel,
            'datetime'   => new DateTimeImmutable('now'),
            'extra'      => [
                'nested' => [
                    'data' => 'Nested log data',
                ],
            ],
        ];
    }

    public function testFilterbyMessage(): void
    {
        $dataHelperStub = $this->createStub(Data::class);
        $dataHelperStub->method('getLogFilterRules')
            ->willReturn([
                ['/^test$/'],
                '/^another test$/',
                '/^Could not acquire lock for cron job:/',
                "/^Failed cm checkEmailInList: We couldn't find the resource you're looking for. Please check the documentation and try again$/"
            ]);

        $handler = new AttlazMagentoLogHandler($dataHelperStub);


        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord(Logger::WARNING, 'test')));
        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord(Logger::INFO, 'another test')));
        $this->assertFalse($handler->isRecordFiltered($this->createLogRecord(Logger::INFO, 'I could not acquire lock for cron job: test')));
        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord(Logger::INFO, 'Could not acquire lock for cron job: sales_send_order_invoice_emails')));
        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord(Logger::INFO, "Failed cm checkEmailInList: We couldn't find the resource you're looking for. Please check the documentation and try again")));
    }

    public function testFilterByProperty(): void
    {
        $dataHelperStub = $this->createStub(Data::class);
        $dataHelperStub->method('getLogFilterRules')
            ->willReturn([
                ['channel', '/^test$/'],
                ['level_name', '/^(INFO|DEBUG)$/'],
            ]);

        $handler = new AttlazMagentoLogHandler($dataHelperStub);


        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord()));
        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord(Logger::DEBUG, 'test', 'testing')));
        $this->assertTrue($handler->isRecordFiltered($this->createLogRecord(Logger::INFO, 'test', 'testing')));
        $this->assertFalse($handler->isRecordFiltered($this->createLogRecord(Logger::WARNING, 'test', 'testing')));
    }

}
