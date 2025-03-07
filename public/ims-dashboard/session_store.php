<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['token'])) {
    $_SESSION['token'] = $data['token'];
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
