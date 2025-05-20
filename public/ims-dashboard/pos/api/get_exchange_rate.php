<?php
// 1. Tell the browser “this is JSON” so fetch().then(r=>r.json()) won’t choke
header('Content-Type: application/json');

// 2. If your JS and PHP ever live on different origins, allow the call.
//    You can tighten this to your exact domain when you go to production.
header('Access-Control-Allow-Origin: *');

$mysqli = new mysqli("localhost", "root", "", "tappo_market");
if ($mysqli->connect_errno) {
    // On DB error, still return valid JSON
    http_response_code(500);
    echo json_encode(['error' => 'DB connect failed']);
    exit;
}
$mysqli->set_charset("utf8");

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
