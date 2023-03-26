<?php

namespace Glebsky\SimpleQueue;

class Message
{
    const STATUS_NEW        = 1;
    const STATUS_IN_PROCESS = 2;
    const STATUS_ERROR      = 3;

    public $id;
    public $status;
    public $created_at;
    public $updated_at;
    public $attempts;
    public $queue;
    public $job;
    public $body;
    public $priority;
    public $error;
}