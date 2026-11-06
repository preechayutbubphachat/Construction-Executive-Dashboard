<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectConstraintModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_constraint_can_be_created_and_accessed_via_relations(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-CONST-001',
            'code' => 'CONST-001',
            'name' => 'Constraint Test Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);

        $task = WbsTask::query()->create([
            'id' => 'TASK-CONST-001',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Excavation',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-02-01',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $creator = User::factory()->create();
        $assignee = User::factory()->create();

        $constraint = ProjectConstraint::query()->create([
            'id' => 'CONST-001',
            'project_id' => $project->id,
            'task_id' => $task->id,
            'type' => 'MATERIAL',
            'title' => 'Ready-mix concrete shortage',
            'description' => 'Supplier delayed delivery due to batching plant outage.',
            'severity' => 'CRITICAL',
            'status' => 'OPEN',
            'target_resolution_date' => '2026-01-15',
            'assigned_to' => $assignee->id,
            'created_by' => $creator->id,
        ]);

        self::assertNotNull($constraint);
        self::assertSame('CONST-001', $constraint->id);
        self::assertTrue($constraint->project->is($project));
        self::assertTrue($constraint->task->is($task));
        self::assertTrue($constraint->creator->is($creator));
        self::assertTrue($constraint->assignee->is($assignee));

        // Test project and task inverse relationships
        self::assertTrue($project->constraints->contains($constraint));
        self::assertTrue($task->constraints->contains($constraint));

        // Test scopes
        self::assertSame(1, ProjectConstraint::query()->open()->count());
        self::assertSame(1, ProjectConstraint::query()->critical()->count());
    }
}
