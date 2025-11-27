<?php
/**
 * Reset Database and Add Sample Data
 * This script will:
 * 1. Delete all data EXCEPT users
 * 2. Add sample data for testing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔄 Starting database reset and seeding...\n\n";

// Disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// 1. Clear all tables EXCEPT users
echo "🗑️  Clearing existing data (keeping users)...\n";
DB::table('order_products')->truncate();
DB::table('orders')->truncate();
DB::table('customers')->truncate();
DB::table('products')->truncate();
DB::table('product_categories')->truncate();
DB::table('shipments')->truncate();
DB::table('shipment_suppliers')->truncate();
DB::table('delivery_suppliers')->truncate();
DB::table('storages')->truncate();

echo "✅ Data cleared!\n\n";

// 2. Add sample data
echo "📦 Adding sample data...\n\n";

// Storage
echo "Adding storage...\n";
$storageId = DB::table('storages')->insertGetId([
    'name' => 'Main Warehouse',
    'location' => 'Prague, Czech Republic',
    'capacity' => 10000,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Shipment Suppliers
echo "Adding shipment suppliers...\n";
$shipmentSuppliers = [
    ['name' => 'ABC Wholesale Ltd.', 'contact_person' => 'Jan Novák', 'phone' => '+420 123 456 789', 'email' => 'jan@abcwholesale.cz', 'address' => 'Prague 1, Czech Republic'],
    ['name' => 'Global Imports s.r.o.', 'contact_person' => 'Petra Svobodová', 'phone' => '+420 987 654 321', 'email' => 'petra@globalimports.cz', 'address' => 'Brno, Czech Republic'],
    ['name' => 'EuroTrade Partners', 'contact_person' => 'Martin Dvořák', 'phone' => '+420 555 123 456', 'email' => 'martin@eurotrade.eu', 'address' => 'Ostrava, Czech Republic'],
];

$shipmentSupplierIds = [];
foreach ($shipmentSuppliers as $supplier) {
    $supplier['created_at'] = now();
    $supplier['updated_at'] = now();
    $shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId($supplier);
}

// Delivery Suppliers
echo "Adding delivery suppliers...\n";
$deliverySuppliers = [
    ['name' => 'Fast Delivery Express', 'contact_person' => 'Lukáš Procházka', 'phone' => '+420 111 222 333', 'email' => 'lukas@fastdelivery.cz'],
    ['name' => 'Quick Transport s.r.o.', 'contact_person' => 'Eva Černá', 'phone' => '+420 444 555 666', 'email' => 'eva@quicktransport.cz'],
    ['name' => 'Speed Logistics', 'contact_person' => 'Tomáš Veselý', 'phone' => '+420 777 888 999', 'email' => 'tomas@speedlogistics.cz'],
];

$deliverySupplierIds = [];
foreach ($deliverySuppliers as $supplier) {
    $supplier['created_at'] = now();
    $supplier['updated_at'] = now();
    $deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId($supplier);
}

// Product Categories
echo "Adding product categories...\n";
$categories = [
    ['name' => 'Electronics', 'description' => 'Electronic devices and accessories', 'visible_in_pos' => 1],
    ['name' => 'Clothing', 'description' => 'Apparel and fashion items', 'visible_in_pos' => 1],
    ['name' => 'Food & Beverages', 'description' => 'Food products and drinks', 'visible_in_pos' => 1],
    ['name' => 'Home & Garden', 'description' => 'Home improvement and garden supplies', 'visible_in_pos' => 1],
    ['name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear', 'visible_in_pos' => 1],
    ['name' => 'Books & Stationery', 'description' => 'Books, notebooks, and office supplies', 'visible_in_pos' => 1],
];

$categoryIds = [];
foreach ($categories as $category) {
    $category['created_at'] = now();
    $category['updated_at'] = now();
    $categoryIds[] = DB::table('product_categories')->insertGetId($category);
}

// Shipments
echo "Adding shipments...\n";
$shipments = [
    [
        'shipment_supplier_id' => $shipmentSupplierIds[0],
        'storage_id' => $storageId,
        'shipment_code' => 'SH-2025-001',
        'shipment_date' => now()->subDays(30),
        'status' => 'completed',
    ],
    [
        'shipment_supplier_id' => $shipmentSupplierIds[1],
        'storage_id' => $storageId,
        'shipment_code' => 'SH-2025-002',
        'shipment_date' => now()->subDays(15),
        'status' => 'completed',
    ],
    [
        'shipment_supplier_id' => $shipmentSupplierIds[2],
        'storage_id' => $storageId,
        'shipment_code' => 'SH-2025-003',
        'shipment_date' => now()->subDays(5),
        'status' => 'pending',
    ],
];

$shipmentIds = [];
foreach ($shipments as $shipment) {
    $shipment['created_at'] = now();
    $shipment['updated_at'] = now();
    $shipmentIds[] = DB::table('shipments')->insertGetId($shipment);
}

// Products
echo "Adding products...\n";
$products = [
    // Electronics
    ['name' => 'Smartphone XZ Pro', 'code' => 'EL-001', 'category_id' => $categoryIds[0], 'shipment_id' => $shipmentIds[0], 'price' => 15999, 'cost' => 12000, 'original_quantity' => 50, 'current_quantity' => 45, 'tax' => 21, 'barcode' => '8594123456789'],
    ['name' => 'Wireless Headphones', 'code' => 'EL-002', 'category_id' => $categoryIds[0], 'shipment_id' => $shipmentIds[0], 'price' => 2499, 'cost' => 1800, 'original_quantity' => 100, 'current_quantity' => 85, 'tax' => 21, 'barcode' => '8594123456790'],
    ['name' => 'Laptop 15" Professional', 'code' => 'EL-003', 'category_id' => $categoryIds[0], 'shipment_id' => $shipmentIds[1], 'price' => 25999, 'cost' => 20000, 'original_quantity' => 30, 'current_quantity' => 28, 'tax' => 21, 'barcode' => '8594123456791'],
    
    // Clothing
    ['name' => 'Cotton T-Shirt Blue', 'code' => 'CL-001', 'category_id' => $categoryIds[1], 'shipment_id' => $shipmentIds[0], 'price' => 499, 'cost' => 250, 'original_quantity' => 200, 'current_quantity' => 180, 'tax' => 21, 'barcode' => '8594123456792'],
    ['name' => 'Denim Jeans Classic', 'code' => 'CL-002', 'category_id' => $categoryIds[1], 'shipment_id' => $shipmentIds[1], 'price' => 1299, 'cost' => 800, 'original_quantity' => 150, 'current_quantity' => 140, 'tax' => 21, 'barcode' => '8594123456793'],
    ['name' => 'Winter Jacket Premium', 'code' => 'CL-003', 'category_id' => $categoryIds[1], 'shipment_id' => $shipmentIds[1], 'price' => 3999, 'cost' => 2500, 'original_quantity' => 80, 'current_quantity' => 75, 'tax' => 21, 'barcode' => '8594123456794'],
    
    // Food & Beverages
    ['name' => 'Organic Coffee Beans 1kg', 'code' => 'FB-001', 'category_id' => $categoryIds[2], 'shipment_id' => $shipmentIds[0], 'price' => 399, 'cost' => 250, 'original_quantity' => 500, 'current_quantity' => 450, 'tax' => 15, 'barcode' => '8594123456795'],
    ['name' => 'Green Tea Premium Box', 'code' => 'FB-002', 'category_id' => $categoryIds[2], 'shipment_id' => $shipmentIds[0], 'price' => 299, 'cost' => 180, 'original_quantity' => 300, 'current_quantity' => 280, 'tax' => 15, 'barcode' => '8594123456796'],
    ['name' => 'Energy Drink 24-pack', 'code' => 'FB-003', 'category_id' => $categoryIds[2], 'shipment_id' => $shipmentIds[1], 'price' => 599, 'cost' => 400, 'original_quantity' => 200, 'current_quantity' => 190, 'tax' => 21, 'barcode' => '8594123456797'],
    
    // Home & Garden
    ['name' => 'LED Light Bulb Set', 'code' => 'HG-001', 'category_id' => $categoryIds[3], 'shipment_id' => $shipmentIds[1], 'price' => 599, 'cost' => 350, 'original_quantity' => 250, 'current_quantity' => 240, 'tax' => 21, 'barcode' => '8594123456798'],
    ['name' => 'Garden Tool Set', 'code' => 'HG-002', 'category_id' => $categoryIds[3], 'shipment_id' => $shipmentIds[1], 'price' => 1999, 'cost' => 1200, 'original_quantity' => 100, 'current_quantity' => 95, 'tax' => 21, 'barcode' => '8594123456799'],
    
    // Sports & Outdoors
    ['name' => 'Yoga Mat Professional', 'code' => 'SO-001', 'category_id' => $categoryIds[4], 'shipment_id' => $shipmentIds[1], 'price' => 799, 'cost' => 500, 'original_quantity' => 150, 'current_quantity' => 145, 'tax' => 21, 'barcode' => '8594123456800'],
    ['name' => 'Running Shoes Sport Pro', 'code' => 'SO-002', 'category_id' => $categoryIds[4], 'shipment_id' => $shipmentIds[2], 'price' => 2499, 'cost' => 1600, 'original_quantity' => 120, 'current_quantity' => 115, 'tax' => 21, 'barcode' => '8594123456801'],
    
    // Books & Stationery
    ['name' => 'Notebook A4 Premium', 'code' => 'BS-001', 'category_id' => $categoryIds[5], 'shipment_id' => $shipmentIds[0], 'price' => 199, 'cost' => 100, 'original_quantity' => 500, 'current_quantity' => 480, 'tax' => 21, 'barcode' => '8594123456802'],
    ['name' => 'Pen Set Professional', 'code' => 'BS-002', 'category_id' => $categoryIds[5], 'shipment_id' => $shipmentIds[0], 'price' => 349, 'cost' => 200, 'original_quantity' => 400, 'current_quantity' => 390, 'tax' => 21, 'barcode' => '8594123456803'],
];

$productIds = [];
foreach ($products as $product) {
    $product['created_at'] = now();
    $product['updated_at'] = now();
    $productIds[] = DB::table('products')->insertGetId($product);
}

// Customers
echo "Adding customers...\n";
$customers = [
    ['name' => 'Petr Novotný', 'email' => 'petr.novotny@email.cz', 'phone' => '+420 601 234 567', 'address' => 'Václavské náměstí 1, Prague 1', 'city' => 'Prague', 'postal_code' => '110 00', 'tax_code' => 'CZ1234567890', 'vat_code' => 'CZ1234567890'],
    ['name' => 'Jana Svobodová', 'email' => 'jana.svobodova@email.cz', 'phone' => '+420 602 345 678', 'address' => 'Masarykova 15, Brno', 'city' => 'Brno', 'postal_code' => '602 00', 'tax_code' => 'CZ2345678901', 'vat_code' => 'CZ2345678901'],
    ['name' => 'Martin Dvořák', 'email' => 'martin.dvorak@email.cz', 'phone' => '+420 603 456 789', 'address' => 'Náměstí Svobody 10, Ostrava', 'city' => 'Ostrava', 'postal_code' => '702 00', 'tax_code' => 'CZ3456789012', 'vat_code' => 'CZ3456789012'],
    ['name' => 'Eva Procházková', 'email' => 'eva.prochazkova@email.cz', 'phone' => '+420 604 567 890', 'address' => 'Hlavní třída 20, Plzeň', 'city' => 'Plzeň', 'postal_code' => '301 00', 'tax_code' => 'CZ4567890123', 'vat_code' => 'CZ4567890123'],
    ['name' => 'Tomáš Černý', 'email' => 'tomas.cerny@email.cz', 'phone' => '+420 605 678 901', 'address' => 'Dlouhá ulice 5, České Budějovice', 'city' => 'České Budějovice', 'postal_code' => '370 01', 'tax_code' => 'CZ5678901234', 'vat_code' => 'CZ5678901234'],
];

$customerIds = [];
foreach ($customers as $customer) {
    $customer['created_at'] = now();
    $customer['updated_at'] = now();
    $customerIds[] = DB::table('customers')->insertGetId($customer);
}

// Orders
echo "Adding orders...\n";
$orders = [
    // Recent orders
    ['customer_id' => $customerIds[0], 'delivery_supplier_id' => $deliverySupplierIds[0], 'total_price' => 19497, 'paid_amount' => 19497, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
    ['customer_id' => $customerIds[1], 'delivery_supplier_id' => $deliverySupplierIds[1], 'total_price' => 5996, 'paid_amount' => 3000, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
    ['customer_id' => $customerIds[2], 'delivery_supplier_id' => $deliverySupplierIds[0], 'total_price' => 28498, 'paid_amount' => 28498, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)],
    ['customer_id' => $customerIds[3], 'delivery_supplier_id' => $deliverySupplierIds[2], 'total_price' => 2495, 'paid_amount' => 2495, 'created_at' => now()->subDays(7), 'updated_at' => now()->subDays(7)],
    ['customer_id' => $customerIds[4], 'delivery_supplier_id' => $deliverySupplierIds[1], 'total_price' => 7995, 'paid_amount' => 5000, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)],
    // Older orders for history
    ['customer_id' => $customerIds[0], 'delivery_supplier_id' => $deliverySupplierIds[0], 'total_price' => 3497, 'paid_amount' => 3497, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)],
    ['customer_id' => $customerIds[1], 'delivery_supplier_id' => $deliverySupplierIds[2], 'total_price' => 1698, 'paid_amount' => 1698, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)],
    ['customer_id' => $customerIds[2], 'delivery_supplier_id' => $deliverySupplierIds[1], 'total_price' => 5198, 'paid_amount' => 5198, 'created_at' => now()->subDays(25), 'updated_at' => now()->subDays(25)],
];

$orderIds = [];
foreach ($orders as $order) {
    $orderIds[] = DB::table('orders')->insertGetId($order);
}

// Order Products
echo "Adding order products...\n";
$orderProducts = [
    // Order 1: Smartphone + Headphones + T-Shirt
    ['order_id' => $orderIds[0], 'product_id' => $productIds[0], 'quantity' => 1, 'price' => 15999, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
    ['order_id' => $orderIds[0], 'product_id' => $productIds[1], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
    ['order_id' => $orderIds[0], 'product_id' => $productIds[3], 'quantity' => 2, 'price' => 499, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
    
    // Order 2: Jeans + Winter Jacket
    ['order_id' => $orderIds[1], 'product_id' => $productIds[4], 'quantity' => 2, 'price' => 1299, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
    ['order_id' => $orderIds[1], 'product_id' => $productIds[5], 'quantity' => 1, 'price' => 3999, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
    
    // Order 3: Laptop + Running Shoes
    ['order_id' => $orderIds[2], 'product_id' => $productIds[2], 'quantity' => 1, 'price' => 25999, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)],
    ['order_id' => $orderIds[2], 'product_id' => $productIds[12], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)],
    
    // Order 4: Running Shoes
    ['order_id' => $orderIds[3], 'product_id' => $productIds[12], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(7), 'updated_at' => now()->subDays(7)],
    
    // Order 5: Winter Jacket + T-Shirts
    ['order_id' => $orderIds[4], 'product_id' => $productIds[5], 'quantity' => 1, 'price' => 3999, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)],
    ['order_id' => $orderIds[4], 'product_id' => $productIds[3], 'quantity' => 8, 'price' => 499, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)],
    
    // Order 6: Headphones + T-Shirt
    ['order_id' => $orderIds[5], 'product_id' => $productIds[1], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)],
    ['order_id' => $orderIds[5], 'product_id' => $productIds[3], 'quantity' => 2, 'price' => 499, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)],
    
    // Order 7: Jeans + Coffee
    ['order_id' => $orderIds[6], 'product_id' => $productIds[4], 'quantity' => 1, 'price' => 1299, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)],
    ['order_id' => $orderIds[6], 'product_id' => $productIds[6], 'quantity' => 1, 'price' => 399, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)],
    
    // Order 8: Headphones + Yoga Mat
    ['order_id' => $orderIds[7], 'product_id' => $productIds[1], 'quantity' => 2, 'price' => 2499, 'created_at' => now()->subDays(25), 'updated_at' => now()->subDays(25)],
    ['order_id' => $orderIds[7], 'product_id' => $productIds[11], 'quantity' => 1, 'price' => 799, 'created_at' => now()->subDays(25), 'updated_at' => now()->subDays(25)],
];

foreach ($orderProducts as $orderProduct) {
    DB::table('order_products')->insert($orderProduct);
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\n✅ Database reset and seeding completed successfully!\n\n";
echo "📊 Summary:\n";
echo "   - Storage: 1\n";
echo "   - Shipment Suppliers: " . count($shipmentSuppliers) . "\n";
echo "   - Delivery Suppliers: " . count($deliverySuppliers) . "\n";
echo "   - Categories: " . count($categories) . "\n";
echo "   - Shipments: " . count($shipments) . "\n";
echo "   - Products: " . count($products) . "\n";
echo "   - Customers: " . count($customers) . "\n";
echo "   - Orders: " . count($orders) . "\n";
echo "   - Order Products: " . count($orderProducts) . "\n";
echo "\n🎉 Ready to test!\n";
