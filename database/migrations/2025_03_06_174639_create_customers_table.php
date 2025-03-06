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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Customer Name
            $table->string('email')->unique(); // Customer Email
            $table->text('address')->nullable(); // Customer Address
            $table->string('phone')->nullable(); // Customer Phone
            $table->integer('total_orders')->default(0); // ✅ Auto-count from orders
            $table->decimal('total_debt', 10, 2)->default(0); // ✅ Total Price - Paid Amount from orders
            $table->string('vat_code')->nullable(); // VAT Code
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
