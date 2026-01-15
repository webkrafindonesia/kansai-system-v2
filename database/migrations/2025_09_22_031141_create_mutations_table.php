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
        Schema::create('mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->datetime('date');
            $table->string('origin');
            $table->string('destination');
            $table->string('reference')->nullable();
            $table->boolean('is_processed')->default(0);

            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('mutation_items', function (Blueprint $table) {
            $table->id()->primary();
            $table->uuid('mutation_id');
            $table->uuid('product_id');
            $table->float('qty')->default(0);
            $table->string('uom')->default(0);

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
        Schema::dropIfExists('mutation_items');
        Schema::dropIfExists('mutations');
    }
};
