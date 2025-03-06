<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_supplier_id')->constrained('shipment_suppliers')->onDelete('cascade'); // FK to Shipment Supplier
            $table->foreignId('storage_id')->constrained('storages')->onDelete('cascade'); // FK to Storage
            $table->date('order_date'); // Date when shipment was ordered
            $table->date('received_date')->nullable(); // Date when shipment was received
            $table->date('expired_date')->nullable(); // Expiry date of products in shipment
            $table->decimal('cost', 10, 2)->default(0); // Cost of all products in shipment (Calculated)
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
