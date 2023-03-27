<h1 align="center">Simple Queue</h1>

<img src="./docs/logo.png" alt="Simple Queue" />
<p align="center">
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://badgen.net/github/release/glebsky/simplequeue" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/v/unstable" alt="Stable"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/license" alt="License"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://badgen.net/packagist/php/glebsky/simple-queue" alt="PHP Version"></a>
<br>
<br>
Simple queues and simple queue handling for your PHP project
<p align="center">
    <a href="./docs/READMEUA.md">Ukrainian</a> | <a href="README.md">English</a> | <a href="./docs/READMERU.md">Russian</a>
</p>

---

## Installation

The library is installed via composer.

```
composer require glebsky/simple-queue
```

## Configuration

You need to implement the `TransportInterface` interface.

You can use MySql, Redis or any other storage that suits you.

The data that is present in `Message` and your class must be based on this data.

```shell
$id - int
$status - int
$created_at - timestamp
$updated_at - timestamp 
$attempts - int
$queue  - string
$job - string
$body - string
$priority - int
$error - string
```

> For example, you can refer to `example/DBTranspot.php` which works on PDO basis.

## Usage

#### Create a task

You need to create your own Job class to implement the interface `JobInterface`

- `public $queueName` - property in this class You can assign a queue to a particular job. If this property is not
  added, the queue will not be assigned.

- `public $priority` - property in this class You can set the priority in numbers, the higher the number, the higher the
  priority.

`handle` method In this class will be executed when processing the queue. This is where you put the code you need.

> An example class can be found in `example/TestJob.php`

#### Adding a task to the queue

The `Queue` class is used to add a task to the queue. We need to create an instance of our `TransportInterface` and
connect it to the `Queue` class. Then we create our task - `TestJob` and add the task to the queue using the `dispatch`
method

```php
$transport = new DBTransport(); // create transport object
$queue = new Queue($transport); // add $transport to queue  
$job = new TestJob('testmail@gmail.com','Subject','Message text'); //  create job
$job->queueName = 'example_queue'; // you can change queue name
$job->priority = 3; // you can change priority
$result = $queue->dispatch($job); // send job to queue
```

#### Обработка очереди

The queue is processed using the `Worker` class.

```php
$transport = new DBTransport();
$worker = new Worker($transport);
$worker->run();
```

You can create your own PHP script where tasks will be handled, and you can run this script via CLI.

```php
// Worker.php
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
```

An example of running a script.

```sh
php Worker.php example_queue,test_queue
```

Where `example_queue,test_queue` are the comma-separated names of the queues to be processed. If you do not pass the
queue name parameter, the handler will work with unnamed queues.

It is also possible to process a specific task, in a place convenient for you in your application.

```php
$transport = new DBTransport();
$message = $transport->fetchMessage(['queue_name1','queue_name2'])
$worker->processJob($message);
```

### Tests 
To run tests, you can use the command `composer run-script test`

> Examples can be found in the `example` folder

<img src="./docs/uaflag.jpg" alt="UA FLAG" /> <small>Stand With Ukraine</small>