<?php

namespace Glebsky\SimpleQueueTest;

use Glebsky\SimpleQueue\Message;
use Glebsky\SimpleQueue\Transports\PDOTransport;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{

    public function testSendMessage()
    {
        $transport = new PDOTransport('localhost:3306','simple_queue','root','');
        $message   = $this->generateTestMessage();
        $message   = $transport->send($message);

        self::assertTrue($message instanceof Message);
    }

    public function testFetchMessage()
    {
        $transport = new PDOTransport('localhost:3306','simple_queue','root','');
        $message   = $transport->fetchMessage(['test_queue']);

        self::assertTrue($message instanceof Message);
    }

    public function testChangeMessageStatus()
    {
        $transport = new PDOTransport('localhost:3306','simple_queue','root','');
        $message   = $transport->fetchMessage(['test_queue']);
        $transport->changeMessageStatus($message, Message::STATUS_IN_PROCESS);
        self::assertEquals(Message::STATUS_IN_PROCESS, $message->status);

        $transport->changeMessageStatus($message, Message::STATUS_NEW);
        self::assertEquals(Message::STATUS_NEW, $message->status);
    }

    public function testDeleteMessage()
    {
        $transport = new PDOTransport('localhost:3306','simple_queue','root','');
        $message   = $transport->fetchMessage(['test_queue']);

        $result = $transport->deleteMessage($message);
        self::assertTrue($result);
    }

    private function generateTestMessage(): Message
    {
        $job                 = new TestJob('example@gmail.com', 'TEST EMail', 'Test body text');
        $message             = new Message();
        $message->status     = Message::STATUS_NEW;
        $message->created_at = date("Y-m-d H:i:s", time());
        $message->job        = get_class($job);
        $message->body       = serialize($job);
        $message->attempts   = 0;
        $message->priority   = $job->priority;
        $message->queue      = $job->queueName;

        return $message;
    }
}