<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interim_payment_certificates', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->unsignedInteger('certificate_number');
            $table->string('reference_no', 80);
            $table->string('contractor_name');
            $table->string('contractor_invoice_no')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 32)->default('DRAFT');

            // Financial Calculations
            $table->decimal('gross_work_amount', 15, 2)->default(0.00);
            $table->decimal('previous_certified_amount', 15, 2)->default(0.00);
            $table->decimal('current_work_amount', 15, 2)->default(0.00);
            $table->decimal('retention_rate_percent', 5, 2)->default(5.00);
            $table->decimal('retention_deduction', 15, 2)->default(0.00);
            $table->decimal('advance_recovery_deduction', 15, 2)->default(0.00);
            $table->decimal('other_deductions', 15, 2)->default(0.00);
            $table->decimal('net_amount_before_vat', 15, 2)->default(0.00);
            $table->decimal('vat_rate_percent', 5, 2)->default(7.00);
            $table->decimal('vat_amount', 15, 2)->default(0.00);
            $table->decimal('net_payable_amount', 15, 2)->default(0.00);

            // 3-Level Approval Metadata
            $table->foreignId('site_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('site_verified_at')->nullable();
            $table->text('site_notes')->nullable();

            $table->foreignId('quantity_surveyor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('qs_verified_at')->nullable();
            $table->text('qs_notes')->nullable();

            $table->foreignId('project_director_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('director_approved_at')->nullable();
            $table->text('director_notes')->nullable();

            $table->string('finance_transaction_id', 36)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'certificate_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('finance_transaction_id')->references('id')->on('finance_transactions')->nullOnDelete();
        });

        Schema::create('ipc_items', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('ipc_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->string('description');
            $table->decimal('contract_amount', 15, 2)->default(0.00);
            $table->decimal('previous_percent', 5, 2)->default(0.00);
            $table->decimal('current_percent', 5, 2)->default(0.00);
            $table->decimal('this_period_percent', 5, 2)->default(0.00);
            $table->decimal('claimed_amount', 15, 2)->default(0.00);
            $table->decimal('certified_amount', 15, 2)->default(0.00);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['ipc_id', 'wbs_task_id']);
            $table->foreign('ipc_id')->references('id')->on('interim_payment_certificates')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ipc_items');
        Schema::dropIfExists('interim_payment_certificates');
    }
};
