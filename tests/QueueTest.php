<?php

namespace Glebsky\SimpleQueueTest;

use Glebsky\SimpleQueue\Queue;
use Glebsky\SimpleQueue\Transports\PDOTransport;
use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{
    public function testQueueDispatch()
    {
        $transport = new PDOTransport('localhost:3306','simple_queue','root','');
        $queue     = new Queue($transport);
        $job       = new TestJob('testmail@gmail.com', 'Test Subject', 'Test Message text');
        $result    = $queue->dispatch($job);

        self::assertTrue($result);
    }
}