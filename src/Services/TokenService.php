<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\Config;
use App\Models\Token;
use App\Utils\ApiError;
use DateTime;
use DateInterval;

class TokenService
{
    public const TYPE_ACCESS = 'access';
    public const TYPE_REFRESH = 'refresh';
    public const TYPE_RESET_PASSWORD = 'resetPassword';
    public const TYPE_VERIFY_EMAIL = 'verifyEmail';

    public static function generateToken($userId, $expires, $type, $secret = null)
    {
        $secret = $secret ?? Config::get('jwt.secret');
        $payload = [
            'sub' => $userId,
            'iat' => time(),
            'exp' => $expires->getTimestamp(),
            'type' => $type,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    public static function saveToken($token, $userId, $expires, $type, $blacklisted = 0)
    {
        return Token::create([
            'token' => $token,
            'user_id' => $userId,
            'expires' => $expires->format('Y-m-d H:i:s'),
            'type' => $type,
            'blacklisted' => $blacklisted
        ]);
    }

    public static function verifyToken($token, $type)
    {
        try {
            $payload = JWT::decode($token, new Key(Config::get('jwt.secret'), 'HS256'));
        } catch (\Exception $e) {
            throw new ApiError(401, 'Invalid token');
        }

        $tokenDoc = Token::findOne([
            'token' => $token, 
            'type' => $type, 
            'user_id' => $payload->sub, 
            'blacklisted' => 0
        ]);

        if (!$tokenDoc) {
            throw new ApiError(404, 'Token not found');
        }

        return $tokenDoc;
    }

    public static function generateAuthTokens($user)
    {
        $accessTokenExpires = (new DateTime())->add(new DateInterval('PT' . Config::get('jwt.access_expiration_minutes') . 'M'));
        $accessToken = self::generateToken($user['id'], $accessTokenExpires, self::TYPE_ACCESS);

        $refreshTokenExpires = (new DateTime())->add(new DateInterval('P' . Config::get('jwt.refresh_expiration_days') . 'D'));
        $refreshToken = self::generateToken($user['id'], $refreshTokenExpires, self::TYPE_REFRESH);
        
        self::saveToken($refreshToken, $user['id'], $refreshTokenExpires, self::TYPE_REFRESH);

        return [
            'access' => [
                'token' => $accessToken,
                'expires' => $accessTokenExpires->format(DateTime::ATOM),
            ],
            'refresh' => [
                'token' => $refreshToken,
                'expires' => $refreshTokenExpires->format(DateTime::ATOM),
            ],
        ];
    }
}