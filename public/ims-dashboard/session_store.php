<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['token']) && isset($data['role'])) {
    $_SESSION['token'] = $data['token'];
    $_SESSION['role'] = $data['role']; // Store the role in the session
    $_SESSION['name'] = $data['name']; // ✅ store the name
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Token and/or role not provided."]);
}
?>