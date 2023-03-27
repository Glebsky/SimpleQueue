<?php

use Glebsky\SimpleQueue\Example\DBTransport;
use Glebsky\SimpleQueue\Example\TestJob;
use Glebsky\SimpleQueue\Message;
use Glebsky\SimpleQueue\Queue;

require_once '../vendor/autoload.php';
require 'DBTransport.php';

$transport = new DBTransport();
$message   = new Message();

$transport = new \Glebsky\SimpleQueueTest\DBTransport();
$queue     = new Queue($transport);
$job       = new TestJob('testmail@gmail.com', 'Test Subject', 'Test Message text');
$result    = $queue->dispatch($job);

echo 'Job successfully dispatched';