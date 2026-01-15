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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->string('uom')->nullable();
            $table->text('specification')->nullable();
            $table->string('types')->nullable();
            $table->uuid('product_category_id')->nullable();
            $table->decimal('buying_price',20,2)->default(0);
            $table->decimal('selling_price',20,2)->default(0);
            $table->float('safety_stock')->default(0);
            $table->boolean('purchasable')->default(1);
            $table->boolean('is_active')->default(1);

            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('product_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('product_id');
            $table->uuid('product_breakdown_id');
            $table->float('qty');
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
        Schema::dropIfExists('product_products');
        Schema::dropIfExists('products');
    }
};
