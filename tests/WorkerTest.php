<?php

namespace Glebsky\SimpleQueue;

use Glebsky\SimpleQueueTest\DBTransport;
use Glebsky\SimpleQueueTest\TestJob;
use Glebsky\SimpleQueueTest\TestJobFail;
use PHPUnit\Framework\TestCase;

class WorkerTest extends TestCase
{
    public function testWorker()
    {
        $transport = new DBTransport();
        $queue     = new Queue($transport);
        $job       = new TestJob('testmail@gmail.com', 'Test Subject', 'Test Message text');
        $result    = $queue->dispatch($job);
        self::assertTrue($result);

        $worker = new Worker($transport);
        while ($message = $transport->fetchMessage(['test_queue'])) {
            $worker->processJob($message);
        }
        self::assertTrue(true);
    }

    public function testWorkerError()
    {
        $transport = new DBTransport();
        $queue     = new Queue($transport);
        $job       = new TestJobFail('testmail@gmail.com', 'Test Subject', 'Test Message text');
        $result    = $queue->dispatch($job);
        self::assertTrue($result);

        $worker = new Worker($transport);
        while ($message = $transport->fetchMessage(['test_queue'])) {
            $worker->processJob($message);
        }
        $queue->deleteErrorQueues(['test_queue']);

        self::assertTrue(true);
    }
}