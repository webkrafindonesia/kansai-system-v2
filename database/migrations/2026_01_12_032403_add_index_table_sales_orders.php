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
            $table->index('salesorder_no');
            $table->index('customer_id');
            $table->index('sales_id');
            $table->index('delivery_order_id');
            $table->index('invoice_no');
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->index('sales_order_id');
            $table->index('product_id');
            $table->index('assembly_id');
        });

        Schema::table('sales_order_item_breakdowns', function (Blueprint $table) {
            $table->index('sales_order_id');
            $table->index('sales_order_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('sales_orders_salesorder_no_index');
            $table->dropIndex('sales_orders_customer_id_index');
            $table->dropIndex('sales_orders_sales_id_index');
            $table->dropIndex('sales_orders_delivery_order_id_index');
            $table->dropIndex('sales_orders_invoice_no_index');
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropIndex('sales_order_items_sales_order_id_index');
            $table->dropIndex('sales_order_items_product_id_index');
            $table->dropIndex('sales_order_items_assembly_id_index');
        });

        Schema::table('sales_order_item_breakdowns', function (Blueprint $table) {
            $table->dropIndex('sales_order_item_breakdowns_sales_order_id_index');
            $table->dropIndex('sales_order_item_breakdowns_sales_order_item_id_index');
            $table->dropIndex('sales_order_item_breakdowns_product_id_index');
        });
    }
};
