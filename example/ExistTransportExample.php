<?php

use Glebsky\SimpleQueue\Example\TestJob;
use Glebsky\SimpleQueue\Queue;
use Glebsky\SimpleQueue\Transports\PDOTransport;
use Glebsky\SimpleQueue\Worker;

require_once '../vendor/autoload.php';
require_once 'TestJob.php';

//credentials
$host         = 'localhost:3306';
$db_name      = 'simple_queue';
$username     = 'root';
$password     = '';
$jobTableName = 'jobs';

//initialize PDO connection
$transport = new PDOTransport($host, $db_name, $username, $password, $jobTableName);

//check for migrations and existing 'jobs' table
$transport->migrate();

//Create new queue and add new job
$queue = new Queue($transport);
$job   = new TestJob('testmail@gmail.com', 'Test Subject', 'Test Message text');
//set properties for queue
$job->priority = 3;
//set queue Name
$job->queueName = 'email_queue';
// add job to queue
$result = $queue->dispatch($job);

//run worker to handle queue
$worker = new Worker($transport);
$worker->run(['email_queue']);
//or u can user to handle single job
$message = $transport->fetchMessage(['email_queue']);
$result  = $worker->processJob($message); // true or false