<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\UserService;
use App\Utils\Validator;
use App\Utils\ApiError;

class UserController
{
    public static function createUser(Request $request)
    {
        $body = Validator::validate($request->getBody(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'name' => 'required',
            'role' => 'required|in:user,admin'
        ]);

        $user = UserService::createUser($body);
        unset($user['password']);
        
        Response::send(201, $user);
    }

    public static function getUsers(Request $request)
    {
        // Extract filters
        $filter = [
            'name' => $request->getQuery('name'),
            'role' => $request->getQuery('role'),
            'search' => $request->getQuery('search'),
            'scope' => $request->getQuery('scope'),
        ];

        // Extract options
        $options = [
            'sortBy' => $request->getQuery('sortBy'),
            'limit' => $request->getQuery('limit'),
            'page' => $request->getQuery('page'),
        ];

        $result = UserService::queryUsers($filter, $options);
        Response::send(200, $result);
    }

    public static function getUser(Request $request)
    {
        $userId = $request->getParam('userId');
        $user = UserService::getUserById($userId);
        
        if (!$user) {
            throw new ApiError(404, 'User not found');
        }
        
        unset($user['password']);
        Response::send(200, $user);
    }

    public static function updateUser(Request $request)
    {
        $userId = $request->getParam('userId');
        $body = Validator::validate($request->getBody(), [
            'email' => 'email',
            'password' => 'min:8',
            'name' => 'min:1'
        ]);

        $user = UserService::updateUserById($userId, $body);
        unset($user['password']);
        
        Response::send(200, $user);
    }

    public static function deleteUser(Request $request)
    {
        $userId = $request->getParam('userId');
        UserService::deleteUserById($userId);
        Response::send(204);
    }
}