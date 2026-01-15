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
        Schema::table('products', function (Blueprint $table) {
            $table->index('code');
            $table->index('name');
            $table->index('types');
            $table->index('product_category_id');
        });

        Schema::table('product_products', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('product_breakdown_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_code_index');
            $table->dropIndex('products_name_index');
            $table->dropIndex('products_types_index');
            $table->dropIndex('products_product_category_id_index');
        });

        Schema::table('product_products', function (Blueprint $table) {
            $table->dropIndex('product_products_product_id_index');
            $table->dropIndex('product_products_product_breakdown_id_index');
        });
    }
};
