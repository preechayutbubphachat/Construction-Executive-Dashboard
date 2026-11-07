<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_conformance_reports', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->unsignedInteger('ncr_number');
            $table->string('reference_no', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 32)->default('WORKMANSHIP');
            $table->string('severity', 16)->default('MAJOR');
            $table->string('status', 32)->default('OPEN');
            $table->boolean('hold_payment')->default(true);
            $table->string('location_zone')->nullable();

            // CAPA / Rectification details submitted by contractor
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('preventive_action')->nullable();
            $table->foreignId('rectification_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rectification_submitted_at')->nullable();

            // QC Inspector / PM verification & close-out
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'ncr_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'wbs_task_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_conformance_reports');
    }
};
