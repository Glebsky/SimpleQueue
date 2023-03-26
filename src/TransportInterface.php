<?php

namespace Glebsky\SimpleQueue;

interface TransportInterface
{
    /**
     * Transport initialization
     */
    public function init();

    /**
     * Send message to queue
     *
     * @param Message $message
     * @return Message
     */
    public function send(Message $message): Message;

    /**
     * Update message to queue
     *
     * @param Message $message
     * @return Message
     */
    public function update(Message $message): Message;

    /**
     * Fetch the next message from the queue
     *
     * @param array $queues
     * @return Message|NULL
     */
    public function fetchMessage(array $queues = []);

    /**
     * Get All error queues
     * @param array $queues
     * @return array
     */
    public function getErrorMessages(array $queues = []): array;

    /**
     * Change message status from queue
     *
     * @param Message $message
     * @param int     $status
     * @return Message
     */
    public function changeMessageStatus(Message $message, int $status): Message;

    /**
     * Delete message from queue
     *
     * @param Message $message
     * @return bool
     */
    public function deleteMessage(Message $message): bool;
}