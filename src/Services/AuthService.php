<?php

namespace App\Services;

use App\Services\UserService;
use App\Services\TokenService;
use App\Utils\ApiError;
use App\Models\Token;

class AuthService
{
    public static function loginUserWithEmailAndPassword($email, $password)
    {
        $user = UserService::getUserByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new ApiError(401, 'Incorrect email or password');
        }
        return $user;
    }

    public static function logout($refreshToken)
    {
        $refreshTokenDoc = Token::findOne([
            'token' => $refreshToken, 
            'type' => TokenService::TYPE_REFRESH, 
            'blacklisted' => 0
        ]);
        
        if (!$refreshTokenDoc) {
            throw new ApiError(404, 'Not found');
        }
        
        Token::delete($refreshTokenDoc['id']);
    }

    public static function refreshAuth($refreshToken)
    {
        try {
            $refreshTokenDoc = TokenService::verifyToken($refreshToken, TokenService::TYPE_REFRESH);
            $user = UserService::getUserById($refreshTokenDoc['user_id']);
            if (!$user) {
                throw new \Exception();
            }
            Token::delete($refreshTokenDoc['id']);
            return TokenService::generateAuthTokens($user);
        } catch (\Exception $e) {
            throw new ApiError(401, 'Please authenticate');
        }
    }
}