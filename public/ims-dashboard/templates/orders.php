<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
include "../define.php";

// Lấy dữ liệu Đơn hàng, Khách hàng và Nhà cung cấp giao hàng
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

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

$allOrders = fetchData(BASE_URL . '/api/orders');
$allOrders = array_reverse($allOrders); // ✅ Reverse the array to show latest first
$totalOrders = count($allOrders);
$totalPages = ceil($totalOrders / $perPage);

// Slice orders for current page
$start = ($page - 1) * $perPage;
$orders = array_slice($allOrders, $start, $perPage);
$customers = fetchData(BASE_URL . '/api/customers');
$deliverySuppliers = fetchData(BASE_URL . '/api/delivery-suppliers');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="main">
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">  
        <div class="main-content-header">
            <h1>Quản lý Đơn hàng</h1>
            <button class="add-button" onclick="openModal('addOrderModal')">Thêm Đơn hàng</button>
        </div>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Nhà cung cấp giao hàng</th>
                    <th>Tổng giá</th>
                    <th>Số tiền đã thanh toán</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="order-table">
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <?php
                        // If there's no customer object, show “Khách vãng lai”
                        $custName = isset($order['customer']['name'])
                                    ? $order['customer']['name']
                                    : 'Khách vãng lai';
                        ?>
                        <td><?= htmlspecialchars($custName) ?></td>
                        <td>
                        <?= htmlspecialchars(
                                $order['delivery_supplier']['name'] 
                                ?? '—'      // or “N/A” / “Khách vãng lai” / whatever makes sense
                            ) ?>
                        </td>
                        <td><?= round($order['total_price']) ?> Kč</td>
                        <td><?= htmlspecialchars($order['paid_amount']) ?> Kč</td>
                        <td>
                            <button onclick="window.location.href='order-products.php?order_id=<?= $order['id'] ?>'">Quản lý Sản phẩm</button>
                            <script>
                            </script>
                            <?php
                            $orderId = (int)$order['id'];
                            $supplierId = isset($order['delivery_supplier']['id']) ? (int)$order['delivery_supplier']['id'] : 0;
                            $paidAmount = isset($order['paid_amount']) ? (float)$order['paid_amount'] : 0;
                            $totalPrice = isset($order['total_price']) ? (float)$order['total_price'] : 0;
                            ?>
                            <button onclick="openEditOrderForm(<?= $orderId ?>, <?= $supplierId ?>, <?= $paidAmount ?>, <?= $totalPrice ?>)">Sửa</button>
                            <button onclick="deleteOrder(<?= $order['id'] ?>)">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">&laquo;</a>
            <?php endif; ?>

            <?php
            $range = 2; // Number of pages to show before/after current page

            for ($i = 1; $i <= $totalPages; $i++) {
                if (
                    $i == 1 ||
                    $i == $totalPages ||
                    ($i >= $page - $range && $i <= $page + $range)
                ) {
                    if ($i == $page) {
                        echo "<a class='active' href='?page=$i'>$i</a>";
                    } else {
                        echo "<a href='?page=$i'>$i</a>";
                    }
                } elseif (
                    $i == $page - $range - 1 ||
                    $i == $page + $range + 1
                ) {
                    echo "<span style='padding: 0 4px;'>...</span>";
                }
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">&raquo;</a>
            <?php endif; ?>
        </div>

        <!-- ✅ Thêm Đơn hàng Modal -->
        <div id="addOrderModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addOrderModal')">&times;</span>
                <h2>Thêm Đơn hàng</h2>
                <form id="order-form">

                <div class="add-row">
                    <label for="customer_id">Khách hàng:</label>
                    <select id="customer_id" required>
                        <option value="">Chọn Khách hàng</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= htmlspecialchars($customer['id']) ?>"><?= htmlspecialchars($customer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="add-row">
                    <label for="delivery_supplier_id">Nhà cung cấp giao hàng:</label>
                    <select id="delivery_supplier_id" required>
                        <option value="">Chọn Nhà cung cấp giao hàng</option>
                        <?php foreach ($deliverySuppliers as $supplier): ?>
                            <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                    <button type="submit">Lưu</button>
                </form>
            </div>
        </div>

        <!-- ✅ Sửa Đơn hàng Modal -->
        <div id="editOrderModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editOrderModal')">&times;</span>
                <h2>Sửa Đơn hàng</h2>
                <form id="edit-order-form">
                    <div class="add-row">
                        <input type="hidden" id="edit_order_id">
                        <label for="edit_delivery_supplier_id">Nhà cung cấp giao hàng:</label>
                        <select id="edit_delivery_supplier_id" required>
                            <option value="">Chọn Nhà cung cấp giao hàng</option>
                            <?php foreach ($deliverySuppliers as $supplier): ?>
                                <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="add-row">
                        <label for="edit_paid_amount">Số tiền đã thanh toán:</label>
                        <input type="number" id="edit_paid_amount" placeholder="Nhập số tiền đã thanh toán" step="0.01" required>
                     </div>               


                    <button type="submit">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
    <link rel="stylesheet" href="../css/add.css">
    <script src="../js/orders.js"></script>
    <style>
    .pagination {
        margin-top: 20px;
        display: flex;
        justify-content: flex-end;
    }
    .pagination a {
        padding: 6px 12px;
        margin: 0 2px;
        border: 1px solid #ccc;
        text-decoration: none;
        color: #333;
    }
    .pagination a.active {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }
    .pagination a:hover:not(.active) {
        background-color: #ddd;
    }
        </style>

</body>
</html>