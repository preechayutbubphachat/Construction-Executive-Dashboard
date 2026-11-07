<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requisitions', function (Blueprint $table): void {
            $table->string('id', 36)->primary();
            $table->string('project_id', 36);
            $table->string('wbs_task_id', 36)->nullable();
            $table->string('constraint_id', 36)->nullable();
            $table->unsignedInteger('mr_number');
            $table->string('reference_no', 80);
            $table->string('material_name');
            $table->string('category', 32)->default('STEEL_REBAR');
            $table->string('unit', 24)->default('TON');
            $table->decimal('required_qty', 12, 2)->default(0.00);
            $table->decimal('delivered_qty', 12, 2)->default(0.00);
            $table->decimal('unit_cost', 15, 2)->default(0.00);
            $table->decimal('total_estimated_cost', 15, 2)->default(0.00);
            $table->string('supplier_name')->nullable();
            $table->string('po_number', 80)->nullable();
            $table->date('required_on_site_date');
            $table->string('status', 32)->default('REQUESTED');

            // Site delivery & QC inspection metadata
            $table->string('delivery_order_no', 100)->nullable();
            $table->string('mill_cert_ref', 120)->nullable();
            $table->text('inspection_notes')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'mr_number']);
            $table->unique(['project_id', 'reference_no']);
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'required_on_site_date']);
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreign('wbs_task_id')->references('id')->on('wbs_tasks')->nullOnDelete();
            $table->foreign('constraint_id')->references('id')->on('project_constraints')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requisitions');
    }
};
