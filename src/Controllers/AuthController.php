<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\TokenService;
use App\Services\EmailService;
use App\Utils\Validator;

class AuthController
{
    public static function register(Request $request)
    {
        $body = Validator::validate($request->getBody(), [
            'email' => 'required|email',
            'password' => 'required|min:8', // Add custom regex in Validator if needed
            'name' => 'required'
        ]);

        $user = UserService::createUser($body);
        $tokens = TokenService::generateAuthTokens($user);
        
        // Filter sensitive data
        unset($user['password']);

        Response::send(201, ['user' => $user, 'tokens' => $tokens]);
    }

    public static function login(Request $request)
    {
        $body = Validator::validate($request->getBody(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = AuthService::loginUserWithEmailAndPassword($body['email'], $body['password']);
        $tokens = TokenService::generateAuthTokens($user);
        
        unset($user['password']);

        Response::send(200, ['user' => $user, 'tokens' => $tokens]);
    }

    public static function logout(Request $request)
    {
        $body = Validator::validate($request->getBody(), [
            'refreshToken' => 'required'
        ]);

        AuthService::logout($body['refreshToken']);
        Response::send(204);
    }

    public static function refreshTokens(Request $request)
    {
        $body = Validator::validate($request->getBody(), [
            'refreshToken' => 'required'
        ]);

        $tokens = AuthService::refreshAuth($body['refreshToken']);
        Response::send(200, $tokens);
    }
}