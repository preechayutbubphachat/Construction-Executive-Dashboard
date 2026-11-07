<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_orders', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->unsignedInteger('vo_number');
            $table->string('reference_no', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('reason', 32)->default('CLIENT_REQUEST');
            $table->string('type', 16)->default('ADDITION');
            $table->string('status', 32)->default('SUBMITTED');

            // Dual impact assessment (signed values)
            $table->decimal('cost_impact', 15, 2)->default(0.00);
            $table->integer('time_impact_days')->default(0);

            // Snapshots recorded when the VO is applied to the contract
            $table->decimal('previous_budget_amount', 15, 2)->nullable();
            $table->decimal('new_budget_amount', 15, 2)->nullable();
            $table->date('previous_finish_date')->nullable();
            $table->date('new_finish_date')->nullable();

            // 3-level approval metadata
            $table->foreignId('site_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('site_verified_at')->nullable();
            $table->text('site_notes')->nullable();

            $table->foreignId('quantity_surveyor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('qs_verified_at')->nullable();
            $table->text('qs_notes')->nullable();

            $table->foreignId('project_director_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('director_approved_at')->nullable();
            $table->text('director_notes')->nullable();

            $table->text('rejection_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'vo_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
        });

        Schema::table('ipc_items', function (Blueprint $table): void {
            $table->string('variation_order_id', 36)->nullable()->after('wbs_task_id');
            $table->index('variation_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('ipc_items', function (Blueprint $table): void {
            $table->dropIndex(['variation_order_id']);
            $table->dropColumn('variation_order_id');
        });
        Schema::dropIfExists('variation_orders');
    }
};
