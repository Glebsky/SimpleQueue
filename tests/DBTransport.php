<?php

namespace Glebsky\SimpleQueueTest;

use Glebsky\SimpleQueue\Message;
use Glebsky\SimpleQueue\TransportInterface;
use InvalidArgumentException;
use PDO;

class DBTransport implements TransportInterface
{
    private $connection;
    private $tableName = 'jobs';

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $host     = 'localhost:3306';
        $dbname   = 'simple_queue';
        $username = 'root';
        $password = '';
        $pdo      = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $pdo;
    }

    /**
     * Send message to queue
     *
     * @param Message $message
     * @return Message
     */
    public function send(Message $message): Message
    {
        $data = [
            'status'     => $message->status,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'attempts'   => $message->attempts,
            'queue'      => $message->queue,
            'job'        => $message->job,
            'body'       => $message->body,
            'priority'   => $message->priority,
            'error'      => $message->error
        ];

        $query =
            "INSERT INTO {$this->tableName} (status, created_at, updated_at, attempts, queue, job, body, priority, error) VALUES (:status, :created_at, :updated_at, :attempts, :queue, :job, :body, :priority, :error)";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute($data);

        $message->id = (int)$this->connection->lastInsertId();

        return $message;
    }

    /**
     * Send message to queue
     *
     * @param Message $message
     * @return Message
     */
    public function update(Message $message): Message
    {
        $data = [
            'id'         => $message->id,
            'status'     => $message->status,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'attempts'   => $message->attempts,
            'queue'      => $message->queue,
            'job'        => $message->job,
            'body'       => $message->body,
            'priority'   => $message->priority,
            'error'      => $message->error
        ];

        $query =
            "UPDATE {$this->tableName} SET status = :status, created_at = :created_at, updated_at = :updated_at, attempts = :attempts, queue = :queue, job = :job, body = :body, priority = :priority, error = :error WHERE id = :id";
        $stmt  = $this->connection->prepare($query);
        $stmt->execute($data);

        $message->id       = (int)$message->id;
        $message->status   = (int)$message->status;
        $message->priority = (int)$message->priority;

        return $message;
    }

    /**
     * Fetch the next message from the queue
     *
     * @param array $queues
     * @return Message|NULL
     */
    public function fetchMessage(array $queues = [])
    {
        $query = "SELECT * FROM {$this->tableName} WHERE status = " . Message::STATUS_NEW;
        if (!empty($queues)) {
            $inClause = implode("', '", $queues);
            $inClause = "'" . $inClause . "'";
            $query    .= " AND queue IN ($inClause) ";
        } else {
            $query .= ' AND queue IS NULL ';
        }
        $query .= 'ORDER BY priority DESC, created_at ASC';
        $query .= ' LIMIT 1';

        $stmt = $this->connection->query($query);

        $row = $stmt->fetch();
        if (!$row) {
            return NULL;
        }
        $message             = new Message();
        $message->id         = (int)$row['id'];
        $message->status     = (int)$row['status'];
        $message->created_at = $row['created_at'];
        $message->updated_at = $row['updated_at'];
        $message->attempts   = (int)$row['attempts'];
        $message->queue      = $row['queue'];
        $message->job        = $row['job'];
        $message->body       = $row['body'];
        $message->priority   = (int)$row['priority'];
        $message->error      = $row['error'];

        return $message;
    }

    public function getErrorMessages(array $queues = []): array
    {
        $query = "SELECT * FROM {$this->tableName} WHERE status = " . Message::STATUS_ERROR;
        if (!empty($queues)) {
            $inClause = implode("', '", $queues);
            $inClause = "'" . $inClause . "'";
            $query    .= " AND queue IN ($inClause) ";
        } else {
            $query .= ' AND queue IS NULL ';
        }
        $query .= 'ORDER BY created_at ASC, priority DESC';

        $stmt = $this->connection->query($query);

        $resultArr = [];
        while ($row = $stmt->fetch()) {
            $message             = new Message();
            $message->id         = (int)$row['id'];
            $message->status     = (int)$row['status'];
            $message->created_at = $row['created_at'];
            $message->updated_at = $row['updated_at'];
            $message->attempts   = (int)$row['attempts'];
            $message->queue      = $row['queue'];
            $message->job        = $row['job'];
            $message->body       = $row['body'];
            $message->priority   = (int)$row['priority'];
            $message->error      = $row['error'];

            $resultArr[] = $message;
        }

        return $resultArr;
    }

    /**
     * Change message status from queue
     *
     * @param Message $message
     * @param int     $status
     * @return Message
     */
    public function changeMessageStatus(Message $message, int $status): Message
    {
        if (!is_numeric($message->id)) {
            throw new InvalidArgumentException('Message Id must be set.');
        }
        $query = "UPDATE {$this->tableName} SET status = :status WHERE id = :id";
        $stmt  = $this->connection->prepare($query);

        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $message->id);

        $stmt->execute();

        $message->id     = (int)$message->id;
        $message->status = (int)$status;
        return $message;
    }

    /**
     * Delete message from queue
     *
     * @param Message $message
     * @return bool
     */
    public function deleteMessage(Message $message): bool
    {
        if (!is_numeric($message->id)) {
            throw new InvalidArgumentException('Message Id must be set.');
        }
        $query = "DELETE FROM {$this->tableName} WHERE id = :id";

        $stmt = $this->connection->prepare($query);
        $stmt->execute([':id' => $message->id]);

        return true;
    }
}