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
        Schema::create('product_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->datetime('date')->nullable();
            $table->string('description')->nullable();
            $table->uuid('product_id');
            $table->float('qty')->default(0);
            $table->string('uom')->default(0);
            $table->string('total_nominal')->default(0); // sudah dikalikan dengan qty
            $table->string('types'); // ex.: mutation, adjustment, production, etc.
            $table->uuid('warehouse_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('reference_id')->nullable();
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
        Schema::dropIfExists('product_histories');
    }
};
