<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permit_to_works', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->string('constraint_id', 36)->nullable();
            $table->unsignedInteger('ptw_number');
            $table->string('reference_no', 80);
            $table->string('permit_type', 32)->default('HOT_WORK');
            $table->string('work_title');
            $table->string('location_zone')->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->text('precautions_checklist')->nullable();
            $table->string('status', 24)->default('REQUESTED');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('safety_notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'ptw_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
            $table->foreign('constraint_id')->references('id')->on('project_constraints')->nullOnDelete();
        });

        Schema::create('hse_safety_logs', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->date('log_date');
            $table->string('record_type', 24)->default('TOOLBOX_TALK');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('attendees_count')->default(0);
            $table->decimal('lost_time_hours', 8, 2)->default(0.00);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['project_id', 'log_date']);
            $table->index(['project_id', 'record_type']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hse_safety_logs');
        Schema::dropIfExists('permit_to_works');
    }
};
