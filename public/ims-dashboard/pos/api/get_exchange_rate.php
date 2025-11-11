<?php
// Load database configuration
require_once dirname(dirname(__DIR__)) . '/define.php';

// Tell the browser "this is JSON"
header('Content-Type: application/json');

// Allow CORS
header('Access-Control-Allow-Origin: *');

// Database connection is already available from define.php as $mysqli
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connect failed: ' . $mysqli->connect_error]);
    exit;
}

$result = $mysqli->query(
    "SELECT value
       FROM settings
      WHERE name = 'exchange_rate'
      LIMIT 1"
);

if (! $result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed']);
    exit;
}

$row = $result->fetch_assoc();
$rate = isset($row['value']) 
      ? (float)$row['value'] 
      : 25.0;

echo json_encode(['rate' => $rate]);
$mysqli->close();
