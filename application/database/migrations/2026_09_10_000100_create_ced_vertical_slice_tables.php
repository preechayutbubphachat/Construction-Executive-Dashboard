<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->date('baseline_start_date');
            $table->date('baseline_finish_date');
            $table->decimal('budget_amount', 15, 2)->default(0);
            $table->string('status', 32)->default('ACTIVE');
            $table->timestamps();
        });

        Schema::create('project_members', function (Blueprint $table): void {
            $table->string('project_id', 36);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 40);
            $table->timestamps();
            $table->primary(['project_id', 'user_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });

        Schema::create('wbs_tasks', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('parent_id', 36)->nullable();
            $table->string('wbs_code', 40);
            $table->string('name');
            $table->date('baseline_start_date');
            $table->date('baseline_finish_date');
            $table->decimal('weight', 7, 4);
            $table->string('status', 32)->default('NOT_STARTED');
            $table->boolean('is_baseline')->default(true);
            $table->boolean('is_leaf')->default(true);
            $table->timestamps();
            $table->unique(['project_id', 'wbs_code']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('wbs_tasks')->nullOnDelete();
        });

        Schema::create('progress_entries', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('task_id', 36);
            $table->date('progress_date');
            $table->decimal('progress_percent', 5, 2);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamp('recorded_at');
            $table->text('note')->nullable();
            $table->index(['task_id', 'progress_date']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('wbs_tasks')->cascadeOnDelete();
        });

        Schema::create('project_risks', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('title');
            $table->string('severity', 16);
            $table->string('status', 16)->default('OPEN');
            $table->timestamps();
            $table->index(['project_id', 'severity', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });

        Schema::create('finance_transactions', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('direction', 16);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('status', 16)->default('DRAFT');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'status', 'direction']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });

        Schema::create('evidence_documents', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('storage_disk', 32)->default('evidence');
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('byte_size');
            $table->char('sha256', 64);
            $table->string('access_class', 24)->default('PRIVATE');
            $table->string('review_state', 24)->default('UPLOADED');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->unique(['project_id', 'sha256']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });

        Schema::create('audit_events', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->foreignId('actor_id')->constrained('users');
            $table->string('action', 80);
            $table->string('auditable_type');
            $table->string('auditable_id', 36);
            $table->json('summary');
            $table->timestamp('occurred_at');
            $table->index(['project_id', 'occurred_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('evidence_documents');
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('project_risks');
        Schema::dropIfExists('progress_entries');
        Schema::dropIfExists('wbs_tasks');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('projects');
    }
};
