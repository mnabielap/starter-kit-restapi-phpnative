<?php

// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Utils\ErrorHandler;
use App\Config\Config;

// Initialize Exception Handling
set_exception_handler([ErrorHandler::class, 'handleException']);
set_error_handler([ErrorHandler::class, 'handleError']);

// Initialize Config (loads env)
Config::get();

// Initialize Router
$router = new Router();

// Basic Route for testing
$router->get('/', function() {
    Response::send(200, ['message' => 'Welcome to the PHP Native REST API']);
});

// Load Routes
require_once __DIR__ . '/../src/routes.php';

// Dispatch Request
$router->dispatch(new Request());