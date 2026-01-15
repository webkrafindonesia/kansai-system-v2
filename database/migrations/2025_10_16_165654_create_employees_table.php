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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('address')->nullable();
            $table->date('join_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender',['Laki-laki','Perempuan'])->nullable();
            $table->double('weekly_salary',20,2)->default(0);
            $table->double('monthly_salary',20,2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id');
            $table->date('loan_date');
            $table->double('amount',20,2)->default(0);
            $table->double('remaining',20,2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('employee_loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id');
            $table->integer('employee_loan_id');
            $table->date('repayment_date');
            $table->double('amount',20,2)->default(0);
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
        Schema::dropIfExists('employee_loan_repayments');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('employees');
    }
};
