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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('salesorder_no')->unique();
            $table->string('purchaseorder_no')->nullable()->unique();
            $table->date('date');
            $table->uuid('customer_id');
            $table->uuid('sales_id')->nullable();
            $table->float('discount_sales')->default(0);
            $table->float('discount_company')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('production_start')->default(false);
            $table->boolean('production_end')->default(false);
            $table->uuid('delivery_order_id')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->enum('invoice_status',['Belum Lunas','Lunas'])->default('Belum Lunas')->nullable();
            $table->date('term_of_payment')->nullable();
            $table->string('total_omset')->default(0); // will be filled when invoice generated
            $table->timestamp('paid_at')->nullable();
            $table->uuid('paid_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_id');
            $table->uuid('product_id')->nullable();
            $table->float('qty')->default(0);
            $table->string('uom');
            $table->string('price')->default(0);
            $table->string('total_price')->default(0);
            $table->string('master_price')->default(0);
            $table->string('master_total_price')->default(0);
            $table->text('notes')->nullable();
            $table->uuid('assembly_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('sales_order_item_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_id');
            $table->string('sales_order_item_id');
            $table->uuid('product_id')->nullable();
            $table->float('qty')->default(0);
            $table->string('uom');
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_item_breakdowns');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
