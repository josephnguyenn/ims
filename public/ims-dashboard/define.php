<?php

// ✅ 1. Define BASE_URL from .env or fallback
if (! defined('BASE_URL')) {
    // Load .env first to get APP_URL
    $envPath = dirname(dirname(__DIR__)).'/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0 || ! strpos($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
        }
    }

    $envUrl = $_ENV['APP_URL'] ?? getenv('APP_URL');
    define('BASE_URL', rtrim($envUrl ?: 'http://127.0.0.1:8000', '/'));
}

// ✅ 3. Initialize MySQLi Connection if needed
if (! isset($mysqli)) {
    $dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
    $dbUser = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
    $dbPass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
    $dbName = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'tappomarket_ims';

    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) {
        exit('Database connection failed: '.$mysqli->connect_error);
    }
    $mysqli->set_charset('utf8');
}
