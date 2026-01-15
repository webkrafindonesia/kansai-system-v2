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
        Schema::create('employee_payrolls', function (Blueprint $table) {
            $table->id();
            $table->uuid('employee_id');
            $table->enum('type',['Mingguan','Bulanan'])->default('Mingguan');
            $table->date('payroll_date');
            $table->double('amount',20,2)->default(0);
            $table->double('amount_loan_repayment',20,2)->default(0);
            $table->double('amount_after_loan_repayment',20,2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::table('employee_loan_repayments', function (Blueprint $table) {
            $table->integer('employee_payroll_id')->after('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_loan_repayments', function (Blueprint $table) {
            $table->dropColumn('employee_payroll_id');
        });
        Schema::dropIfExists('employee_payrolls');
    }
};
