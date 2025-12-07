<?php

namespace App\Utils;

use Exception;

class ApiError extends Exception
{
    public $statusCode;
    public $isOperational;

    public function __construct($statusCode, $message, $isOperational = true, $stack = '')
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->isOperational = $isOperational;
        // Stack trace is handled automatically by PHP's Exception class
    }
}