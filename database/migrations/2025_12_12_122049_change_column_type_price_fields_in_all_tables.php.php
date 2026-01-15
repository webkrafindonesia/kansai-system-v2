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
        Schema::table('employee_payrolls', function (Blueprint $table) {
            $table->decimal('amount',20,5)->default(0)->change();
            $table->decimal('amount_loan_repayment',20,5)->default(0)->change();
            $table->decimal('amount_after_loan_repayment',20,5)->default(0)->change();
        });

        Schema::table('employee_loans', function (Blueprint $table) {
            $table->decimal('amount',20,5)->default(0)->change();
            $table->decimal('remaining',20,5)->default(0)->change();
        });

        Schema::table('employee_loan_repayments', function (Blueprint $table) {
            $table->decimal('amount',20,5)->default(0)->change();
        });

        Schema::table('petty_cashes', function (Blueprint $table) {
            $table->decimal('trx_in',20,5)->default(0)->change();
            $table->decimal('trx_out',20,5)->default(0)->change();
        });

        Schema::table('product_histories', function (Blueprint $table) {
            $table->decimal('total_nominal',20,5)->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('buying_price',20,5)->default(0)->change();
            $table->decimal('selling_price',20,5)->default(0)->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('total_price',20,5)->default(0)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('price',20,5)->default(0)->change();
            $table->decimal('total_price',20,5)->default(0)->change();
        });

        Schema::table('return_sales_order_items', function (Blueprint $table) {
            $table->decimal('price',20,5)->default(0)->change();
            $table->decimal('total_price',20,5)->default(0)->change();
            $table->decimal('discounted_total_price',20,5)->default(0)->change();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('total_omset',20,5)->default(0)->change();
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal('price',20,5)->default(0)->change();
            $table->decimal('total_price',20,5)->default(0)->change();
            $table->decimal('master_price',20,5)->default(0)->change();
            $table->decimal('master_total_price',20,5)->default(0)->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
