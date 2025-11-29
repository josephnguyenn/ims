<?php
session_start();
if (! isset($_SESSION['token'])) {
    header('Location: ../login.php');
    exit();
}
include '../define.php';

function fetchData($apiUrl, $timeout = 10)
{
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer '.$_SESSION['token'],
    ]);
    $response = curl_exec($ch);

    if (curl_error($ch)) {
        curl_close($ch);

        return ['error' => 'API request failed: '.curl_error($ch)];
    }

    curl_close($ch);

    return json_decode($response, true);
}

// Get all products (for now, to maintain compatibility)
$productsResponse = fetchData(BASE_URL.'/api/products?paginate=false');
$allProducts = isset($productsResponse['data']) ? $productsResponse['data'] : (is_array($productsResponse) ? $productsResponse : []);

// For dropdowns, get smaller datasets
$shipments = fetchData(BASE_URL.'/api/shipments');
$storages = fetchData(BASE_URL.'/api/storages');
$shipmentSuppliers = fetchData(BASE_URL.'/api/shipment-supplier');

$shipment_id = $_GET['shipment_id'] ?? null;
$selectedShipment = [];
$storageName = 'Không xác định';
$supplierName = 'Không xác định';

if ($shipment_id) {
    $products = array_filter($allProducts ?? [], function ($product) use ($shipment_id) {
        return $product['shipment_id'] == $shipment_id;
    });

    if (! empty($shipments) && is_array($shipments)) {
        foreach ($shipments as $shipment) {
            if ((string) $shipment['id'] === (string) $shipment_id) {
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

if (! empty($product_code_filter)) {
    $products = array_filter($products, function ($product) use ($product_code_filter) {
        return strpos($product['code'], $product_code_filter) !== false;
    });
}

// Cài đặt phân trang
$perPage = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
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
    <link rel="stylesheet" href="../css/enhancements.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <meta name="csrf-token" content="<?= $csrfToken ?>">

</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="main">
        <?php include '../includes/sidebar.php'; ?>

        <div class="main-content">
            <div class="main-content-header">
                <h1>Quản lý sản phẩm</h1>
            <button class="add-button" onclick="openModal('addProductForm')">Thêm sản phẩm</button>
        </div>
        <form method="get" style="margin-bottom: 20px; width: 30%;">
            <!-- Trường ẩn để giữ shipment_id trong URL -->
            <?php if (! empty($shipment_id)) { ?>
                <input type="hidden" name="shipment_id" value="<?= htmlspecialchars($shipment_id) ?>">
            <?php } ?>

            <!-- Trường nhập để lọc mã sản phẩm -->
            <input type="text" name="product_code_filter" placeholder="Lọc theo mã sản phẩm" value="<?= htmlspecialchars($_GET['product_code_filter'] ?? '') ?>">
            <button type="submit">Lọc</button>
            <a href="?<?= ! empty($shipment_id) ? "shipment_id=$shipment_id" : '' ?>" class="reset-button">Đặt lại bộ lọc</a>
        </form>
            

        <div class="shipment-meta">
            <?php if ($shipment_id && ! empty($selectedShipment)) { ?>
                <h3>Sản phẩm trong lô hàng <?= htmlspecialchars($shipment_id) ?></h3>
                <p>Kho: <?= htmlspecialchars($storageName) ?></p>
                <p>Nhà cung cấp: <?= htmlspecialchars($supplierName) ?></p>
                <p>Ngày nhận: <?= htmlspecialchars($selectedShipment['received_date']) ?></p>
                <p>Ngày hết hạn: <?= htmlspecialchars($selectedShipment['expired_date']) ?></p>
                <p>Tổng chi phí: <?= htmlspecialchars($selectedShipment['cost']) ?> CZK</p>
            <?php } ?>
        </div>




        <table border="1" id="productsDataTable" class="display" style="width:100%">
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
                <!-- DataTables will populate this via AJAX -->
            </tbody>
        </table>

        <!-- Pagination removed - handled by DataTables -->


        
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
                            <?php foreach ($shipments as $shipment) { ?>
                                <option value="<?= htmlspecialchars($shipment['id']) ?>" <?= $shipment_id == $shipment['id'] ? 'selected' : '' ?>>Lô hàng #<?= htmlspecialchars($shipment['id']) ?></option>
                            <?php } ?>
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
            <?php foreach ($shipments as $shipment) { ?>
                <option value="<?= $shipment['id'] ?>">Lô hàng #<?= $shipment['id'] ?></option>
            <?php } ?>
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
    
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- DataTables Core -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    
    <script src="../js/products.js"></script>
    <script src="../js/notification-manager.js"></script>
    <script src="../js/theme-toggle.js"></script>
    <script src="../js/keyboard-shortcuts.js"></script>
    <script src="../js/autocomplete.js"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            const token = localStorage.getItem("token");
            const shipmentFilter = "<?= $shipment_id ?? '' ?>";
            
            window.productsTable = $('#productsDataTable').DataTable({
                ajax: {
                    url: BASE_URL + '/api/products?paginate=false',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    dataSrc: function(json) {
                        // Filter by shipment if needed
                        if (shipmentFilter) {
                            return json.data ? json.data.filter(p => p.shipment_id == shipmentFilter) : json.filter(p => p.shipment_id == shipmentFilter);
                        }
                        return json.data || json;
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'code' },
                    { data: 'original_quantity' },
                    { 
                        data: 'actual_quantity',
                        render: function(data, type, row) {
                            let className = '';
                            if (data === 0) className = 'out-of-stock';
                            else if (data <= 10) className = 'low-stock';
                            return '<span class="' + className + '">' + data + '</span>';
                        }
                    },
                    { 
                        data: 'price',
                        render: (data) => parseFloat(data).toLocaleString() + ' Kč'
                    },
                    { 
                        data: 'cost',
                        render: (data) => parseFloat(data).toLocaleString() + ' Kč'
                    },
                    { 
                        data: 'total_cost',
                        render: (data) => parseFloat(data).toLocaleString() + ' Kč'
                    },
                    { 
                        data: 'shipment_id',
                        render: (data) => 'Lô hàng ' + data
                    },
                    { 
                        data: 'expired_date',
                        render: (data) => data || 'Không có'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return '<button onclick="openEditModal(' + row.id + ')">Sửa</button> ' +
                                   '<button onclick="deleteProduct(' + row.id + ')">Xóa</button>';
                        }
                    }
                ],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: '📋 Copy'
                    },
                    {
                        extend: 'excel',
                        text: '📊 Excel',
                        title: 'Danh sách sản phẩm'
                    },
                    {
                        extend: 'pdf',
                        text: '📄 PDF',
                        title: 'Danh sách sản phẩm'
                    },
                    {
                        extend: 'print',
                        text: '🖨️ Print'
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    search: "Tìm kiếm:",
                    lengthMenu: "Hiển thị _MENU_ dòng",
                    info: "Hiển thị _START_ đến _END_ của _TOTAL_ sản phẩm",
                    infoEmpty: "Không có dữ liệu",
                    infoFiltered: "(lọc từ _MAX_ sản phẩm)",
                    paginate: {
                        first: "Đầu",
                        last: "Cuối",
                        next: "Tiếp",
                        previous: "Trước"
                    },
                    emptyTable: "Không có sản phẩm nào"
                },
                order: [[0, 'desc']] // Sort by ID descending
            });
            
            // Reload table after operations
            window.reloadProductsTable = function() {
                window.productsTable.ajax.reload(null, false);
            };
        });
    </script>
    
    <link rel="stylesheet" href="../css/add.css">
    <style>
        /* DataTables custom styling */
        .dataTables_wrapper .dt-buttons {
            margin-bottom: 10px;
        }
        .dt-button {
            background: #1a4ba8 !important;
            color: white !important;
            border: none !important;
            padding: 8px 15px !important;
            border-radius: 4px !important;
            margin-right: 5px !important;
            cursor: pointer !important;
        }
        .dt-button:hover {
            background: #153d8a !important;
        }
        .low-stock {
            color: #ff9800;
            font-weight: bold;
        }
        .out-of-stock {
            color: #f44336;
            font-weight: bold;
        }
        /* Remove old pagination styles */
        .pagination {
            display: none;
        }
    </style>

</body>
</html>