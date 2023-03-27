<?php
require_once '../vendor/autoload.php';
require_once 'DBTransport.php';

use Glebsky\SimpleQueue\Example\DBTransport;
use Glebsky\SimpleQueue\Worker;

if (isset($argv[1])) {
    $queues = explode(',', $argv[1]);
} else {
    $queues = [];
}

$transport = new DBTransport();
$worker    = new Worker($transport);
$worker->run($queues);