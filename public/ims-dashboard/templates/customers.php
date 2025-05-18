<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
include "../define.php";

// Lấy dữ liệu Khách hàng
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

$customers = fetchData(BASE_URL . '/api/customers');
// Tạo mã CSRF
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách hàng</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="main">
    <?php include "../includes/sidebar.php"; ?>
    <div class="main-content">
        <div class="main-content-header">
            <h1>Quản lý Khách hàng</h1>
            <button class="add-button" onclick="document.getElementById('addCustomerForm').style.display='flex'">Thêm Khách hàng</button>
        </div>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Mã số thuế</th>
                    <th>Tổng đơn hàng</th>
                    <th>Tổng nợ</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="customer-table">
                <tr><td colspan="9">Đang tải...</td></tr>
            </tbody>
        </table>

        <!-- ✅ Form Thêm Khách hàng (Ẩn) -->
        <div id="addCustomerForm" class="modal" style="display: none;">
        <span class="close" onclick="closeModal('addCustomerForm')">&times;</span>
            <div class="modal-content">
                <h2>Thêm Khách hàng</h2>
                <form id="customer-form" onsubmit="return addCustomer();">
                <div class="add-row">
                        <label for="customer_name">Tên:</label>
                        <input type="text" id="customer_name" placeholder="Tên Khách hàng" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_email">Email:</label>
                        <input type="email" id="customer_email" placeholder="Email" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_phone">Số điện thoại:</label>
                        <input type="text" id="customer_phone" placeholder="Số điện thoại" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_address">Địa chỉ:</label>
                        <input type="text" id="customer_address" placeholder="Địa chỉ" required>
                    </div>

                    <div class="add-row">
                        <label for="customer_city">Thành phố:</label>
                        <input type="text" id="customer_city" placeholder="Thành phố" required>
                    </div>
                    
                    <div class="add-row">
                        <label for="customer_postal_code">Mã bưu chính:</label>
                        <input type="number" id="customer_postal_code" placeholder="Mã bưu chính" required>
                    </div>

                    <div class="add-row">
                        <label for="customer_vat_code">Mã số GTGT:</label>
                        <input type="text" id="customer_vat_code" placeholder="Mã số GTGT">
                    </div>

                    <div class="add-row">
                        <label for="customer_tax_code">Mã số thuế:</label>
                        <input type="number" id="customer_tax_code" placeholder="Mã số thuế">
                    </div>

                    <div class="form-buttons">
                        <button type="submit">Lưu</button>
                        <button type="button" onclick="closeModal('addCustomerForm')">Hủy</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ✅ Form Sửa Khách hàng (Ẩn) -->
        <div id="editCustomerForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editCustomerForm')">&times;</span>
            <h2>Sửa Khách hàng</h2>
            <form id="edit-customer-form">
            <div class="add-row">
                <input type="hidden" id="edit_customer_id">
                <label for="edit_customer_name">Tên:</label>
                <input type="text" id="edit_customer_name" required>
            </div>

            <div class="add-row">
                <label for="edit_customer_email">Email:</label>
                <input type="email" id="edit_customer_email" required>
            </div>
            <div class="add-row">
                <label for="edit_customer_phone">Số điện thoại:</label>
                <input type="text" id="edit_customer_phone" required>
            </div>
            <div class="add-row">
                <label for="edit_customer_city">Thành phố:</label>
                <input type="text" id="edit_customer_city" required>
            </div>
            <div class="add-row">
                <label for="edit_customer_postal_code">Mã bưu chính:</label>
                <input type="number" id="edit_customer_postal_code" required>
            </div>
            <div class="add-row">
                <label for="edit_customer_address">Địa chỉ:</label>
                <input type="text" id="edit_customer_address" required>
            </div>
            <div class="add-row">
                <label for="edit_customer_vat_code">Mã số GTGT:</label>
                <input type="text" id="edit_customer_vat_code">
            </div>

            <div class="add-row">
                <label for="edit_customer_tax_code">Mã số thuế:</label>
                <input type="number" id="edit_customer_tax_code">
            </div>

                <div class="form-buttons">
                    <button type="button" onclick="updateCustomer()">Cập nhật</button>
                    <button type="button" onclick="closeModal('editCustomerForm')">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    </div>
</div>
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
    <script src="../js/customers.js"></script>
    <link rel="stylesheet" href="../css/add.css">
</body>
</html>