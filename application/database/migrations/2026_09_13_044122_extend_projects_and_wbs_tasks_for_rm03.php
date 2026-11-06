<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Schema::table('wbs_tasks', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->decimal('weight', 7, 4)->nullable()->change();
            $table->unique(['project_id', 'id'], 'wbs_tasks_project_identity_unique');
            $table->foreign(['project_id', 'parent_id'], 'wbs_tasks_project_id_parent_id_foreign')
                ->references(['project_id', 'id'])
                ->on('wbs_tasks')
                ->restrictOnDelete();
        });

        Schema::table('progress_entries', function (Blueprint $table): void {
            $table->dropForeign(['task_id']);
            $table->foreign(['project_id', 'task_id'], 'progress_entries_project_id_task_id_foreign')
                ->references(['project_id', 'id'])
                ->on('wbs_tasks')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('progress_entries', function (Blueprint $table): void {
            $table->dropForeign(['project_id', 'task_id']);
            $table->foreign('task_id')->references('id')->on('wbs_tasks')->cascadeOnDelete();
        });

        Schema::table('wbs_tasks', function (Blueprint $table): void {
            $table->dropForeign(['project_id', 'parent_id']);
            $table->dropUnique('wbs_tasks_project_identity_unique');
            $table->foreign('parent_id')->references('id')->on('wbs_tasks')->nullOnDelete();
        });

        DB::table('wbs_tasks')->whereNull('weight')->update(['weight' => 1]);

        Schema::table('wbs_tasks', function (Blueprint $table): void {
            $table->decimal('weight', 7, 4)->nullable(false)->change();
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });
    }
};
