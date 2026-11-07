<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_manpower_logs', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->string('constraint_id', 36)->nullable();
            $table->date('log_date');
            $table->string('trade', 32)->default('GENERAL_LABOR');
            $table->string('contractor_company')->nullable();
            $table->unsignedInteger('planned_headcount')->default(0);
            $table->unsignedInteger('actual_headcount')->default(0);
            $table->decimal('working_hours', 5, 2)->default(8.00);
            $table->decimal('total_man_hours', 10, 2)->default(0.00);
            $table->string('shift', 16)->default('DAY');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['project_id', 'log_date']);
            $table->index(['project_id', 'wbs_task_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
            $table->foreign('constraint_id')->references('id')->on('project_constraints')->nullOnDelete();
        });

        Schema::create('site_machinery_logs', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->string('constraint_id', 36)->nullable();
            $table->date('log_date');
            $table->string('equipment_code', 64);
            $table->string('equipment_name');
            $table->string('category', 32)->default('EXCAVATOR');
            $table->string('status', 24)->default('OPERATIONAL');
            $table->decimal('operating_hours', 5, 2)->default(0.00);
            $table->decimal('downtime_hours', 5, 2)->default(0.00);
            $table->string('operator_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['project_id', 'log_date']);
            $table->index(['project_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
            $table->foreign('constraint_id')->references('id')->on('project_constraints')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_machinery_logs');
        Schema::dropIfExists('site_manpower_logs');
    }
};
