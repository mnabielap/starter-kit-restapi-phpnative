<?php

namespace App\Utils;

use App\Config\Config;
use App\Core\Response;
use Throwable;

class ErrorHandler
{
    public static function handleException(Throwable $exception)
    {
        $statusCode = 500;
        $message = "Internal Server Error";

        if ($exception instanceof ApiError) {
            $statusCode = $exception->statusCode;
            $message = $exception->getMessage();
        }

        // Log error (simple implementation)
        error_log("[" . date('Y-m-d H:i:s') . "] Error: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());

        $response = [
            'code' => $statusCode,
            'message' => $message,
        ];

        if (Config::get('env') === 'development') {
            $response['stack'] = $exception->getTraceAsString();
        }

        Response::send($statusCode, $response);
    }

    public static function handleError($level, $message, $file, $line)
    {
        // Convert PHP errors/warnings to Exceptions
        throw new \ErrorException($message, 0, $level, $file, $line);
    }
}