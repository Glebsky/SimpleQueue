<?php

use Glebsky\SimpleQueue\Example\DBTransport;
use Glebsky\SimpleQueue\Example\TestJob;
use Glebsky\SimpleQueue\Queue;

require_once '../vendor/autoload.php';
require 'DBTransport.php';
require 'TestJob.php';

$transport = new DBTransport();
$queue     = new Queue($transport);
$job       = new TestJob('testmail@gmail.com', 'Test Subject', 'Test Message text');
$result    = $queue->dispatch($job);

echo 'Job successfully dispatched';