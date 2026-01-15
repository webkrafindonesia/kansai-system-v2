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
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('purchase_no');
            $table->string('deliveryorder_no')->nullable();
            $table->date('date');
            $table->uuid('supplier_id');
            $table->uuid('warehouse_id');
            $table->text('notes')->nullable();
            $table->boolean('is_accepted')->default(false);
            $table->string('total_price')->default(0);
            $table->enum('payment_status',['Belum Lunas','Lunas'])->default('Belum Lunas');
            $table->timestamp('paid_at')->nullable();
            $table->uuid('paid_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('purchase_id');
            $table->uuid('product_id');
            $table->float('qty');
            $table->string('uom');
            $table->double('price',20,2)->default(0);
            $table->double('total_price',20,2)->default(0);
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
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
