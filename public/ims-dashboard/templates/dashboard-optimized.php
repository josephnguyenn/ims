<?php
session_start();
if (! isset($_SESSION['token'])) {
    header('Location: ../login.php');
    exit();
}
include '../define.php';
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// Get basic stats only - no heavy data loading
function getQuickStats()
{
    global $mysqli;

    $stats = [];

    // Quick count queries
    $stats['total_products'] = $mysqli->query('SELECT COUNT(*) as count FROM products')->fetch_assoc()['count'];
    $stats['total_orders'] = $mysqli->query('SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()')->fetch_assoc()['count'];
    $stats['total_customers'] = $mysqli->query('SELECT COUNT(*) as count FROM customers')->fetch_assoc()['count'];
    $stats['low_stock'] = $mysqli->query('SELECT COUNT(*) as count FROM products WHERE quantity < 10')->fetch_assoc()['count'];

    return $stats;
}

$stats = getQuickStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IMS Tappo Market</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/performance.js"></script>
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <style>
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .content-tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            border: none;
            background: none;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-button.active {
            border-bottom-color: #667eea;
            color: #667eea;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
            min-height: 400px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            flex-grow: 1;
        }
        
        .btn-search {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 3px;
        }
        
        .page-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Dashboard - Hệ Thống Quản Lý Kho</h1>
            <a href="../performance-test.html" target="_blank" style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; font-size: 14px;">
                📊 Performance Monitor
            </a>
        </div>
        
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <h3><?= number_format($stats['total_products']) ?></h3>
                <p>Tổng Sản Phẩm</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($stats['total_orders']) ?></h3>
                <p>Đơn Hàng Hôm Nay</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($stats['total_customers']) ?></h3>
                <p>Khách Hàng</p>
            </div>
            <div class="stat-card">
                <h3><?= number_format($stats['low_stock']) ?></h3>
                <p>Sắp Hết Hàng</p>
            </div>
        </div>
        
        <!-- Content Tabs -->
        <div class="content-tabs">
            <button class="tab-button active" onclick="switchTab('products')">Sản Phẩm</button>
            <button class="tab-button" onclick="switchTab('orders')">Đơn Hàng</button>
            <button class="tab-button" onclick="switchTab('customers')">Khách Hàng</button>
            <button class="tab-button" onclick="switchTab('reports')">Báo Cáo</button>
        </div>
        
        <!-- Products Tab -->
        <div id="products-tab" class="tab-content active">
            <div class="search-container">
                <input type="text" class="search-input" id="product-search" placeholder="Tìm kiếm sản phẩm...">
                <button class="btn-search" onclick="searchProducts()">Tìm Kiếm</button>
            </div>
            <div id="products-container">
                <!-- Products will be loaded here -->
            </div>
        </div>
        
        <!-- Orders Tab -->
        <div id="orders-tab" class="tab-content">
            <div class="search-container">
                <input type="text" class="search-input" id="order-search" placeholder="Tìm kiếm đơn hàng...">
                <button class="btn-search" onclick="searchOrders()">Tìm Kiếm</button>
            </div>
            <div id="orders-container">
                <!-- Orders will be loaded here -->
            </div>
        </div>
        
        <!-- Customers Tab -->
        <div id="customers-tab" class="tab-content">
            <div id="customers-container">
                <!-- Customers will be loaded here -->
            </div>
        </div>
        
        <!-- Reports Tab -->
        <div id="reports-tab" class="tab-content">
            <div id="reports-container">
                <h3>Báo Cáo Nhanh</h3>
                <p>Đang phát triển...</p>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        let currentTab = 'products';
        let currentPage = {};
        
        // Initialize page counters
        ['products', 'orders', 'customers'].forEach(tab => {
            currentPage[tab] = 1;
        });

        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            currentTab = tabName;
            
            // Load data if not already loaded
            loadTabData(tabName);
        }

        async function loadTabData(tabName) {
            const container = document.getElementById(tabName + '-container');
            
            // Check if data is already loaded
            if (container.innerHTML.trim() && !container.innerHTML.includes('loading-spinner')) {
                return;
            }
            
            performanceHelper.showLoading(tabName + '-container');
            
            try {
                let data;
                switch(tabName) {
                    case 'products':
                        data = await performanceHelper.fetchWithCache(`${BASE_URL}/api/products?per_page=20&page=${currentPage[tabName]}`);
                        renderProducts(data, tabName + '-container');
                        break;
                    case 'orders':
                        data = await performanceHelper.fetchWithCache(`${BASE_URL}/api/orders?per_page=15&page=${currentPage[tabName]}`);
                        renderOrders(data, tabName + '-container');
                        break;
                    case 'customers':
                        data = await performanceHelper.fetchWithCache(`${BASE_URL}/api/customers?per_page=20&page=${currentPage[tabName]}`);
                        renderCustomers(data, tabName + '-container');
                        break;
                }
            } catch (error) {
                console.error(`Error loading ${tabName}:`, error);
                container.innerHTML = `<p style="color: red;">Lỗi tải dữ liệu: ${error.message}</p>`;
            }
        }

        function renderProducts(data, containerId) {
            const container = document.getElementById(containerId);
            const products = data.data || data;
            
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Số Lượng</th>
                            <th>Giá</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            if (products && products.length > 0) {
                products.forEach(product => {
                    const status = product.quantity < 10 ? '<span style="color: red;">Sắp hết</span>' : '<span style="color: green;">Còn hàng</span>';
                    html += `
                        <tr>
                            <td>${product.sku || product.barcode || 'N/A'}</td>
                            <td>${product.name}</td>
                            <td>${product.quantity}</td>
                            <td>${new Intl.NumberFormat('vi-VN').format(product.price)} VNĐ</td>
                            <td>${status}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="5">Không có dữ liệu</td></tr>';
            }
            
            html += '</tbody></table>';
            
            // Add pagination if needed
            if (data.last_page > 1) {
                html += renderPagination(data, 'products');
            }
            
            container.innerHTML = html;
        }

        function renderOrders(data, containerId) {
            const container = document.getElementById(containerId);
            const orders = data.data || data;
            
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách Hàng</th>
                            <th>Tổng Tiền</th>
                            <th>Ngày Đặt</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            if (orders && orders.length > 0) {
                orders.forEach(order => {
                    const customerName = order.customer ? order.customer.name : 'Khách lẻ';
                    const date = new Date(order.created_at).toLocaleDateString('vi-VN');
                    html += `
                        <tr>
                            <td>#${order.id}</td>
                            <td>${customerName}</td>
                            <td>${new Intl.NumberFormat('vi-VN').format(order.total_amount)} VNĐ</td>
                            <td>${date}</td>
                            <td><span style="color: green;">Hoàn thành</span></td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="5">Không có dữ liệu</td></tr>';
            }
            
            html += '</tbody></table>';
            
            // Add pagination if needed
            if (data.last_page > 1) {
                html += renderPagination(data, 'orders');
            }
            
            container.innerHTML = html;
        }

        function renderCustomers(data, containerId) {
            const container = document.getElementById(containerId);
            const customers = data.data || data;
            
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tên Khách Hàng</th>
                            <th>Số Điện Thoại</th>
                            <th>Địa Chỉ</th>
                            <th>Ngày Tạo</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            if (customers && customers.length > 0) {
                customers.forEach(customer => {
                    const date = new Date(customer.created_at).toLocaleDateString('vi-VN');
                    html += `
                        <tr>
                            <td>${customer.name}</td>
                            <td>${customer.phone || 'N/A'}</td>
                            <td>${customer.address || 'N/A'}</td>
                            <td>${date}</td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="4">Không có dữ liệu</td></tr>';
            }
            
            html += '</tbody></table>';
            
            container.innerHTML = html;
        }

        function renderPagination(data, type) {
            if (!data.last_page || data.last_page <= 1) return '';
            
            let html = '<div class="pagination">';
            
            // Previous button
            if (data.current_page > 1) {
                html += `<button class="page-btn" onclick="changePage('${type}', ${data.current_page - 1})">‹ Trước</button>`;
            }
            
            // Page numbers
            const start = Math.max(1, data.current_page - 2);
            const end = Math.min(data.last_page, data.current_page + 2);
            
            for (let i = start; i <= end; i++) {
                const activeClass = i === data.current_page ? 'active' : '';
                html += `<button class="page-btn ${activeClass}" onclick="changePage('${type}', ${i})">${i}</button>`;
            }
            
            // Next button
            if (data.current_page < data.last_page) {
                html += `<button class="page-btn" onclick="changePage('${type}', ${data.current_page + 1})">Tiếp ›</button>`;
            }
            
            html += '</div>';
            return html;
        }

        async function changePage(type, page) {
            currentPage[type] = page;
            performanceHelper.clearCache(type); // Clear cache for this type
            await loadTabData(type);
        }

        // Search functions
        const searchProducts = performanceHelper.debounce(async function() {
            const searchTerm = document.getElementById('product-search').value;
            performanceHelper.clearCache('products');
            const data = await performanceHelper.fetchWithCache(`${BASE_URL}/api/products?per_page=20&search=${encodeURIComponent(searchTerm)}`);
            renderProducts(data, 'products-container');
        }, 500);

        const searchOrders = performanceHelper.debounce(async function() {
            const searchTerm = document.getElementById('order-search').value;
            performanceHelper.clearCache('orders');
            const data = await performanceHelper.fetchWithCache(`${BASE_URL}/api/orders?per_page=15&search=${encodeURIComponent(searchTerm)}`);
            renderOrders(data, 'orders-container');
        }, 500);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadTabData('products');
        });
    </script>
</body>
</html>