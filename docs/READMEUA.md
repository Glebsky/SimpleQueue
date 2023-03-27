<h1 align="center">Simple Queue</h1>

<img src="logo.png" alt="Simple Queue" />
<p align="center">
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://badgen.net/github/release/glebsky/simplequeue" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/v/unstable" alt="Stable"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://poser.pugx.org/glebsky/simple-queue/license" alt="License"></a>
<a href="https://packagist.org/packages/glebsky/simple-queue"><img src="https://badgen.net/packagist/php/glebsky/simple-queue" alt="PHP Version"></a>
<br>
<br>
Прості черги та проста обробка черг для вашого PHP проекту
<p align="center">
    <a href="READMEUA.md">Ukrainian</a> | <a href="../README.md">English</a> | <a href="READMERU.md">Russian</a>
</p>

---
## Встановлення
Встановлення бібліотеки здійснюється через комп'ютер.
```
composer require glebsky/simple-queue
```

## Налаштування

Вам потрібно імплементувати інтерфейс `TransportInterface`.

Ви можете використовувати MySql, Redis або будь-яке інше зручне для вас сховище.

Дані які є в `Message` і ваш клас повинен ґрунтуватися на цих даних.

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

> Наприклад, можете орієнтуватися на `example/DBTranspot.php` який працює на основі PDO.

## Використання

#### Створення завдання

Вам необхідно створити свій Job клас імплементують інтерфейс `JobInterface`

- `public $queueName` - Якість у цьому класі Ви можете призначити чергу певного завдання. Якщо це властивість
  не додати, черга не буде призначена.

- `public $priority` - властивість у цьому класі Ви можете задати пріоритет у числах, чим вище число, тим вище
  пріоритет.

Метод `handle` У даному класі буде виконано при обробці черги. Тут ви поміщаєте необхідний вам код.

> Приклад класу можна знайти в `example/TestJob.php`

#### Додавання завдання до черги

Для додавання завдання у чергу використовується клас `Queue`. Нам необхідно створити екземпляр нашого `TransportInterface`
і з'єднати його з класом 'Queue'. Потім створюємо наше завдання - `TestJob` і додаємо завдання в чергу за допомогою
методу `dispatch`

```php
$transport = new DBTransport(); // create transport object
$queue = new Queue($transport); // add $transport to queue  
$job = new TestJob('testmail@gmail.com','Subject','Message text'); //  create job
$job->queueName = 'example_queue'; // you can change queue name
$job->priority = 3; // you can change priority
$result = $queue->dispatch($job); // send job to queue
```

#### Обробка черги

Черга обробляється за допомогою класу `Worker`.

```php
$transport = new DBTransport();
$worker = new Worker($transport);
$worker->run();
```

Ви можете створити свій PHP скрипт, де буде обробка завдань, і ви можете запустити цей скрипт через CLI.

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

Приклад запуску сценарію.

```sh
php Worker.php example_queue,test_queue
```

Де `example_queue, test_queue` - назви черг через кому, які будуть оброблятися. Якщо не передати
параметр назви черг, обробник працюватиме з чергами без імені.

Також є можливість обробити певне завдання, у зручному для вас місці вашої програми.

```php
$transport = new DBTransport();
$message = $transport->fetchMessage(['queue_name1','queue_name2'])
$worker->processJob($message);
```

### Тести
Для запуску тестів можна використовувати команду
`composer run-script test`

> Приклади можна знайти в папці `example`

<img src="uaflag.jpg" alt="UA FLAG" /> <small>Підтримай Україну</small>