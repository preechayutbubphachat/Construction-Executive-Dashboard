<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_documents', function (Blueprint $table): void {
            $table->string('wbs_task_id', 36)->nullable()->after('project_id');
            $table->string('constraint_id', 36)->nullable()->after('wbs_task_id');
            $table->string('caption', 255)->nullable()->after('original_name');
            $table->decimal('gps_latitude', 10, 7)->nullable()->after('caption');
            $table->decimal('gps_longitude', 10, 7)->nullable()->after('gps_latitude');
            $table->timestamp('captured_at')->nullable()->after('gps_longitude');
            $table->json('metadata')->nullable()->after('review_state');

            $table->index(['project_id', 'wbs_task_id']);
            $table->index(['project_id', 'constraint_id']);
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
            $table->foreign('constraint_id')->references('id')->on('project_constraints')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('evidence_documents', function (Blueprint $table): void {
            $table->dropForeign(['constraint_id']);
            $table->dropForeign(['wbs_task_id']);
            $table->dropIndex(['project_id', 'constraint_id']);
            $table->dropIndex(['project_id', 'wbs_task_id']);

            $table->dropColumn([
                'metadata',
                'captured_at',
                'gps_longitude',
                'gps_latitude',
                'caption',
                'constraint_id',
                'wbs_task_id',
            ]);
        });
    }
};
