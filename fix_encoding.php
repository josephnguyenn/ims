<?php
/**
 * Fix Czech Character Encoding
 * Run this on production server via: php fix_encoding.php
 */

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$db   = $_ENV['DB_DATABASE'];

$mysqli = new mysqli($host, $user, $pass, $db);
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database: {$db}\n\n";

// 1. Convert database to utf8mb4
echo "Step 1: Converting database to utf8mb4...\n";
$mysqli->query("ALTER DATABASE `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "✓ Database converted\n\n";

// 2. Convert tables
$tables = ['products', 'customers', 'orders', 'shipments', 'product_categories'];

echo "Step 2: Converting tables to utf8mb4...\n";
foreach ($tables as $table) {
    $query = "ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($mysqli->query($query)) {
        echo "✓ Converted table: {$table}\n";
    } else {
        echo "✗ Failed: {$table} - " . $mysqli->error . "\n";
    }
}

echo "\n✅ Database conversion complete!\n";
echo "\nNow the Czech characters should display correctly.\n";
echo "If characters are still broken, you may need to re-import your data.\n";

$mysqli->close();
