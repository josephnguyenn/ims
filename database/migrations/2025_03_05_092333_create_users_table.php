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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // User's full name
            $table->string('email')->unique(); // User's email, must be unique
            $table->string('password'); // Hashed password
            $table->enum('role', ['admin', 'staff'])->default('staff'); // Role of the user
            $table->timestamps(); // created_at and updated_at
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};