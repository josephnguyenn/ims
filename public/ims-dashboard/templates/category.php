<?php
// public/ims-dashboard/templates/category.php
session_start();
if (!isset($_SESSION['token'])) {
    header('Location: ../login.php');
    exit();
}
include "../define.php";

// Fetch data via API
function fetchData($apiUrl) {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Get all categories
$categories = fetchData(BASE_URL . '/api/categories');

// CSRF token for form submissions (if needed in JS requests)
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/add.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="main">
        <?php include "../includes/sidebar.php"; ?>
        <div class="main-content">
            <div class="main-content-header">
                <h1>Quản lý Danh mục sản phẩm</h1>
                <button class="add-button" onclick="document.getElementById('addCategoryForm').style.display='flex'">+ Thêm Danh mục</button>
            </div>

            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Hiển thị trong POS</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="category-table">
                    <tr><td colspan="4">Đang tải...</td></tr>
                </tbody>
            </table>

            <!-- Add Category Modal -->
            <div id="addCategoryForm" class="modal" style="display: none;">
                <span class="close" onclick="closeModal('addCategoryForm')">&times;</span>
                <div class="modal-content">
                    <h2>Thêm Danh mục</h2>
                    <form id="category-form">
                        <div class="add-row">
                            <label for="cat_name">Tên danh mục:</label>
                            <input type="text" id="cat_name" placeholder="Nhập tên danh mục" required />
                        </div>
                        <div class="add-row">
                            <label for="cat_visible">Hiển thị POS:</label>
                            <input type="checkbox" id="cat_visible" />
                        </div>
                        <div class="form-buttons">
                            <button type="submit">Lưu</button>
                            <button type="button" onclick="closeModal('addCategoryForm')">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <div id="editCategoryForm" class="modal" style="display: none;">
                <span class="close" onclick="closeModal('editCategoryForm')">&times;</span>
                <div class="modal-content">
                    <h2>Chỉnh sửa Danh mục</h2>
                    <form id="edit-category-form">
                        <input type="hidden" id="edit_cat_id" />
                        <div class="add-row">
                            <label for="edit_cat_name">Tên danh mục:</label>
                            <input type="text" id="edit_cat_name" required />
                        </div>
                        <div class="add-row">
                            <label for="edit_cat_visible">Hiển thị POS:</label>
                            <input type="checkbox" id="edit_cat_visible" />
                        </div>
                        <div class="form-buttons">
                            <button type="button" onclick="updateCategory()">Cập nhật</button>
                            <button type="button" onclick="closeModal('editCategoryForm')">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="../js/categories.js"></script>
</body>
</html>