<h1 align="center">Simple Queue</h1>

<img src="logo.png" alt="Simple Queue" />
<p align="center">
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/v" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/v/unstable" alt="Stable"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/license" alt="License"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://badgen.net/packagist/php/glebsky/simple-queue" alt="PHP Version"></a>
<br>
<br>
Простые очереди и простая обработка очередей для вашего PHP проекта
<p align="center">
    <a href="../docs/READMEUA.md">Ukrainian</a> | <a href="../README.md">English</a> | <a href="READMERU.md">Russian</a>
</p>

---

## Установка

Установка библиотеки осуществляется через composer.
```
composer require glebsky/simple-queue
```

## Настройка

Вам нужно имплементировать интерфейс `TransportInterface`.

Вы можете использовать MySql, Redis или любое другое удобное для вас хранилище.

Данные которые присутствуют в `Message` и ваш класс должен основываться на этих данных.
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
> Для примера, можете ориентироваться на `example/DBTranspot.php` который работает на основе PDO

> Также вы можете использовать готовый `Transport` класс `Glebsky\SimpleQueue\Transports\PDOTransport` который устроен на основе PDO и работает с SQL базами данных.
> 
> Для создания таблицы очередей в Базе данных в данном классе присутствует метод `migrate`

## Использование

#### Создание задачи

Вам необходимо создать свой Job класс имплементируют интерфейс `JobInterface`

- `public $queueName` - свойство в этом классе Вы можете назначить очередь определенному заданию. Если это свойство
  не добавить, очередь не будет назначена.

- `public $priority` - свойство в этом классе Вы можете задать приоритет в числах, чем выше число, тем выше
  приоритет.

метод `handle` В данном классе будет выполнен при обработке очереди. Здесь вы помещаете необходимый для вас код.

> Пример класса можно найти в `example/TestJob.php`

#### Добавление задачи в очередь

Для добавления задачи в очередь используется класс `Queue`. Нам необходимо создать экземпляр нашего `TransportInterface`
и соединить его с классом `Queue`. Потом создаем нашу задачу - `TestJob` и добавляем задачу в очередь с помощью
метода `dispatch`

```php
$transport = new DBTransport(); // create transport object
$queue = new Queue($transport); // add $transport to queue  
$job = new TestJob('testmail@gmail.com','Subject','Message text'); //  create job
$job->queueName = 'example_queue'; // you can change queue name
$job->priority = 3; // you can change priority
$result = $queue->dispatch($job); // send job to queue
```

#### Обработка очереди

Очередь обрабатывается с помощью класса `Worker`.

```php
$transport = new DBTransport();
$worker = new Worker($transport);
$worker->run();
```

Вы можете создать свой PHP скрипт, где будет обработка задач, и вы можете запустить этот скрипт через CLI.

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

Пример запуска скрипта.

```sh
php Worker.php example_queue,test_queue
```

Где `example_queue,test_queue` - названия очередей через запятую, которые будут обрабатываться. Если не передать
параметр названия очередей, обработчик будет работать с очередями без имени.

Также есть возможность обработать определенную задачу, в удобном для вас месте вашего приложения.

```php
$transport = new DBTransport();
$message = $transport->fetchMessage(['queue_name1','queue_name2'])
$worker->processJob($message);
```

### Встроенный класс PDOTransport
Вы если Вы планируете настроить связь на основе SQL баз данных, вы можете использовать встроенный класс `PDOTransport`

Пример использования:
```php
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
```

### Тесты

Для запуска тестов можно использовать команду
`composer run-script test-simple-queues`

> Примеры вы можете найти в папке `example`

<img src="uaflag.jpg" alt="UA FLAG" /> <small>Поддержи Украину</small>