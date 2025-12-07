<?php

namespace App\Core;

class Response
{
    public static function send($statusCode, $data = null)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        if ($data !== null) {
            echo json_encode($data);
        }
        exit();
    }
}