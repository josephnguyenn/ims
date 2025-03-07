<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade'); // ✅ Link to customer
            $table->foreignId('delivery_supplier_id')->nullable()->constrained('delivery_suppliers')->onDelete('cascade'); // ✅ Define column first
            $table->decimal('paid_amount', 10, 2)->default(0); // ✅ Paid amount
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};