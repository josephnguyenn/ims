<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

include "../define.php";

// Lấy dữ liệu kho từ API
$apiUrl = BASE_URL . '/api/storages';
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $_SESSION['token'] // ✅ Bao gồm JWT token
]);
$response = curl_exec($ch);
curl_close($ch);

$storages = json_decode($response, true);

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
                <?php if (!empty($storages)): ?>
                    <?php foreach ($storages as $storage): ?>
                        <tr>
                            <td><?= htmlspecialchars($storage['id']) ?></td>
                            <td><?= htmlspecialchars($storage['name']) ?></td>
                            <td><?= htmlspecialchars($storage['location']) ?></td>
                            <td>
                                <button onclick="openEditForm(
                                    <?= $storage['id'] ?>,
                                    '<?= htmlspecialchars($storage['name'], ENT_QUOTES) ?>',
                                    '<?= htmlspecialchars($storage['location'], ENT_QUOTES) ?>'
                                )">Sửa</button>
                                <button onclick="deleteStorage(<?= $storage['id'] ?>)">Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">Không tìm thấy kho nào.</td></tr>
                <?php endif; ?>
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