<?php
namespace Glebsky\SimpleQueueTest;

use Glebsky\SimpleQueue\JobInterface;

class TestJob implements JobInterface
{
    public $queueName = 'test_queue';
    public $priority  = 1;

    private $email;
    private $subject;
    private $body;


    public function __construct($email, $subject,$body) {
        $this->email = $email;
        $this->subject = $subject;
        $this->body = $body;
    }

    public function handle(): string
    {
        mail($this->email,$this->subject,$this->body);
        return self::SUCCESS;
    }
}