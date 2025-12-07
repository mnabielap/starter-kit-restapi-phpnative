<?php

namespace App\Middlewares;

use App\Core\Request;
use App\Services\TokenService;
use App\Models\User;
use App\Utils\ApiError;
use App\Config\Config;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $requiredRights;

    public function __construct($requiredRights = [])
    {
        $this->requiredRights = $requiredRights;
    }

    public function handle(Request $request)
    {
        $authHeader = $request->getHeader('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new ApiError(401, 'Please authenticate');
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key(Config::get('jwt.secret'), 'HS256'));
        } catch (\Exception $e) {
            throw new ApiError(401, 'Please authenticate');
        }

        $user = User::findById($decoded->sub);

        if (!$user) {
            throw new ApiError(401, 'Please authenticate');
        }

        // Attach user to request
        $request->user = $user;

        // Role based authorization (Basic implementation)
        if (!empty($this->requiredRights)) {
            // Here we implement simple check: if 'manageUsers' is required, role must be 'admin'.
            $roleRights = [
                'user' => [],
                'admin' => ['getUsers', 'manageUsers'],
            ];

            $userRights = $roleRights[$user['role']] ?? [];
            $hasRights = true;

            foreach ($this->requiredRights as $right) {
                if (!in_array($right, $userRights)) {
                    $hasRights = false;
                    break;
                }
            }

            // Allow user to manage their own data if specifically checking parameters (context specific)
            if (!$hasRights) {
                // Check if accessing own data (User update/delete own profile)
                $resourceId = $request->getParam('userId');
                if ($resourceId && $resourceId == $user['id']) {
                    return; // Allow
                }
                throw new ApiError(403, 'Forbidden');
            }
        }
    }
}