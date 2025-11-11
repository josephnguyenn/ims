<?php
// Performance monitoring script
header('Content-Type: application/json');

include '../define.php';

function getQueryPerformance() {
    global $mysqli;
    
    $start = microtime(true);
    
    // Test query performance on products with new indexes
    $productQuery = "SELECT * FROM products WHERE name LIKE '%test%' OR code LIKE '%test%' ORDER BY actual_quantity ASC LIMIT 20";
    $productResult = $mysqli->query($productQuery);
    $productTime = microtime(true) - $start;
    
    $start = microtime(true);
    
    // Test query performance on orders with date index
    $orderQuery = "SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY o.created_at DESC LIMIT 20";
    $orderResult = $mysqli->query($orderQuery);
    $orderTime = microtime(true) - $start;
    
    $start = microtime(true);
    
    // Test customer search performance
    $customerQuery = "SELECT * FROM customers WHERE name LIKE '%test%' OR phone LIKE '%test%' LIMIT 20";
    $customerResult = $mysqli->query($customerQuery);
    $customerTime = microtime(true) - $start;
    
    return [
        'product_search_time' => round($productTime * 1000, 2) . ' ms',
        'order_date_query_time' => round($orderTime * 1000, 2) . ' ms',
        'customer_search_time' => round($customerTime * 1000, 2) . ' ms',
        'total_products' => $productResult->num_rows,
        'total_orders' => $orderResult->num_rows,
        'total_customers' => $customerResult->num_rows,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function getDatabaseStats() {
    global $mysqli;
    
    $stats = [];
    
    // Get table sizes
    $query = "SELECT 
        table_name,
        table_rows,
        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = 'tappomarket_ims' 
        AND table_type = 'BASE TABLE'
        ORDER BY size_mb DESC";
    
    $result = $mysqli->query($query);
    while ($row = $result->fetch_assoc()) {
        $stats['table_sizes'][] = $row;
    }
    
    // Get index information
    $indexQuery = "SELECT 
        table_name,
        index_name,
        column_name,
        cardinality
        FROM information_schema.statistics 
        WHERE table_schema = 'tappomarket_ims' 
        AND index_name != 'PRIMARY'
        ORDER BY table_name, index_name";
    
    $indexResult = $mysqli->query($indexQuery);
    while ($row = $indexResult->fetch_assoc()) {
        $stats['indexes'][] = $row;
    }
    
    return $stats;
}

$action = $_GET['action'] ?? 'performance';

switch ($action) {
    case 'performance':
        echo json_encode(getQueryPerformance());
        break;
    case 'stats':
        echo json_encode(getDatabaseStats());
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>