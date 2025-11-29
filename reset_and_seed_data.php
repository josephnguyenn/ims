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
    'created_at' => now(),
    'updated_at' => now(),
]);

// Shipment Suppliers
echo "Adding shipment suppliers...\n";
$shipmentSupplierIds = [];
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'ABC Wholesale Ltd.', 'created_at' => now(), 'updated_at' => now()]);
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'Global Imports s.r.o.', 'created_at' => now(), 'updated_at' => now()]);
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'EuroTrade Partners', 'created_at' => now(), 'updated_at' => now()]);
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'Czech Distribution Center', 'created_at' => now(), 'updated_at' => now()]);
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'International Supply Co.', 'created_at' => now(), 'updated_at' => now()]);
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'Asia Trade Imports', 'created_at' => now(), 'updated_at' => now()]);
$shipmentSupplierIds[] = DB::table('shipment_suppliers')->insertGetId(['name' => 'European Goods Hub', 'created_at' => now(), 'updated_at' => now()]);

// Delivery Suppliers
echo "Adding delivery suppliers...\n";
$deliverySupplierIds = [];
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'Fast Delivery Express', 'created_at' => now(), 'updated_at' => now()]);
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'Quick Transport s.r.o.', 'created_at' => now(), 'updated_at' => now()]);
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'Speed Logistics', 'created_at' => now(), 'updated_at' => now()]);
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'DPD Czech Republic', 'created_at' => now(), 'updated_at' => now()]);
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'PPL CZ', 'created_at' => now(), 'updated_at' => now()]);
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'Česká pošta', 'created_at' => now(), 'updated_at' => now()]);
$deliverySupplierIds[] = DB::table('delivery_suppliers')->insertGetId(['name' => 'GLS Czech', 'created_at' => now(), 'updated_at' => now()]);

// Product Categories
echo "Adding product categories...\n";
$categoryIds = [];
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Electronics', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Clothing', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Food & Beverages', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Home & Garden', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Sports', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Books & Stationery', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Beauty & Health', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Toys & Games', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Automotive', 'visible_in_pos' => 1]);
$categoryIds[] = DB::table('product_categories')->insertGetId(['name' => 'Pet Supplies', 'visible_in_pos' => 1]);

// Shipments
echo "Adding shipments...\n";
$shipmentIds = [];
$shipmentIds[] = DB::table('shipments')->insertGetId([
    'shipment_supplier_id' => $shipmentSupplierIds[0],
    'storage_id' => $storageId,
    'order_date' => now()->subDays(50),
    'received_date' => now()->subDays(45),
    'expired_date' => now()->addDays(315),
    'cost' => 500000,
    'created_at' => now(),
    'updated_at' => now(),
]);
$shipmentIds[] = DB::table('shipments')->insertGetId([
    'shipment_supplier_id' => $shipmentSupplierIds[1],
    'storage_id' => $storageId,
    'order_date' => now()->subDays(40),
    'received_date' => now()->subDays(35),
    'expired_date' => now()->addDays(325),
    'cost' => 350000,
    'created_at' => now(),
    'updated_at' => now(),
]);
$shipmentIds[] = DB::table('shipments')->insertGetId([
    'shipment_supplier_id' => $shipmentSupplierIds[2],
    'storage_id' => $storageId,
    'order_date' => now()->subDays(30),
    'received_date' => now()->subDays(25),
    'expired_date' => now()->addDays(335),
    'cost' => 280000,
    'created_at' => now(),
    'updated_at' => now(),
]);
$shipmentIds[] = DB::table('shipments')->insertGetId([
    'shipment_supplier_id' => $shipmentSupplierIds[3],
    'storage_id' => $storageId,
    'order_date' => now()->subDays(20),
    'received_date' => now()->subDays(15),
    'expired_date' => now()->addDays(345),
    'cost' => 420000,
    'created_at' => now(),
    'updated_at' => now(),
]);
$shipmentIds[] = DB::table('shipments')->insertGetId([
    'shipment_supplier_id' => $shipmentSupplierIds[4],
    'storage_id' => $storageId,
    'order_date' => now()->subDays(12),
    'received_date' => now()->subDays(8),
    'expired_date' => now()->addDays(352),
    'cost' => 310000,
    'created_at' => now(),
    'updated_at' => now(),
]);

// Products
echo "Adding products...\n";
$productIds = [];
// Electronics (10 products)
$productIds[] = DB::table('products')->insertGetId(['name' => 'Smartphone XZ Pro', 'code' => 'EL-001', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[0], 'price' => 15999, 'cost' => 12000, 'original_quantity' => 50, 'actual_quantity' => 45, 'total_cost' => 600000, 'tax' => 21, 'barcode' => '8594001', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Wireless Headphones', 'code' => 'EL-002', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[0], 'price' => 2499, 'cost' => 1800, 'original_quantity' => 100, 'actual_quantity' => 85, 'total_cost' => 180000, 'tax' => 21, 'barcode' => '8594002', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Laptop 15.6"', 'code' => 'EL-003', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[1], 'price' => 25999, 'cost' => 20000, 'original_quantity' => 30, 'actual_quantity' => 28, 'total_cost' => 600000, 'tax' => 21, 'barcode' => '8594003', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Wireless Mouse', 'code' => 'EL-004', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[0], 'price' => 599, 'cost' => 350, 'original_quantity' => 200, 'actual_quantity' => 180, 'total_cost' => 70000, 'tax' => 21, 'barcode' => '8594004', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Mechanical Keyboard', 'code' => 'EL-005', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[1], 'price' => 1999, 'cost' => 1400, 'original_quantity' => 80, 'actual_quantity' => 75, 'total_cost' => 112000, 'tax' => 21, 'barcode' => '8594005', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'USB-C Hub 7-in-1', 'code' => 'EL-006', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[1], 'price' => 899, 'cost' => 600, 'original_quantity' => 120, 'actual_quantity' => 110, 'total_cost' => 72000, 'tax' => 21, 'barcode' => '8594006', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Power Bank 20000mAh', 'code' => 'EL-007', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[2], 'price' => 1299, 'cost' => 900, 'original_quantity' => 150, 'actual_quantity' => 140, 'total_cost' => 135000, 'tax' => 21, 'barcode' => '8594007', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Smart Watch Series 5', 'code' => 'EL-008', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[2], 'price' => 7999, 'cost' => 6000, 'original_quantity' => 60, 'actual_quantity' => 55, 'total_cost' => 360000, 'tax' => 21, 'barcode' => '8594008', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Bluetooth Speaker', 'code' => 'EL-009', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[2], 'price' => 1799, 'cost' => 1200, 'original_quantity' => 100, 'actual_quantity' => 90, 'total_cost' => 120000, 'tax' => 21, 'barcode' => '8594009', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => '4K Webcam', 'code' => 'EL-010', 'category' => 'Electronics', 'shipment_id' => $shipmentIds[3], 'price' => 2999, 'cost' => 2200, 'original_quantity' => 70, 'actual_quantity' => 65, 'total_cost' => 154000, 'tax' => 21, 'barcode' => '8594010', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);

// Clothing (10 products)
$productIds[] = DB::table('products')->insertGetId(['name' => 'Cotton T-Shirt', 'code' => 'CL-001', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[0], 'price' => 499, 'cost' => 250, 'original_quantity' => 200, 'actual_quantity' => 180, 'total_cost' => 50000, 'tax' => 21, 'barcode' => '8594011', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Denim Jeans', 'code' => 'CL-002', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[1], 'price' => 1299, 'cost' => 800, 'original_quantity' => 150, 'actual_quantity' => 140, 'total_cost' => 120000, 'tax' => 21, 'barcode' => '8594012', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Winter Jacket', 'code' => 'CL-003', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[0], 'price' => 3999, 'cost' => 2800, 'original_quantity' => 80, 'actual_quantity' => 75, 'total_cost' => 224000, 'tax' => 21, 'barcode' => '8594013', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Hoodie Premium', 'code' => 'CL-004', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[1], 'price' => 1599, 'cost' => 1000, 'original_quantity' => 120, 'actual_quantity' => 110, 'total_cost' => 120000, 'tax' => 21, 'barcode' => '8594014', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Dress Shirt', 'code' => 'CL-005', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[1], 'price' => 899, 'cost' => 550, 'original_quantity' => 100, 'actual_quantity' => 95, 'total_cost' => 55000, 'tax' => 21, 'barcode' => '8594015', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Summer Dress', 'code' => 'CL-006', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[2], 'price' => 1199, 'cost' => 750, 'original_quantity' => 90, 'actual_quantity' => 85, 'total_cost' => 67500, 'tax' => 21, 'barcode' => '8594016', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Leather Belt', 'code' => 'CL-007', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[2], 'price' => 699, 'cost' => 400, 'original_quantity' => 150, 'actual_quantity' => 140, 'total_cost' => 60000, 'tax' => 21, 'barcode' => '8594017', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Baseball Cap', 'code' => 'CL-008', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[3], 'price' => 399, 'cost' => 200, 'original_quantity' => 180, 'actual_quantity' => 170, 'total_cost' => 36000, 'tax' => 21, 'barcode' => '8594018', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Wool Scarf', 'code' => 'CL-009', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[3], 'price' => 599, 'cost' => 350, 'original_quantity' => 120, 'actual_quantity' => 115, 'total_cost' => 42000, 'tax' => 21, 'barcode' => '8594019', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Sports Socks 3-Pack', 'code' => 'CL-010', 'category' => 'Clothing', 'shipment_id' => $shipmentIds[3], 'price' => 299, 'cost' => 150, 'original_quantity' => 250, 'actual_quantity' => 240, 'total_cost' => 37500, 'tax' => 21, 'barcode' => '8594020', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);

// Food & Beverages (8 products)
$productIds[] = DB::table('products')->insertGetId(['name' => 'Coffee Beans 1kg', 'code' => 'FB-001', 'category' => 'Food', 'shipment_id' => $shipmentIds[0], 'price' => 399, 'cost' => 250, 'original_quantity' => 500, 'actual_quantity' => 450, 'total_cost' => 125000, 'tax' => 15, 'barcode' => '8594021', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Green Tea Box', 'code' => 'FB-002', 'category' => 'Food', 'shipment_id' => $shipmentIds[0], 'price' => 299, 'cost' => 180, 'original_quantity' => 300, 'actual_quantity' => 280, 'total_cost' => 54000, 'tax' => 15, 'barcode' => '8594022', 'expired_date' => now()->addDays(315), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Organic Honey 500g', 'code' => 'FB-003', 'category' => 'Food', 'shipment_id' => $shipmentIds[1], 'price' => 249, 'cost' => 150, 'original_quantity' => 200, 'actual_quantity' => 190, 'total_cost' => 30000, 'tax' => 15, 'barcode' => '8594023', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Chocolate Bar Premium', 'code' => 'FB-004', 'category' => 'Food', 'shipment_id' => $shipmentIds[1], 'price' => 89, 'cost' => 50, 'original_quantity' => 600, 'actual_quantity' => 580, 'total_cost' => 30000, 'tax' => 15, 'barcode' => '8594024', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Energy Drink 24-pack', 'code' => 'FB-005', 'category' => 'Food', 'shipment_id' => $shipmentIds[2], 'price' => 599, 'cost' => 400, 'original_quantity' => 150, 'actual_quantity' => 140, 'total_cost' => 60000, 'tax' => 21, 'barcode' => '8594025', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Mineral Water 6-pack', 'code' => 'FB-006', 'category' => 'Food', 'shipment_id' => $shipmentIds[2], 'price' => 149, 'cost' => 90, 'original_quantity' => 400, 'actual_quantity' => 380, 'total_cost' => 36000, 'tax' => 15, 'barcode' => '8594026', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Protein Bar Box', 'code' => 'FB-007', 'category' => 'Food', 'shipment_id' => $shipmentIds[3], 'price' => 449, 'cost' => 300, 'original_quantity' => 250, 'actual_quantity' => 240, 'total_cost' => 75000, 'tax' => 15, 'barcode' => '8594027', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Olive Oil 1L', 'code' => 'FB-008', 'category' => 'Food', 'shipment_id' => $shipmentIds[3], 'price' => 349, 'cost' => 220, 'original_quantity' => 180, 'actual_quantity' => 170, 'total_cost' => 39600, 'tax' => 15, 'barcode' => '8594028', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);

// Home & Garden (5 products - simplified)
$productIds[] = DB::table('products')->insertGetId(['name' => 'LED Light Bulbs', 'code' => 'HG-001', 'category' => 'Home', 'shipment_id' => $shipmentIds[1], 'price' => 599, 'cost' => 350, 'original_quantity' => 250, 'actual_quantity' => 240, 'total_cost' => 87500, 'tax' => 21, 'barcode' => '8594029', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Garden Tool Set', 'code' => 'HG-002', 'category' => 'Home', 'shipment_id' => $shipmentIds[2], 'price' => 1999, 'cost' => 1200, 'original_quantity' => 80, 'actual_quantity' => 75, 'total_cost' => 96000, 'tax' => 21, 'barcode' => '8594030', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Towel Set 4-piece', 'code' => 'HG-003', 'category' => 'Home', 'shipment_id' => $shipmentIds[3], 'price' => 899, 'cost' => 550, 'original_quantity' => 120, 'actual_quantity' => 110, 'total_cost' => 66000, 'tax' => 21, 'barcode' => '8594031', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Storage Basket Set', 'code' => 'HG-004', 'category' => 'Home', 'shipment_id' => $shipmentIds[3], 'price' => 699, 'cost' => 400, 'original_quantity' => 150, 'actual_quantity' => 145, 'total_cost' => 60000, 'tax' => 21, 'barcode' => '8594032', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Wall Clock Modern', 'code' => 'HG-005', 'category' => 'Home', 'shipment_id' => $shipmentIds[4], 'price' => 999, 'cost' => 650, 'original_quantity' => 90, 'actual_quantity' => 85, 'total_cost' => 58500, 'tax' => 21, 'barcode' => '8594033', 'expired_date' => now()->addDays(352), 'created_at' => now(), 'updated_at' => now()]);

// Sports (5 products - simplified)
$productIds[] = DB::table('products')->insertGetId(['name' => 'Yoga Mat', 'code' => 'SO-001', 'category' => 'Sports', 'shipment_id' => $shipmentIds[1], 'price' => 799, 'cost' => 500, 'original_quantity' => 150, 'actual_quantity' => 145, 'total_cost' => 75000, 'tax' => 21, 'barcode' => '8594034', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Running Shoes', 'code' => 'SO-002', 'category' => 'Sports', 'shipment_id' => $shipmentIds[1], 'price' => 2499, 'cost' => 1600, 'original_quantity' => 120, 'actual_quantity' => 115, 'total_cost' => 192000, 'tax' => 21, 'barcode' => '8594035', 'expired_date' => now()->addDays(325), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Dumbbell Set 20kg', 'code' => 'SO-003', 'category' => 'Sports', 'shipment_id' => $shipmentIds[2], 'price' => 1899, 'cost' => 1300, 'original_quantity' => 60, 'actual_quantity' => 55, 'total_cost' => 78000, 'tax' => 21, 'barcode' => '8594036', 'expired_date' => now()->addDays(335), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Water Bottle 1L', 'code' => 'SO-004', 'category' => 'Sports', 'shipment_id' => $shipmentIds[3], 'price' => 299, 'cost' => 180, 'original_quantity' => 300, 'actual_quantity' => 290, 'total_cost' => 54000, 'tax' => 21, 'barcode' => '8594037', 'expired_date' => now()->addDays(345), 'created_at' => now(), 'updated_at' => now()]);
$productIds[] = DB::table('products')->insertGetId(['name' => 'Gym Bag Premium', 'code' => 'SO-005', 'category' => 'Sports', 'shipment_id' => $shipmentIds[4], 'price' => 1299, 'cost' => 850, 'original_quantity' => 100, 'actual_quantity' => 95, 'total_cost' => 85000, 'tax' => 21, 'barcode' => '8594038', 'expired_date' => now()->addDays(352), 'created_at' => now(), 'updated_at' => now()]);

// Customers
echo "Adding customers...\n";
$customerIds = [];
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Petr Novotný', 'email' => 'petr@email.cz', 'phone' => '+420601234567', 'address' => 'Václavské náměstí 1, Prague', 'city' => 'Prague', 'postal_code' => '11000', 'vat_code' => 'CZ1234567890', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Jana Svobodová', 'email' => 'jana@email.cz', 'phone' => '+420602345678', 'address' => 'Masarykova 15, Brno', 'city' => 'Brno', 'postal_code' => '60200', 'vat_code' => 'CZ2345678901', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Martin Dvořák', 'email' => 'martin@email.cz', 'phone' => '+420603456789', 'address' => 'Náměstí Svobody 10, Ostrava', 'city' => 'Ostrava', 'postal_code' => '70200', 'vat_code' => 'CZ3456789012', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Eva Procházková', 'email' => 'eva@email.cz', 'phone' => '+420604567890', 'address' => 'Hlavní třída 20, Plzeň', 'city' => 'Plzeň', 'postal_code' => '30100', 'vat_code' => 'CZ4567890123', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Tomáš Černý', 'email' => 'tomas@email.cz', 'phone' => '+420605678901', 'address' => 'Dlouhá ulice 5, České Budějovice', 'city' => 'České Budějovice', 'postal_code' => '37001', 'vat_code' => 'CZ5678901234', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Lucie Horáková', 'email' => 'lucie@email.cz', 'phone' => '+420606789012', 'address' => 'Palackého 8, Olomouc', 'city' => 'Olomouc', 'postal_code' => '77900', 'vat_code' => 'CZ6789012345', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'David Kučera', 'email' => 'david@email.cz', 'phone' => '+420607890123', 'address' => 'Revoluční 12, Hradec Králové', 'city' => 'Hradec Králové', 'postal_code' => '50002', 'vat_code' => 'CZ7890123456', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Monika Veselá', 'email' => 'monika@email.cz', 'phone' => '+420608901234', 'address' => 'Masarykovo náměstí 4, Pardubice', 'city' => 'Pardubice', 'postal_code' => '53002', 'vat_code' => 'CZ8901234567', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Jakub Marek', 'email' => 'jakub@email.cz', 'phone' => '+420609012345', 'address' => 'Nádražní 25, Zlín', 'city' => 'Zlín', 'postal_code' => '76001', 'vat_code' => 'CZ9012345678', 'created_at' => now(), 'updated_at' => now()]);
$customerIds[] = DB::table('customers')->insertGetId(['name' => 'Barbora Málková', 'email' => 'barbora@email.cz', 'phone' => '+420610123456', 'address' => 'Havlíčkova 30, Karlovy Vary', 'city' => 'Karlovy Vary', 'postal_code' => '36001', 'vat_code' => 'CZ0123456789', 'created_at' => now(), 'updated_at' => now()]);

// Orders
echo "Adding orders...\n";
$orderIds = [];
// Recent orders (last 2 weeks) - orders table only has customer_id, delivery_supplier_id, paid_amount
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[0], 'delivery_supplier_id' => $deliverySupplierIds[0], 'paid_amount' => 18498, 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[1], 'delivery_supplier_id' => $deliverySupplierIds[1], 'paid_amount' => 3000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[2], 'delivery_supplier_id' => $deliverySupplierIds[2], 'paid_amount' => 28498, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[3], 'delivery_supplier_id' => $deliverySupplierIds[3], 'paid_amount' => 4495, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[4], 'delivery_supplier_id' => $deliverySupplierIds[4], 'paid_amount' => 5000, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[5], 'delivery_supplier_id' => $deliverySupplierIds[5], 'paid_amount' => 3497, 'created_at' => now()->subDays(6), 'updated_at' => now()->subDays(6)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[6], 'delivery_supplier_id' => $deliverySupplierIds[6], 'paid_amount' => 2698, 'created_at' => now()->subDays(7), 'updated_at' => now()->subDays(7)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[7], 'delivery_supplier_id' => $deliverySupplierIds[0], 'paid_amount' => 10000, 'created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[8], 'delivery_supplier_id' => $deliverySupplierIds[1], 'paid_amount' => 6495, 'created_at' => now()->subDays(9), 'updated_at' => now()->subDays(9)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[9], 'delivery_supplier_id' => $deliverySupplierIds[2], 'paid_amount' => 12996, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);

// Older orders (2-4 weeks ago)
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[0], 'delivery_supplier_id' => $deliverySupplierIds[3], 'paid_amount' => 5597, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[2], 'delivery_supplier_id' => $deliverySupplierIds[4], 'paid_amount' => 9495, 'created_at' => now()->subDays(18), 'updated_at' => now()->subDays(18)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[4], 'delivery_supplier_id' => $deliverySupplierIds[5], 'paid_amount' => 2000, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[6], 'delivery_supplier_id' => $deliverySupplierIds[6], 'paid_amount' => 7996, 'created_at' => now()->subDays(22), 'updated_at' => now()->subDays(22)]);
$orderIds[] = DB::table('orders')->insertGetId(['customer_id' => $customerIds[8], 'delivery_supplier_id' => $deliverySupplierIds[0], 'paid_amount' => 15497, 'created_at' => now()->subDays(25), 'updated_at' => now()->subDays(25)]);

// Order Products
echo "Adding order products...\n";
// Order 1: Smartphone + Headphones
DB::table('order_products')->insert(['order_id' => $orderIds[0], 'product_id' => $productIds[0], 'quantity' => 1, 'price' => 15999, 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)]);
DB::table('order_products')->insert(['order_id' => $orderIds[0], 'product_id' => $productIds[1], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)]);

// Order 2: Jeans + Winter Jacket
DB::table('order_products')->insert(['order_id' => $orderIds[1], 'product_id' => $productIds[11], 'quantity' => 2, 'price' => 1299, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]);
DB::table('order_products')->insert(['order_id' => $orderIds[1], 'product_id' => $productIds[12], 'quantity' => 1, 'price' => 3999, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]);

// Order 3: Laptop + Running Shoes
DB::table('order_products')->insert(['order_id' => $orderIds[2], 'product_id' => $productIds[2], 'quantity' => 1, 'price' => 25999, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)]);
DB::table('order_products')->insert(['order_id' => $orderIds[2], 'product_id' => $productIds[44], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)]);

// Order 4: Vacuum Cleaner + Garden Tools
DB::table('order_products')->insert(['order_id' => $orderIds[3], 'product_id' => $productIds[29], 'quantity' => 1, 'price' => 4999, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)]);

// Order 5: Smart Watch + Yoga Mat
DB::table('order_products')->insert(['order_id' => $orderIds[4], 'product_id' => $productIds[7], 'quantity' => 1, 'price' => 7999, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)]);

// Order 6: T-Shirts + Hoodies
DB::table('order_products')->insert(['order_id' => $orderIds[5], 'product_id' => $productIds[10], 'quantity' => 3, 'price' => 499, 'created_at' => now()->subDays(6), 'updated_at' => now()->subDays(6)]);
DB::table('order_products')->insert(['order_id' => $orderIds[5], 'product_id' => $productIds[13], 'quantity' => 1, 'price' => 1599, 'created_at' => now()->subDays(6), 'updated_at' => now()->subDays(6)]);

// Order 7: Running Shoes + Gym Bag
DB::table('order_products')->insert(['order_id' => $orderIds[6], 'product_id' => $productIds[44], 'quantity' => 1, 'price' => 2499, 'created_at' => now()->subDays(7), 'updated_at' => now()->subDays(7)]);

// Order 8: Wireless Headphones + Power Bank + Keyboard
DB::table('order_products')->insert(['order_id' => $orderIds[7], 'product_id' => $productIds[1], 'quantity' => 2, 'price' => 2499, 'created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)]);
DB::table('order_products')->insert(['order_id' => $orderIds[7], 'product_id' => $productIds[6], 'quantity' => 2, 'price' => 1299, 'created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)]);
DB::table('order_products')->insert(['order_id' => $orderIds[7], 'product_id' => $productIds[4], 'quantity' => 1, 'price' => 1999, 'created_at' => now()->subDays(8), 'updated_at' => now()->subDays(8)]);

// Order 9: Winter Jacket + Scarf + Jeans
DB::table('order_products')->insert(['order_id' => $orderIds[8], 'product_id' => $productIds[12], 'quantity' => 1, 'price' => 3999, 'created_at' => now()->subDays(9), 'updated_at' => now()->subDays(9)]);
DB::table('order_products')->insert(['order_id' => $orderIds[8], 'product_id' => $productIds[18], 'quantity' => 2, 'price' => 599, 'created_at' => now()->subDays(9), 'updated_at' => now()->subDays(9)]);
DB::table('order_products')->insert(['order_id' => $orderIds[8], 'product_id' => $productIds[11], 'quantity' => 1, 'price' => 1299, 'created_at' => now()->subDays(9), 'updated_at' => now()->subDays(9)]);

// Order 10: Laptop + Mouse + USB Hub
DB::table('order_products')->insert(['order_id' => $orderIds[9], 'product_id' => $productIds[2], 'quantity' => 1, 'price' => 25999, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);
DB::table('order_products')->insert(['order_id' => $orderIds[9], 'product_id' => $productIds[3], 'quantity' => 2, 'price' => 599, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);
DB::table('order_products')->insert(['order_id' => $orderIds[9], 'product_id' => $productIds[5], 'quantity' => 1, 'price' => 899, 'created_at' => now()->subDays(10), 'updated_at' => now()->subDays(10)]);

// Order 11: Coffee + Tea + Honey
DB::table('order_products')->insert(['order_id' => $orderIds[10], 'product_id' => $productIds[20], 'quantity' => 5, 'price' => 399, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)]);
DB::table('order_products')->insert(['order_id' => $orderIds[10], 'product_id' => $productIds[21], 'quantity' => 3, 'price' => 299, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)]);
DB::table('order_products')->insert(['order_id' => $orderIds[10], 'product_id' => $productIds[22], 'quantity' => 4, 'price' => 249, 'created_at' => now()->subDays(15), 'updated_at' => now()->subDays(15)]);

// Order 12: Smart Watch + Bluetooth Speaker
DB::table('order_products')->insert(['order_id' => $orderIds[11], 'product_id' => $productIds[7], 'quantity' => 1, 'price' => 7999, 'created_at' => now()->subDays(18), 'updated_at' => now()->subDays(18)]);
DB::table('order_products')->insert(['order_id' => $orderIds[11], 'product_id' => $productIds[8], 'quantity' => 1, 'price' => 1799, 'created_at' => now()->subDays(18), 'updated_at' => now()->subDays(18)]);

// Order 13: Dumbbell Set + Yoga Mat + Water Bottle
DB::table('order_products')->insert(['order_id' => $orderIds[12], 'product_id' => $productIds[45], 'quantity' => 1, 'price' => 1899, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)]);
DB::table('order_products')->insert(['order_id' => $orderIds[12], 'product_id' => $productIds[43], 'quantity' => 2, 'price' => 799, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)]);
DB::table('order_products')->insert(['order_id' => $orderIds[12], 'product_id' => $productIds[47], 'quantity' => 1, 'price' => 299, 'created_at' => now()->subDays(20), 'updated_at' => now()->subDays(20)]);

// Order 14: Smart Watch + Running Shoes
DB::table('order_products')->insert(['order_id' => $orderIds[13], 'product_id' => $productIds[7], 'quantity' => 1, 'price' => 7999, 'created_at' => now()->subDays(22), 'updated_at' => now()->subDays(22)]);

// Order 15: Smartphone + Webcam + Keyboard
DB::table('order_products')->insert(['order_id' => $orderIds[14], 'product_id' => $productIds[0], 'quantity' => 1, 'price' => 15999, 'created_at' => now()->subDays(25), 'updated_at' => now()->subDays(25)]);

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\n✅ Database reset and seeding completed successfully!\n\n";
echo "📊 Summary:\n";
echo "   - Storage: 1\n";
echo "   - Shipment Suppliers: 7\n";
echo "   - Delivery Suppliers: 7\n";
echo "   - Categories: 10\n";
echo "   - Shipments: 5\n";
echo "   - Products: 33 (10 Electronics, 10 Clothing, 8 Food, 5 Home, 5 Sports)\n";
echo "   - Customers: 10\n";
echo "   - Orders: 15\n";
echo "   - Order Products: 35\n";
echo "\n🎉 Ready to test!\n";

