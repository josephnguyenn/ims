<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

include "../define.php";

// Tạo mã CSRF
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kho</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>"> <!-- Thêm mã CSRF -->
</head>
<body>    
    <?php include "../includes/header.php"; ?>


<div class="main">
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="main-content-header">
            <h1>Quản lý kho</h1>
            <button class="add-button" onclick="document.getElementById('addStorageForm').style.display='block'">Thêm kho</button>
        </div>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên kho</th>
                    <th>Vị trí</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="storage-table">
                <tr><td colspan="4" style="text-align: center;">
                    <div class="loading-spinner">Đang tải dữ liệu...</div>
                </td></tr>
            </tbody>
        </table>


        <!-- Form Thêm Kho (Ẩn) -->
        <div id="addStorageForm" style="display: none;">
            <h2>Thêm kho</h2>
            <form id="storage-form">
                <input type="text" id="storage-name" placeholder="Tên kho" required>
                <input type="text" id="storage-location" placeholder="Vị trí" required>
                <button type="submit">Lưu</button>
                <button type="button" onclick="document.getElementById('addStorageForm').style.display='none'">Hủy</button>
            </form>
        </div>

        <!-- Form Sửa Kho (Ẩn) -->
        <div id="editStorageForm" style="display: none;">
            <h2>Sửa kho</h2>
            <form id="edit-storage-form">
                <input type="hidden" id="edit-storage-id">
                <input type="text" id="edit-storage-name" placeholder="Tên kho" required>
                <input type="text" id="edit-storage-location" placeholder="Vị trí" required>
                <button type="submit">Cập nhật</button>
                <button type="button" onclick="document.getElementById('editStorageForm').style.display='none'">Hủy</button>
            </form>
        </div>

    </div>
</div>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
    <script src="../js/storage.js"></script>
</body>
</html>