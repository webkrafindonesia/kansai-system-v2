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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->float('discount_sales')->default(0)->after('sales_id');
            $table->float('discount_company')->default(0)->after('discount_sales');
            $table->string('expedition')->nullable()->after('discount_company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('city');
            $table->dropColumn('discount_sales');
            $table->dropColumn('discount_company');
            $table->dropColumn('expedition');
        });
    }
};
