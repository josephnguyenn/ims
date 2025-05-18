<?php
if (!defined('BASE_URL')) {
    $envUrl = getenv('APP_URL');
    $defaultLocalUrl = 'http://localhost/tappomarket/public'; // ✅ Point to Laravel API
    define('BASE_URL', rtrim($envUrl ?: $defaultLocalUrl, '/'));
}
?>