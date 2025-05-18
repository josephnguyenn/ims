<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
include "../define.php";

// Lấy dữ liệu Nhà cung cấp lô hàng
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

$shipmentSuppliers = fetchData(BASE_URL . '/api/shipment-suppliers');

// Tạo mã CSRF
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà cung cấp lô hàng</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>

    <?php include "../includes/header.php"; ?>

    <div class="main">
    <?php include "../includes/sidebar.php"; ?>
   <div class="main-content">

        <div class="main-content-header">
            <h1>Quản lý Nhà cung cấp lô hàng</h1>
            <button class="add-button" onclick="document.getElementById('addShipmentSupplierForm').style.display='block'">Thêm Nhà cung cấp lô hàng</button>
        </div>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Nhà cung cấp</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="shipment-supplier-table">
                <tr><td colspan="3">Đang tải...</td></tr>
            </tbody>
        </table>

        <!-- ✅ Form Thêm Nhà cung cấp lô hàng (Ẩn) -->
        <div id="addShipmentSupplierForm" style="display: none;">
            <h2>Thêm Nhà cung cấp lô hàng</h2>
            <form id="shipment-supplier-form">
                <input type="text" id="supplier_name" placeholder="Tên Nhà cung cấp" required>
                <button type="submit">Lưu</button>
                <button type="button" onclick="document.getElementById('addShipmentSupplierForm').style.display='none'">Hủy</button>
            </form>
        </div>

        <!-- ✅ Form Sửa Nhà cung cấp lô hàng (Ẩn) -->
        <div id="editShipmentSupplierForm" style="display: none;">
            <h2>Sửa Nhà cung cấp lô hàng</h2>
            <form id="edit-shipment-supplier-form">
                <input type="hidden" id="edit_supplier_id">
                <input type="text" id="edit_supplier_name" required>
                <button type="button" onclick="updateShipmentSupplier()">Cập nhật</button>
                <button type="button" onclick="document.getElementById('editShipmentSupplierForm').style.display='none'">Hủy</button>
            </form>
        </div>
    </div>
</div>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="../js/shipment-suppliers.js"></script>
</body>
</html>