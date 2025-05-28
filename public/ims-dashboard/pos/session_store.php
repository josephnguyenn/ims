<?php
session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',             // <— make it valid site-wide
  'domain'   => $_SERVER['HTTP_HOST'],
  'secure'   => isset($_SERVER['HTTPS']),
  'httponly' => true,
  'samesite' => 'Lax'
]);
// session_store.php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['token']) || empty($data['role'])) {
  echo json_encode(['status'=>'error','message'=>'Missing token or role']);
  exit;
}

$_SESSION['token']   = $data['token'];
$_SESSION['role']    = $data['role'];
// store the user’s ID as well:
if (!empty($data['user_id'])) {
  $_SESSION['user_id'] = (int)$data['user_id'];
}

// you can also store name if you like:
$_SESSION['user_name'] = $data['name'] ?? '';

echo json_encode(['status'=>'success']);
