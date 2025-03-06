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
    Schema::table('shipments', function (Blueprint $table) {
        $table->decimal('cost', 10, 2)->default(0)->after('expired_date'); // ✅ Add cost column
    });
}

public function down()
{
    Schema::table('shipments', function (Blueprint $table) {
        $table->dropColumn('cost'); // Remove column if rolled back
    });
}

};
