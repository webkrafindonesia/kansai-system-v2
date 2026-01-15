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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->after('delivery_order_id');
            $table->date('invoice_date')->nullable()->after('invoice_no');
            $table->date('term_of_payment')->nullable()->after('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('invoice_no');
            $table->dropColumn('invoice_date');
            $table->dropColumn('term_of_payment');
        });
    }
};
