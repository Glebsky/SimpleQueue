<?php

namespace Glebsky\SimpleQueue;

class Queue
{
    private $transport;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function dispatch(JobInterface $job): bool
    {
        $message             = new Message();
        $message->status     = Message::STATUS_NEW;
        $message->created_at = date("Y-m-d H:i:s", time());
        $message->updated_at = NULL;
        $message->attempts   = 0;
        if (isset($job->queueName) && !empty($job->queueName)) {
            $message->queue = $job->queueName;
        } else {
            $message->queue = NULL;
        }
        $message->job  = get_class($job);
        $message->body = serialize($job);
        if (isset($job->priority) && !empty($job->priority)) {
            $message->priority = $job->priority;
        } else {
            $message->priority = 0;
        }
        $message->error = NULL;

        $this->send($message);
        return true;
    }

    public function deleteErrorQueues(array $queueNames = [])
    {
        $messages = $this->transport->getErrorMessages($queueNames);
        foreach ($messages as $message) {
            $this->delete($message);
        }
    }

    private function send(Message $message)
    {
        $this->transport->send($message);
    }

    private function delete(Message $message)
    {
        $this->transport->deleteMessage($message);
    }
}