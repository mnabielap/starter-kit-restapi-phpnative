<?php

namespace App\Services;

use App\Models\User;
use App\Utils\ApiError;

class UserService
{
    public static function createUser($userBody)
    {
        if (User::isEmailTaken($userBody['email'])) {
            throw new ApiError(400, 'Email already taken');
        }
        return User::create($userBody);
    }

    public static function queryUsers($filter, $options)
    {
        return User::paginate($filter, $options);
    }

    public static function getUserById($id)
    {
        return User::findById($id);
    }

    public static function getUserByEmail($email)
    {
        return User::findOne(['email' => $email]);
    }

    public static function updateUserById($userId, $updateBody)
    {
        $user = self::getUserById($userId);
        if (!$user) {
            throw new ApiError(404, 'User not found');
        }
        if (isset($updateBody['email']) && User::isEmailTaken($updateBody['email'], $userId)) {
            throw new ApiError(400, 'Email already taken');
        }
        
        return User::update($userId, $updateBody);
    }

    public static function deleteUserById($userId)
    {
        $user = self::getUserById($userId);
        if (!$user) {
            throw new ApiError(404, 'User not found');
        }
        User::delete($userId);
        return $user;
    }
}