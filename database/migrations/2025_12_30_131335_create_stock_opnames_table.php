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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('warehouse_id');
            $table->timestamp('opname_date');
            $table->string('options')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status',['On Check','Fixed','Invalid'])->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('stock_opname_id');
            $table->uuid('product_id');
            $table->double('system_qty',20,2)->default(0);
            $table->double('actual_qty',20,2)->default(0);
            $table->double('discrepancy_qty',20,2)->default(0);
            $table->string('hpp')->nullable();
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
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};
