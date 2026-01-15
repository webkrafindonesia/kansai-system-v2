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
        Schema::create('return_sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sales_order_id');
            $table->uuid('customer_id');
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('processed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('return_sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('return_sales_order_id');
            $table->integer('sales_order_item_id');
            $table->uuid('product_id');
            $table->float('qty')->default(0);
            $table->string('uom');
            $table->string('action')->default('skip');
            $table->string('price')->default(0);
            $table->string('total_price')->default(0);
            $table->string('discounted_total_price')->default(0);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('return_sales_order_items');
        Schema::dropIfExists('return_sales_orders');
    }
};
