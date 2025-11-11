<?php
// ✅ 1. Define BASE_URL from .env or fallback
if (!defined('BASE_URL')) {
    $envUrl = getenv('APP_URL');
    
    // Auto-detect the correct API URL based on how the page is accessed
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    if ($host === 'localhost' && strpos($_SERVER['REQUEST_URI'] ?? '', '/tappomarket/') !== false) {
        // Accessed via XAMPP - API is on Laravel dev server
        $defaultLocalUrl = 'http://127.0.0.1:8000';
    } else {
        // Accessed via Laravel dev server - use same URL
        $defaultLocalUrl = 'http://127.0.0.1:8000';
    }
    
    define('BASE_URL', rtrim($envUrl ?: $defaultLocalUrl, '/'));
}

// ✅ 2. Auto-load .env variables if not already loaded
$envPath = dirname(dirname(__DIR__)) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Remove quotes if present
        $value = trim($value, '"\'');
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

// ✅ 3. Initialize MySQLi Connection if needed
if (!isset($mysqli)) {
    $dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
    $dbUser = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: 'root';
    $dbPass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
    $dbName = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'tappomarket_ims';

    $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    if ($mysqli->connect_error) {
        die("Database connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset("utf8");
}
?>
