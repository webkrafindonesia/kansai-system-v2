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
        Schema::create('assemblies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->date('assembly_date');
            $table->enum('status',['Draft','In Progress','Done'])->default('Draft');
            $table->uuid('sales_order_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('assembly_items', function (Blueprint $table) {
            $table->id();
            $table->string('assembly_id');
            $table->uuid('product_id')->nullable();
            $table->float('qty')->default(0);
            $table->string('uom');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('assembly_item_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->string('assembly_id');
            $table->string('assembly_item_id');
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
        Schema::dropIfExists('assembly_item_breakdowns');
        Schema::dropIfExists('assembly_items');
        Schema::dropIfExists('assemblies');
    }
};
