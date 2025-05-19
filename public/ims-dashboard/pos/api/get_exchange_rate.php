<?php
$mysqli = new mysqli("localhost", "root", "", "tappo_market");
$mysqli->set_charset("utf8");

$result = $mysqli->query("SELECT value FROM settings WHERE name = 'exchange_rate'");
$row = $result->fetch_assoc();

echo json_encode(['rate' => $row ? $row['value'] : 25]);
