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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Product name
            $table->string('code'); // Product code (Not unique)
            $table->integer('original_quantity'); // Initial quantity
            $table->integer('actual_quantity'); // Auto-set to original_quantity
            $table->decimal('price', 10, 2); // Selling price
            $table->decimal('cost', 10, 2); // Cost per unit
            $table->decimal('total_cost', 10, 2); // Auto-calculated: original_quantity * cost
            $table->string('category'); // Product category
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade'); // FK to Shipment
            $table->date('expired_date'); // Auto-inherit from shipment
            $table->decimal('tax', 5, 2)->default(0); // Tax percentage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
