<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engineering_rfis', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->string('constraint_id', 36)->nullable();
            $table->unsignedInteger('rfi_number');
            $table->string('reference_no', 80);
            $table->string('discipline', 32)->default('STRUCTURAL');
            $table->string('subject');
            $table->string('drawing_ref', 120)->nullable();
            $table->text('question');
            $table->text('proposed_solution')->nullable();
            $table->date('due_date');
            $table->string('status', 24)->default('OPEN');

            $table->text('official_response')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'rfi_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'due_date']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
            $table->foreign('constraint_id')->references('id')->on('project_constraints')->nullOnDelete();
        });

        Schema::create('engineering_submittals', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->unsignedInteger('submittal_number');
            $table->string('reference_no', 80);
            $table->string('submittal_type', 32)->default('SHOP_DRAWING');
            $table->string('discipline', 32)->default('STRUCTURAL');
            $table->string('title');
            $table->string('spec_section', 120)->nullable();
            $table->string('revision', 16)->default('Rev.0');
            $table->string('status', 32)->default('PENDING_REVIEW');

            $table->text('review_comments')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'submittal_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engineering_submittals');
        Schema::dropIfExists('engineering_rfis');
    }
};
