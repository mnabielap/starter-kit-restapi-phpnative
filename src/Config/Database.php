<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static $pdo = null;

    public static function connect()
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = Config::get('db');
        $dsn = '';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            if ($config['connection'] === 'sqlite') {
                $dbPath = dirname(__DIR__, 2) . '/' . $config['sqlite_path'];
                // Ensure directory exists
                $dir = dirname($dbPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                // Check if file exists, if not create it (PDO will create it, but good to be explicit)
                if (!file_exists($dbPath)) {
                    touch($dbPath);
                }
                $dsn = "sqlite:" . $dbPath;
            } elseif ($config['connection'] === 'mysql') {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            } else {
                throw new Exception("Unsupported database connection: " . $config['connection']);
            }

            self::$pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            return self::$pdo;

        } catch (PDOException $e) {
            // In production, log this, don't echo
            error_log("Database Connection Error: " . $e->getMessage());
            die(json_encode([
                "code" => 500,
                "message" => "Internal Server Error: Could not connect to database."
            ]));
        }
    }
}