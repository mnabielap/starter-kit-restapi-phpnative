<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;

// Helper to create middleware instance easily
function auth(...$rights) {
    return function($request) use ($rights) {
        $middleware = new AuthMiddleware($rights);
        $middleware->handle($request);
    };
}

// --- Auth Routes ---
$router->post('/v1/auth/register', [AuthController::class, 'register']);
$router->post('/v1/auth/login', [AuthController::class, 'login']);
$router->post('/v1/auth/logout', [AuthController::class, 'logout']);
$router->post('/v1/auth/refresh-tokens', [AuthController::class, 'refreshTokens']);

// --- User Routes ---

// Create User (Admin only)
$router->post('/v1/users', [UserController::class, 'createUser'], [auth('manageUsers')]);

// Get Users (Admin only)
$router->get('/v1/users', [UserController::class, 'getUsers'], [auth('getUsers')]);

// Get Specific User (Auth required)
$router->get('/v1/users/:userId', [UserController::class, 'getUser'], [auth('getUsers')]);

// Update User (Auth required)
$router->patch('/v1/users/:userId', [UserController::class, 'updateUser'], [auth('manageUsers')]);

// Delete User (Auth required)
$router->delete('/v1/users/:userId', [UserController::class, 'deleteUser'], [auth('manageUsers')]);

// --- Documentation Route (Swagger Placeholder) ---
$router->get('/v1/docs', function() {
    echo json_encode(['message' => 'API Documentation endpoint. Swagger UI not integrated in Native PHP starter.']);
});