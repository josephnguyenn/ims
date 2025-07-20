<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
include "../define.php";

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

$allProducts = fetchData(BASE_URL .'/api/products');
$shipments = fetchData(BASE_URL .'/api/shipments');
$storages = fetchData(BASE_URL .'/api/storages');
$shipmentSuppliers = fetchData(BASE_URL .'/api/shipment-supplier');

$shipment_id = $_GET['shipment_id'] ?? null;
$selectedShipment = [];
$storageName = 'Không xác định';
$supplierName = 'Không xác định';

if ($shipment_id) {
    $products = array_filter($allProducts ?? [], function ($product) use ($shipment_id) {
        return $product['shipment_id'] == $shipment_id;
    });

    if (!empty($shipments) && is_array($shipments)) {
        foreach ($shipments as $shipment) {
            if ((string)$shipment['id'] === (string)$shipment_id) {
                $selectedShipment = $shipment;

                // ✅ Dùng dữ liệu lồng trong API response
                $storageName = $shipment['storage']['name'] ?? 'Không xác định';
                $supplierName = $shipment['supplier']['name'] ?? 'Không xác định';

                break;
            }
        }
    } else {
        echo "<p style='color: red'>⚠ Không thể tải dữ liệu lô hàng hoặc dữ liệu không hợp lệ.</p>";
    }
} else {
    $products = $allProducts ?? [];
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
$products = array_reverse($products); // <-- Add this line before slicing
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
                <p>Tổng chi phí: <?= htmlspecialchars($selectedShipment['cost']) ?> CZK</p>
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
                    <th>Đơn vị</th>
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
                            <td><?= $product['is_weighted'] ? 'kg' : 'sản phẩm' ?></td>
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
        <?php
        $range = 2; // Number of pages to show before and after current page

        if ($totalPages > 1) {
            // Previous Button
            if ($page > 1) {
                $prevPage = $page - 1;
                echo '<a href="?page=' . $prevPage . 
                    ($shipment_id ? '&shipment_id=' . $shipment_id : '') . 
                    ($product_code_filter ? '&product_code_filter=' . urlencode($product_code_filter) : '') . '">«</a>';
            }

            // First Page
            if ($page > $range + 1) {
                echo '<a href="?page=1' . 
                    ($shipment_id ? '&shipment_id=' . $shipment_id : '') . 
                    ($product_code_filter ? '&product_code_filter=' . urlencode($product_code_filter) : '') . '">1</a>';
                if ($page > $range + 2) {
                    echo '<span>...</span>';
                }
            }

            // Page Range
            for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++) {
                echo '<a href="?page=' . $i . 
                    ($shipment_id ? '&shipment_id=' . $shipment_id : '') . 
                    ($product_code_filter ? '&product_code_filter=' . urlencode($product_code_filter) : '') . '"' .
                    ($i === $page ? ' class="active"' : '') . '>' . $i . '</a>';
            }

            // Last Page
            if ($page < $totalPages - $range) {
                if ($page < $totalPages - $range - 1) {
                    echo '<span>...</span>';
                }
                echo '<a href="?page=' . $totalPages . 
                    ($shipment_id ? '&shipment_id=' . $shipment_id : '') . 
                    ($product_code_filter ? '&product_code_filter=' . urlencode($product_code_filter) : '') . '">' . $totalPages . '</a>';
            }

            // Next Button
            if ($page < $totalPages) {
                $nextPage = $page + 1;
                echo '<a href="?page=' . $nextPage . 
                    ($shipment_id ? '&shipment_id=' . $shipment_id : '') . 
                    ($product_code_filter ? '&product_code_filter=' . urlencode($product_code_filter) : '') . '">»</a>';
            }
        }
        ?>
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
                        <div class="quantity-input-group">
                            <label for="is_weighted">Sản phẩm cân theo kg:</label>
                            <input type="checkbox" id="is_weighted">
                        </div>
                    </div>

                    <div class="add-row">
                        <label for="original_quantity">Số lượng:</label>
                        <div class="quantity-input-group">
                            <input type="number" id="original_quantity" placeholder="Nhập số lượng" required>
                            <span id="quantity_unit" class="unit-label">sản phẩm</span>
                        </div>
                    </div>

                    <div class="add-row">
                        <label for="price">Giá:</label>
                        <input type="number" id="price" placeholder="Nhập giá" step="0.01" required>
                    </div>

                    <div class="add-row">
                        <label for="cost">Chi phí:</label>
                        <input type="number" id="cost" placeholder="Nhập chi phí" step="0.01" required>
                    </div>

                    <div class="add-row">
                            <label for="tax">Thuế (%):</label>
                            <input type="number" id="tax" placeholder="Nhập thuế" step="0.01" value="0" required>
                    </div>

                    <div class="add-row">
                    <label for="expiry_mode">Ngày hết hạn:</label>
                    <select id="expiry_mode" onchange="handleExpiryModeChange(this.value)">
                        <option value="inherit">Theo lô hàng</option>
                        <option value="custom">Tự chọn ngày hết hạn</option>
                        <option value="none">Không có ngày hết hạn</option>
                    </select>

                    <input type="date" id="expired_date" style="display:none;">
                    </div>


                    <div class="add-row">
                    <label for="category_id">Danh mục:</label>
                    <select id="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <!-- options will be injected here -->
                    </select>
                    </div>

                    <div class="add-row">
                        <label for="shipment_id">Lô hàng:</label>
                        <select id="shipment_id" required>
                            <option value="">Chọn lô hàng</option>
                            <?php foreach ($shipments as $shipment): ?>
                                <option value="<?= htmlspecialchars($shipment['id']) ?>" <?= $shipment_id == $shipment['id'] ? 'selected' : '' ?>>Lô hàng #<?= htmlspecialchars($shipment['id']) ?></option>
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
            <div class="quantity-input-group">
                <label for="edit_is_weighted">Sản phẩm cân theo kg:</label>
                <input type="checkbox" id="edit_is_weighted">
            </div>
        </div>

        <div class="add-row">
            <label for="edit_original_quantity">Số lượng gốc:</label>
            <div class="quantity-input-group">
                <input type="number" id="edit_original_quantity" required>
                <span id="edit_quantity_unit" class="unit-label">sản phẩm</span>
            </div>
        </div>

        <div class="add-row">
            <label for="edit_price">Giá:</label>
            <input type="number" id="edit_price" required step="0.01">
        </div>

        <div class="add-row">
            <label for="edit_cost">Chi phí:</label>
            <input type="number" id="edit_cost" required step="0.01">
        </div>

        <div class="add-row">
            <label for="edit_tax">Thuế (%):</label>
            <input type="number" id="edit_tax" step="0.01">
        </div>

        <div class="add-row">
            <label for="edit_expiry_mode">Ngày hết hạn:</label>
            <select id="edit_expiry_mode" onchange="handleExpiryModeChangeEdit(this.value)">
                <option value="inherit">Theo lô hàng</option>
                <option value="custom">Tự chọn ngày hết hạn</option>
                <option value="none">Không có ngày hết hạn</option>
            </select>

            <input type="date" id="edit_expired_date" style="display:none;">
        </div>


        <div class="add-row">
        <label for="edit_category_id">Danh mục:</label>
        <select id="edit_category_id" required>
            <option value="">-- Chọn danh mục --</option>
            <!-- options will be injected here -->
        </select>
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
    <script>
        const BASE_URL = "<?= BASE_URL ?>";
    </script>
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