<?php

namespace App\Core;

class Request
{
    private $params = [];
    private $body = [];
    private $query = [];
    public $user = null;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $this->parseBody();
    }

    private function parseBody()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $input = file_get_contents('php://input');
                $data = json_decode($input, true);
                return $data ?? [];
            }
            
            return $_POST;
        }
        return [];
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getQuery($key = null)
    {
        if ($key) return $this->query[$key] ?? null;
        return $this->query;
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    public function getParam($key)
    {
        return $this->params[$key] ?? null;
    }

    public function getHeader($key)
    {
        $normalizedKey = strtoupper(str_replace('-', '_', $key));
        $headerKey = 'HTTP_' . $normalizedKey;

        if (isset($_SERVER[$headerKey])) {
            return $_SERVER[$headerKey];
        }

        if ($normalizedKey === 'AUTHORIZATION') {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $header => $value) {
                if (strcasecmp($header, $key) === 0) {
                    return $value;
                }
            }
        }

        return null;
    }
}