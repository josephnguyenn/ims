<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

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

$allProducts = fetchData("http://localhost/ims/public/api/products");
$shipments = fetchData("http://localhost/ims/public/api/shipments");
$storages = fetchData("http://localhost/ims/public/api/storages");
$shipmentSuppliers = fetchData("http://localhost/ims/public/api/shipment-suppliers");

$shipment_id = $_GET['shipment_id'] ?? null;
$selectedShipment = [];
$storageName = 'Không xác định';
$supplierName = 'Không xác định';

if ($shipment_id) {
    $products = array_filter($allProducts, function ($product) use ($shipment_id) {
        return $product['shipment_id'] == $shipment_id;
    });

    foreach ($shipments as $shipment) {
        if ($shipment['id'] == $shipment_id) {
            $selectedShipment = $shipment;
            // Tìm tên kho
            foreach ($storages as $storage) {
                if ($storage['id'] == $shipment['storage_id']) {
                    $storageName = $storage['name'];
                    break;
                }
            }
            // Tìm tên nhà cung cấp
            foreach ($shipmentSuppliers as $supplier) {
                if ($supplier['id'] == $shipment['shipment_supplier_id']) {
                    $supplierName = $supplier['name'];
                    break;
                }
            }
            break;
        }
    }
} else {
    $products = $allProducts;
}

$product_code_filter = $_GET['product_code_filter'] ?? null;

if ($shipment_id) {
    $products = array_filter($allProducts, function ($product) use ($shipment_id) {
        return $product['shipment_id'] == $shipment_id;
    });
} else {
    $products = $allProducts;
}

if (!empty($product_code_filter)) {
    $products = array_filter($products, function ($product) use ($product_code_filter) {
        return strpos($product['code'], $product_code_filter) !== false;
    });
}

// Cài đặt phân trang
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalProducts = count($products);
$totalPages = ceil($totalProducts / $perPage);

// Cắt mảng sản phẩm cho trang hiện tại
$start = ($page - 1) * $perPage;
$paginatedProducts = array_slice($products, $start, $perPage);

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shipment-product.css">

    <meta name="csrf-token" content="<?= $csrfToken ?>">

</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="main">
        <?php include "../includes/sidebar.php"; ?>

        <div class="main-content">
            <div class="main-content-header">
                <h1>Quản lý sản phẩm</h1>
            <button class="add-button" onclick="openModal('addProductForm')">Thêm sản phẩm</button>
        </div>
        <form method="get" style="margin-bottom: 20px; width: 30%;">
            <!-- Trường ẩn để giữ shipment_id trong URL -->
            <?php if (!empty($shipment_id)): ?>
                <input type="hidden" name="shipment_id" value="<?= htmlspecialchars($shipment_id) ?>">
            <?php endif; ?>

            <!-- Trường nhập để lọc mã sản phẩm -->
            <input type="text" name="product_code_filter" placeholder="Lọc theo mã sản phẩm" value="<?= htmlspecialchars($_GET['product_code_filter'] ?? '') ?>">
            <button type="submit">Lọc</button>
            <a href="?<?= !empty($shipment_id) ? "shipment_id=$shipment_id" : "" ?>" class="reset-button">Đặt lại bộ lọc</a>
        </form>
            

        <div class="shipment-meta">
            <?php if ($shipment_id && !empty($selectedShipment)): ?>
                <h3>Sản phẩm trong lô hàng <?= htmlspecialchars($shipment_id) ?></h3>
                <p>Kho: <?= htmlspecialchars($storageName) ?></p>
                <p>Nhà cung cấp: <?= htmlspecialchars($supplierName) ?></p>
                <p>Ngày nhận: <?= htmlspecialchars($selectedShipment['received_date']) ?></p>
                <p>Ngày hết hạn: <?= htmlspecialchars($selectedShipment['expired_date']) ?></p>
                <p>Tổng chi phí: $<?= htmlspecialchars($selectedShipment['cost']) ?></p>
            <?php endif; ?>
        </div>




        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mã</th>
                    <th>Số lượng gốc</th>
                    <th>Số lượng thực tế</th>
                    <th>Giá</th>
                    <th>Chi phí</th>
                    <th>Tổng chi phí</th>
                    <th>Lô hàng</th>
                    <th>Ngày hết hạn</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="product-table">
                <?php if (!empty($paginatedProducts)): ?>
                    <?php foreach ($paginatedProducts as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['code']) ?></td>
                            <td><?= htmlspecialchars($product['original_quantity']) ?></td>
                            <td><?= htmlspecialchars($product['actual_quantity']) ?></td>
                            <td><?= htmlspecialchars($product['price']) ?>Kč</td>
                            <td><?= htmlspecialchars($product['cost']) ?>Kč</td>
                            <td><?= htmlspecialchars($product['total_cost']) ?>Kč</td>
                            <td>Lô hàng <?= htmlspecialchars($product['shipment_id']) ?></td>
                            <td><?= htmlspecialchars($product['expired_date'] ?? 'Không có') ?></td>
                            <td>
                            <button onclick="openEditModal(<?= $product['id'] ?>)">Sửa</button>
                            <button onclick="deleteProduct(<?= $product['id'] ?>)">Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">Không có sản phẩm nào trong lô hàng này.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $shipment_id ? '&shipment_id=' . $shipment_id : '' ?><?= $product_code_filter ? '&product_code_filter=' . urlencode($product_code_filter) : '' ?>"
                    class="<?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>

        
    <div id="addProductForm" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Thêm sản phẩm</h2>
                <form id="product-form" class="form-container">
                    <div class="add-row">
                        <label for="product_name">Tên sản phẩm:</label>
                        <input type="text" id="product_name" placeholder="Nhập tên sản phẩm" required>
                    </div>
                    
                    <div class="add-row">
                        <label for="product_code">Mã sản phẩm:</label>
                        <input type="text" id="product_code" placeholder="Nhập mã sản phẩm" required oninput="suggestProductCode()">
                        <div id="suggestions" class="suggestion-box"></div>
                    </div>

                    <div class="add-row">
                        <label for="original_quantity">Số lượng gốc:</label>
                        <input type="number" id="original_quantity" placeholder="Nhập số lượng gốc" required>
                    </div>

                    <div class="add-row">
                        <label for="price">Giá:</label>
                        <input type="number" id="price" placeholder="Nhập giá" required>
                    </div>

                    <div class="add-row">
                        <label for="cost">Chi phí:</label>
                        <input type="number" id="cost" placeholder="Nhập chi phí" required>
                    </div>

                    <div class="add-row">
                            <label for="tax">Thuế (%):</label>
                            <input type="number" id="tax" placeholder="Nhập thuế" step="0.01" value="0" required>
                    </div>

                    <div class="add-row">
                        <label for="expired_date">Ngày hết hạn:</label>
                        <input type="date" id="expired_date">
                    </div>


                    <div class="add-row">
                        <label for="category">Danh mục:</label>
                        <input type="text" id="category" placeholder="Nhập danh mục" required>
                    </div>

                    <div class="add-row">
                        <label for="shipment_id">Lô hàng:</label>
                        <select id="shipment_id" required>
                            <option value="">Chọn lô hàng</option>
                            <?php foreach ($shipments as $shipment): ?>
                                <option value="<?= htmlspecialchars($shipment['id']) ?>">Lô hàng #<?= htmlspecialchars($shipment['id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button-save">Lưu</button>
                        <button type="button" onclick="closeModal('addProductForm')" class="button-cancel">Hủy</button>
                    </div>
                </form>
        </div>
    </div>
        <div id="editProductForm" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Sửa sản phẩm</h2>
        <form id="edit-product-form" class="form-container">
        <input type="hidden" id="edit_product_id">

        <div class="add-row">
            <label for="edit_product_name">Tên:</label>
            <input type="text" id="edit_product_name" required>
        </div>

        <div class="add-row">
            <label for="edit_product_code">Mã:</label>
            <input type="text" id="edit_product_code" required>
        </div>

        <div class="add-row">
            <label for="edit_original_quantity">Số lượng gốc:</label>
            <input type="number" id="edit_original_quantity" required>
        </div>

        <div class="add-row">
            <label for="edit_price">Giá:</label>
            <input type="number" id="edit_price" required>
        </div>

        <div class="add-row">
            <label for="edit_cost">Chi phí:</label>
            <input type="number" id="edit_cost" required>
        </div>

        <div class="add-row">
            <label for="edit_tax">Thuế (%):</label>
            <input type="number" id="edit_tax" step="0.01">
        </div>

        <div class="add-row">
            <label for="expired_date">Ngày hết hạn:</label>
            <input type="date" id="edit_expired_date"> <!-- ✅ Fix -->
        </div>


        <div class="add-row">
            <label for="edit_category">Danh mục:</label>
            <input type="text" id="edit_category" required>
        </div>

        <div class="add-row">
            <label for="edit_shipment_id">Lô hàng:</label>
            <select id="edit_shipment_id" required>
            <option value="">Chọn lô hàng</option>
            <?php foreach ($shipments as $shipment): ?>
                <option value="<?= $shipment['id'] ?>">Lô hàng #<?= $shipment['id'] ?></option>
            <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="button-save">Cập nhật</button>
            <button type="button" onclick="closeModal('editProductForm')" class="button-cancel">Hủy</button>
        </div>
        </form>
    </div>
    </div>

    </div>
</div>
    <script src="../js/products.js"></script>
    <link rel="stylesheet" href="../css/add.css">
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