<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
include "../define.php";

// Lấy dữ liệu Kho & Nhà cung cấp lô hàng
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



$storages = fetchData(BASE_URL . '/api/storages');
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
    <title>Quản lý lô hàng</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>

<?php include "../includes/header.php"; ?>


<div class="main">
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="main-content-header">
            <h1>Quản lý lô hàng</h1>
            <button class="add-button" onclick="openModal('addShipmentForm')">Thêm Lô Hàng</button>
        </div>
        
        <table border="1">
            <thead>
                <tr>
                    <th>Mã lô</th>
                    <th>Nhà cung cấp</th>
                    <th>Kho</th>
                    <th>Ngày đặt</th>
                    <th>Ngày nhận</th>
                    <th>Ngày hết hạn</th>
                    <th>Chi phí</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="shipment-table">
                <tr><td colspan="8">Đang tải...</td></tr>
            </tbody>
        </table>

        <!-- Form Thêm Lô Hàng (Modal) -->
        <div id="addShipmentForm" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Thêm Lô Hàng</h2>
                <form id="shipment-form">
                    <div class="add-row">
                        <label for="shipment_supplier_id">Nhà cung cấp lô hàng:</label>
                        <select id="shipment_supplier_id" required>
                            <option value="">Chọn nhà cung cấp</option>
                            <?php foreach ($shipmentSuppliers as $supplier): ?>
                                <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="add-row">
                        <label for="storage_id">Vị trí kho:</label>
                        <select id="storage_id" required>
                            <option value="">Chọn kho</option>
                            <?php foreach ($storages as $storage): ?>
                                <option value="<?= htmlspecialchars($storage['id']) ?>"><?= htmlspecialchars($storage['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="add-row">                
                        <label for="order_date">Ngày đặt:</label>
                        <input type="date" id="order_date" required>
                    </div>                    
                    
                    <div class="add-row">                
                        <label for="received_date">Ngày nhận:</label>
                        <input type="date" id="received_date">
                    </div>

                    <div class="add-row">                
                        <label for="expired_date">Ngày hết hạn:</label>
                        <input type="date" id="expired_date">
                    </div>

                    <button type="submit">Lưu</button>
                    <button type="button" onclick="closeModal('addShipmentForm')">Hủy</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";
</script>

    <script src="../js/shipments.js"></script>
    <link rel="stylesheet" href="../css/add.css">

</body>
</html>