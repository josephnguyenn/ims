<?php
session_start();
if (!in_array($_SESSION['role'], ['admin', 'manager'])) {
    die("Access Denied.");
}
include "../define.php";

// Xác định section hiện tại
$section = $_GET['section'] ?? 'exchange';
?>
<!DOCTYPE html>
<html lang="vi">
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
        .settings-menu a:hover, .settings-menu a.active {
            background: #7da9ee;
        }
        .settings-content {
            flex: 1;
            padding: 40px;
            background: #fff;
            overflow-y: auto;
        }
        .settings-content h2 { margin-top: 0; }
        input[type="text"], input[type="time"], input[type="number"] {
            padding: 10px; width: 200px; font-size: 16px; margin-top: 5px;
        }
        button { padding: 10px 20px; background: #94B9F1; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #7da9ee; }
        .btn-delete { color: red; text-decoration: none; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        form.inline { display: inline-block; margin: 0; }
        .success { color: green; margin-top: 20px; }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="settings-wrapper">

    <!-- Sidebar -->
    <div class="settings-menu">
    <h3>Cài Đặt</h3>
    <a href="?section=exchange" class="<?= $section==='exchange'?'active':'' ?>">Tỷ giá</a>
    <a href="?section=shifts"   class="<?= $section==='shifts'  ?'active':'' ?>">Ca làm việc</a>
    <a href="?section=reports"  class="<?= $section==='reports' ?'active':'' ?>">Báo cáo</a>
    <a href="?section=other"    class="<?= $section==='other'   ?'active':'' ?>">Khác</a>
    </div>

    <!-- Main content -->
    <div class="settings-content">
        <?php
            switch ($section) {
            case 'exchange': include 'settings_exchange_rate.php'; break;
            case 'shifts':   include 'settings_shifts_content.php'; break;
            case 'reports':  include 'settings_reports_content.php'; break;
            default: echo '<h2>Coming Soon…</h2>';
            }
        ?>
    </div>

</div>
</body>
</html>
