<?php

namespace Glebsky\SimpleQueue;

use InvalidArgumentException;
use Throwable;

class Worker
{
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function run(array $queuesNames = [])
    {
        echo 'Worker started...' . PHP_EOL;
        while (true) {
            if ($message = $this->transport->fetchMessage($queuesNames)) {
                echo 'Message ID: ', $message->id . '. Job: ' . $message->job . PHP_EOL;
                try {
                    $this->processJob($message);
                    echo 'Job finished.' . PHP_EOL;
                } catch (Throwable $throwable) {
                    echo 'Job failed.' . PHP_EOL;
                    $this->processFailureResult($throwable, $message);
                }
                continue;
            }
            usleep(200000); // 0.2 second
        }
    }

    /**
     * Process current queue message
     * @param Message $message
     * @return bool
     */
    public function processJob(Message $message): bool
    {
        $this->transport->changeMessageStatus($message, Message::STATUS_IN_PROCESS);

        try {
            $job    = unserialize($message->body);
            $result = $job->handle();
            $this->processSuccessResult($result, $message);
            return true;
        } catch (Throwable $exception) {
            $this->processFailureResult($exception, $message);
            return false;
        }
    }

    private function setJobDone(Message $message)
    {
        $this->transport->deleteMessage($message);
    }

    private function rejectJob(Message $message)
    {
        $message->status     = Message::STATUS_ERROR;
        $message->updated_at = date("Y-m-d H:i:s", time());
        $message->attempts++;
        $this->transport->update($message);
    }

    private function processSuccessResult(string $result, Message $message)
    {
        if ($result === JobInterface::SUCCESS) {
            $this->setJobDone($message);
            return;
        }

        if ($result === JobInterface::ERROR) {
            $this->rejectJob($message);
            return;
        }

        throw new InvalidArgumentException(sprintf('Unsupported result status: "%s".', $result));
    }

    private function processFailureResult(Throwable $exception, Message $message)
    {
        $message->error = $exception->getMessage();
        $this->rejectJob($message);
    }
}