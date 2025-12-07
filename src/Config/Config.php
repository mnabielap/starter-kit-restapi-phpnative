<?php

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        // Load .env
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();

        // Map and validate essential variables
        $this->config = [
            'env' => $_ENV['NODE_ENV'] ?? 'development',
            'port' => $_ENV['PORT'] ?? 3000,
            'db' => [
                'connection' => $_ENV['DB_CONNECTION'] ?? 'sqlite',
                'sqlite_path' => $_ENV['DB_SQLITE_PATH'] ?? 'database/database.sqlite',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['DB_PORT'] ?? 3306,
                'database' => $_ENV['DB_DATABASE'] ?? 'starter_kit',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
            ],
            'jwt' => [
                'secret' => $_ENV['JWT_SECRET'] ?? 'secret',
                'access_expiration_minutes' => (int)($_ENV['JWT_ACCESS_EXPIRATION_MINUTES'] ?? 30),
                'refresh_expiration_days' => (int)($_ENV['JWT_REFRESH_EXPIRATION_DAYS'] ?? 30),
                'reset_password_expiration_minutes' => (int)($_ENV['JWT_RESET_PASSWORD_EXPIRATION_MINUTES'] ?? 10),
                'verify_email_expiration_minutes' => (int)($_ENV['JWT_VERIFY_EMAIL_EXPIRATION_MINUTES'] ?? 10),
            ],
            'email' => [
                'smtp' => [
                    'host' => $_ENV['SMTP_HOST'] ?? '',
                    'port' => $_ENV['SMTP_PORT'] ?? 587,
                    'auth' => [
                        'user' => $_ENV['SMTP_USERNAME'] ?? '',
                        'pass' => $_ENV['SMTP_PASSWORD'] ?? '',
                    ],
                ],
                'from' => $_ENV['EMAIL_FROM'] ?? 'noreply@example.com',
            ],
        ];
    }

    public static function get($key = null)
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }

        if ($key === null) {
            return self::$instance->config;
        }

        // Allow dot notation (e.g., 'jwt.secret')
        $keys = explode('.', $key);
        $value = self::$instance->config;

        foreach ($keys as $nestedKey) {
            if (!isset($value[$nestedKey])) {
                return null;
            }
            $value = $value[$nestedKey];
        }

        return $value;
    }
}