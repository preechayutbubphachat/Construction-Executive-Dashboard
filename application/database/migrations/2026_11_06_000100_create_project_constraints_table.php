<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_constraints', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('task_id', 36)->nullable();
            $table->string('type', 32);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity', 16)->default('MEDIUM');
            $table->string('status', 16)->default('OPEN');
            $table->date('target_resolution_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['project_id', 'status', 'severity']);
            $table->index(['task_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_constraints');
    }
};
