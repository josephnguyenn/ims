<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('code', 'idx_products_code');
            $table->index('actual_quantity', 'idx_products_quantity');
            $table->index('category_id', 'idx_products_category');
            $table->index('shipment_id', 'idx_products_shipment');
            $table->index(['code', 'actual_quantity'], 'idx_products_code_quantity');
            $table->index(['shipment_id', 'actual_quantity'], 'idx_products_shipment_quantity');
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at', 'idx_orders_created');
            $table->index('customer_id', 'idx_orders_customer');
            $table->index('cashier_id', 'idx_orders_cashier');
            $table->index(['customer_id', 'created_at'], 'idx_orders_customer_created');
            $table->index(['cashier_id', 'created_at'], 'idx_orders_cashier_created');
        });

        // Order Products table indexes
        Schema::table('order_products', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_products_order');
            $table->index('product_id', 'idx_order_products_product');
            $table->index(['order_id', 'product_id'], 'idx_order_products_order_product');
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone', 'idx_customers_phone');
            $table->index('name', 'idx_customers_name');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
        });

        // Shipments table indexes
        Schema::table('shipments', function (Blueprint $table) {
            $table->index('order_date', 'idx_shipments_order_date');
            $table->index('expired_date', 'idx_shipments_expired_date');
            $table->index('shipment_supplier_id', 'idx_shipments_supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_code');
            $table->dropIndex('idx_products_quantity');
            $table->dropIndex('idx_products_category');
            $table->dropIndex('idx_products_shipment');
            $table->dropIndex('idx_products_code_quantity');
            $table->dropIndex('idx_products_shipment_quantity');
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_created');
            $table->dropIndex('idx_orders_customer');
            $table->dropIndex('idx_orders_cashier');
            $table->dropIndex('idx_orders_customer_created');
            $table->dropIndex('idx_orders_cashier_created');
        });

        // Order Products table indexes
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropIndex('idx_order_products_order');
            $table->dropIndex('idx_order_products_product');
            $table->dropIndex('idx_order_products_order_product');
        });

        // Customers table indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_phone');
            $table->dropIndex('idx_customers_name');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
        });

        // Shipments table indexes
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex('idx_shipments_order_date');
            $table->dropIndex('idx_shipments_expired_date');
            $table->dropIndex('idx_shipments_supplier');
        });
    }
};
