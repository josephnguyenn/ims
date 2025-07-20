<?php
if (!defined('BASE_URL')) {
    $baseUrl = getenv('APP_URL') ?: 'http://localhost//public/ims-dashboard';
    define('BASE_URL', $baseUrl . '/ims-dashboard');
}
?>
<div class="sidebar">
    <h2>Bảng Điều Khiển</h2>
    <ul>
        <li><a href="dashboard.php">Bảng Điều Khiển</a></li>

        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager')): ?>
            <li><a href="storage.php">Kho Hàng</a></li>
            <li><a href="shipment-suppliers.php">Nhà Cung Cấp Lô Hàng</a></li>
            <li><a href="revenue.php">Doanh Thu</a></li>
            <li><a href="delivery-suppliers.php">Nhà Cung Cấp Giao Hàng</a></li>
            <li><a href="category.php">Danh Mục Sản Phẩm</a></li>

        <?php endif; ?>

        <li><a href="shipments.php">Lô Hàng</a></li>
        <li><a href="products.php">Sản Phẩm</a></li>
        <li><a href="customers.php">Khách Hàng</a></li>
        <li><a href="orders.php">Đơn Hàng</a></li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="users.php">Người Dùng</a></li>
        <?php endif; ?>

        <li><a href="../logout.php">Đăng Xuất</a></li>
    </ul>
</div>