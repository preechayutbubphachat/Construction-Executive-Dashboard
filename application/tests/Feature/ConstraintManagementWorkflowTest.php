<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\ConstraintManagement;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConstraintManagementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_constraint_can_be_created_and_logged_to_audit_trail(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-WORKFLOW-001',
            'code' => 'WORKFLOW-001',
            'name' => 'Workflow Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'PROJECT_MANAGER']);

        $task = WbsTask::query()->create([
            'id' => 'TASK-WORKFLOW-001',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Column Pouring',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-02-01',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $service = app(ConstraintManagement::class);

        $constraint = $service->create($project, $member, [
            'task_id' => $task->id,
            'type' => 'DESIGN_RFI',
            'title' => 'Structural beam dimension conflict',
            'description' => 'Architectural drawing differs from structural drawing.',
            'severity' => 'HIGH',
            'target_resolution_date' => '2026-01-20',
        ]);

        self::assertNotNull($constraint);
        self::assertSame('OPEN', $constraint->status);
        self::assertSame('DESIGN_RFI', $constraint->type);

        // Verify audit event
        $audit = AuditEvent::query()->where('auditable_id', $constraint->id)->first();
        self::assertNotNull($audit);
        self::assertSame('constraint.created', $audit->action);
        self::assertSame($project->id, $audit->project_id);
        self::assertSame($member->id, $audit->actor_id);
    }

    public function test_cross_project_task_assignment_is_rejected(): void
    {
        $project1 = Project::query()->create([
            'id' => 'PRJ-WORKFLOW-002',
            'code' => 'WORKFLOW-002',
            'name' => 'Project 1',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);
        $project2 = Project::query()->create([
            'id' => 'PRJ-WORKFLOW-003',
            'code' => 'WORKFLOW-003',
            'name' => 'Project 2',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project1->members()->attach($member, ['role' => 'PROJECT_MANAGER']);

        $taskInOtherProject = WbsTask::query()->create([
            'id' => 'TASK-OTHER-001',
            'project_id' => $project2->id,
            'wbs_code' => '1.0',
            'name' => 'Other Project Task',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-02-01',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $service = app(ConstraintManagement::class);

        $this->expectException(DomainException::class);
        $service->create($project1, $member, [
            'task_id' => $taskInOtherProject->id,
            'type' => 'MATERIAL',
            'title' => 'Invalid task reference',
            'severity' => 'MEDIUM',
        ]);
    }

    public function test_constraint_can_be_resolved_with_notes_and_timestamp(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-WORKFLOW-004',
            'code' => 'WORKFLOW-004',
            'name' => 'Resolution Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'PROJECT_MANAGER']);

        $service = app(ConstraintManagement::class);

        $constraint = $service->create($project, $member, [
            'type' => 'INSPECTION',
            'title' => 'Municipal building permit inspection',
            'severity' => 'CRITICAL',
        ]);

        $resolved = $service->resolve($constraint, $member, 'Permit issued by city inspector.');

        self::assertSame('RESOLVED', $resolved->status);
        self::assertNotNull($resolved->resolved_at);
        self::assertSame('Permit issued by city inspector.', $resolved->resolution_notes);

        $audit = AuditEvent::query()
            ->where('auditable_id', $constraint->id)
            ->where('action', 'constraint.resolved')
            ->first();
        self::assertNotNull($audit);
    }
}
