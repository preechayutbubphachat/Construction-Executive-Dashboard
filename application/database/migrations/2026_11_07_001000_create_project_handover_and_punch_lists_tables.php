<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_punch_items', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignUlid('wbs_task_id')->nullable()->constrained('wbs_tasks')->nullOnDelete();
            $table->string('punch_code', 40);
            $table->string('location_zone', 160);
            $table->string('discipline', 40)->default('ARCHITECTURAL'); // ARCHITECTURAL, STRUCTURAL, MEP, CIVIL_EXTERNAL, FIRE_SAFETY
            $table->string('severity', 40)->default('MINOR'); // MINOR, MAJOR, CRITICAL_HANDOVER_BLOCKER
            $table->text('description');
            $table->text('rectification_notes')->nullable();
            $table->string('status', 40)->default('OPEN'); // OPEN, RECTIFIED_PENDING_INSPECTION, VERIFIED_CLOSED
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('rectified_by')->nullable()->constrained('users');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('rectified_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'punch_code']);
            $table->index(['project_id', 'status']);
        });

        Schema::create('project_handover_certificates', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('certificate_number', 50);
            $table->string('certificate_type', 50); // TAKING_OVER_CERTIFICATE, DEFECTS_LIABILITY_CERTIFICATE
            $table->date('handover_date');
            $table->unsignedInteger('dlp_months')->default(12);
            $table->date('dlp_expiry_date');
            $table->decimal('final_contract_sum', 16, 2)->default(0);
            $table->decimal('total_approved_vo_amount', 16, 2)->default(0);
            $table->decimal('total_certified_ipc_amount', 16, 2)->default(0);
            $table->decimal('retention_held_total', 16, 2)->default(0);
            $table->decimal('retention_release_amount', 16, 2)->default(0);
            $table->string('status', 40)->default('ISSUED'); // ISSUED, RETENTION_RELEASED
            $table->text('scope_remarks')->nullable();
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('certified_by')->constrained('users');
            $table->foreignId('released_by')->nullable()->constrained('users');
            $table->timestamp('certified_at');
            $table->timestamp('retention_released_at')->nullable();
            $table->foreignUlid('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'certificate_number']);
            $table->index(['project_id', 'certificate_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_handover_certificates');
        Schema::dropIfExists('handover_punch_items');
    }
};
