<?php

namespace Glebsky\SimpleQueue;

interface JobInterface
{
    const SUCCESS = 'success';
    const ERROR = 'error';

    public function handle(): string;
}