<?php
session_start();
if (!in_array($_SESSION['role'], ['admin', 'manager'])) {
    die("Access Denied.");
}
include "../define.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS Settings</title>
    <style>
        body { font-family: Arial; margin: 0; background: #E8F1FF; }
        .settings-wrapper { display: flex; height: 100vh; }
        .settings-menu {
            width: 250px;
            background: #94B9F1;
            color: #fff;
            padding: 20px;
        }
        .settings-menu h3 { color: #fff; margin-top: 0; }
        .settings-menu a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .settings-menu a:hover {
            background: #7da9ee;
        }
        .settings-content {
            flex: 1;
            padding: 40px;
            background: #fff;
        }
        .settings-content h2 { margin-top: 0; }
        input[type="number"] { padding: 10px; width: 200px; font-size: 16px; }
        button { padding: 10px 20px; background: #94B9F1; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #7da9ee; }
        .success { color: green; margin-top: 20px; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="settings-wrapper">
    <div class="settings-menu">
        <h3>Cài Đặt</h3>
        <a href="?section=exchange">Chuyển đổi ngoại tệ</a>
        <a href="?section=other">Khác</a>
    </div>

    <div class="settings-content">
        <?php
        $section = $_GET['section'] ?? 'exchange';
        if ($section === 'exchange') {
            include 'settings_exchange_rate.php';
        } else {
            echo "<h2>Coming Soon...</h2>";
        }
        ?>
    </div>
</div>

</body>
</html>
