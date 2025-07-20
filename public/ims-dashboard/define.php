<?php
// ✅ 1. Define BASE_URL from .env or fallback
if (!defined('BASE_URL')) {
    $envUrl = getenv('APP_URL');
    $defaultLocalUrl = 'http://localhost/ims/public';
    define('BASE_URL', rtrim($envUrl ?: $defaultLocalUrl, '/'));
}

// ✅ 2. Auto-load .env variables if not already loaded
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// ✅ 3. Initialize MySQLi Connection if needed
if (!isset($mysqli)) {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbUser = getenv('DB_USERNAME') ?: 'root';
    $dbPass = getenv('DB_PASSWORD') ?: '';
    $dbName = getenv('DB_DATABASE') ?: 'tappo_market';

    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8");
}
?>
