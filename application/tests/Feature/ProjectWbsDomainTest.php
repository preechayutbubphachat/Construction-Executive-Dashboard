<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\ProgressCalculator;
use App\Services\ProgressRecorder;
use App\Services\ProjectManagement;
use App\Services\WbsTaskManagement;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class ProjectWbsDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_metadata_lifecycle_identity_and_audit_are_enforced(): void
    {
        CarbonImmutable::setTestNow('2026-09-13T04:50:00Z');
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $executive = User::factory()->create();
        $project->members()->attach($executive, ['role' => 'EXECUTIVE']);

        $this->actingAs($executive)
            ->patchJson(route('projects.update', $project), [
                'name' => 'Renamed Project',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-12-15',
                'budget_amount' => '250000.00',
            ])
            ->assertOk()
            ->assertJsonPath('project.id', 'CED-PROJECT-001')
            ->assertJsonPath('project.code', 'CED-001')
            ->assertJsonPath('project.name', 'Renamed Project');

        $this->assertDatabaseHas('audit_events', [
            'project_id' => $project->id,
            'action' => 'project.metadata_updated',
            'auditable_id' => $project->id,
        ]);

        $this->actingAs($executive)
            ->patchJson(route('projects.update', $project->refresh()), [
                'baseline_start_date' => '2026-12-20',
                'baseline_finish_date' => '2026-12-10',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('project');

        $project->refresh()->code = 'CHANGED';
        try {
            $project->save();
            self::fail('Project identity accepted a code change.');
        } catch (LogicException $exception) {
            self::assertStringContainsString('id and code are immutable', $exception->getMessage());
        }

        $this->actingAs($executive)
            ->patchJson(route('projects.lifecycle.update', $project->refresh()), ['status' => 'ON_HOLD'])
            ->assertOk()
            ->assertJsonPath('project.status', 'ON_HOLD');
        $this->actingAs($executive)
            ->postJson(route('projects.archive', $project->refresh()))
            ->assertOk()
            ->assertJsonPath('project.status', 'ARCHIVED')
            ->assertJsonPath('project.archived_at', '2026-09-13T04:50:00.000000Z');

        $this->actingAs($executive)
            ->patchJson(route('projects.update', $project->refresh()), ['name' => 'Forbidden after archive'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('project');

        $project->refresh();
        self::assertSame('CED-PROJECT-001', $project->id);
        self::assertSame('CED-001', $project->code);
        self::assertSame('Renamed Project', $project->name);
        self::assertSame('ARCHIVED', $project->status);
        self::assertNotNull($project->archived_at);
        $this->assertDatabaseHas('audit_events', ['project_id' => $project->id, 'action' => 'project.lifecycle_changed']);
        $this->assertDatabaseHas('audit_events', ['project_id' => $project->id, 'action' => 'project.archived']);
        CarbonImmutable::setTestNow();
    }

    public function test_adjacency_hierarchy_rejects_self_cycles_cross_project_parents_and_invalid_domain_values(): void
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $otherProject = $this->project('CED-PROJECT-002', 'CED-002');
        $executive = User::factory()->create();
        $project->members()->attach($executive, ['role' => 'EXECUTIVE']);
        $tasks = app(WbsTaskManagement::class);
        $parent = $tasks->create($project, $executive, $this->taskAttributes('1', 'Parent WBS', '100.0000'));
        $child = $tasks->create($project, $executive, [
            ...$this->taskAttributes('1.1', 'Leaf task', '100.0000'),
            'parent_id' => $parent->id,
        ]);
        $otherParent = WbsTask::query()->create([
            'id' => 'OTHER-PARENT',
            'project_id' => $otherProject->id,
            ...$this->taskAttributes('1', 'Other project parent', '100.0000'),
            'is_leaf' => true,
        ]);

        $parent->refresh();
        self::assertFalse($parent->is_leaf);
        self::assertNull($parent->weight);

        $this->assertDomainRejected(
            fn () => $tasks->update($project, $child->refresh(), $executive, ['parent_id' => $child->id]),
            'own parent',
        );
        $this->assertDomainRejected(
            fn () => $tasks->update($project, $parent->refresh(), $executive, ['parent_id' => $child->id]),
            'cycle',
        );
        $this->assertDomainRejected(
            fn () => $tasks->update($project, $child->refresh(), $executive, ['parent_id' => $otherParent->id]),
            'same project',
        );
        $this->assertDomainRejected(
            fn () => $tasks->update($project, $child->refresh(), $executive, [
                'baseline_start_date' => '2026-08-01',
                'baseline_finish_date' => '2026-07-01',
            ]),
            'finish date',
        );
        $this->assertDomainRejected(
            fn () => $tasks->update($project, $child->refresh(), $executive, ['weight' => '101.0000']),
            'no more than 100',
        );

        $completed = $tasks->update($project, $child->refresh(), $executive, ['status' => 'COMPLETED']);
        self::assertSame('COMPLETED', $completed->status);
        $this->assertDomainRejected(
            fn () => $tasks->update($project, $completed->refresh(), $executive, ['status' => 'IN_PROGRESS']),
            'not allowed',
        );
        $this->assertDomainRejected(
            fn () => app(ProjectManagement::class)->transition($project->refresh(), $executive, 'COMPLETED'),
            'remain active',
        );
        $parent = $tasks->update($project, $parent->refresh(), $executive, ['status' => 'COMPLETED']);
        $completedProject = app(ProjectManagement::class)->transition($project->refresh(), $executive, 'COMPLETED');
        self::assertSame('COMPLETED', $parent->status);
        self::assertSame('COMPLETED', $completedProject->status);
        $this->assertDomainRejected(
            fn () => $tasks->update($completedProject, $completed, $executive, ['name' => 'Late edit']),
            'cannot change',
        );

        $this->assertDatabaseHas('audit_events', [
            'project_id' => $project->id,
            'action' => 'wbs_task.updated',
            'auditable_id' => $child->id,
        ]);
        self::assertSame($project->id, $child->refresh()->project_id);
        self::assertSame($parent->id, $child->parent_id);
    }

    public function test_append_only_versioned_progress_returns_data_incomplete_for_mixed_leaf_weights_without_double_counting(): void
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $executive = User::factory()->create();
        $project->members()->attach($executive, ['role' => 'EXECUTIVE']);
        $tasks = app(WbsTaskManagement::class);
        $parent = $tasks->create($project, $executive, $this->taskAttributes('1', 'Parent WBS', '100.0000'));
        $leafA = $tasks->create($project, $executive, [
            ...$this->taskAttributes('1.1', 'Leaf A', '60.0000'),
            'parent_id' => $parent->id,
        ]);
        $leafB = $tasks->create($project, $executive, [
            ...$this->taskAttributes('1.2', 'Leaf B', null),
            'parent_id' => $parent->id,
        ]);
        $progress = app(ProgressRecorder::class);
        $entryA = $progress->record($project, $leafA, $executive, [
            'progress_date' => '2026-06-15',
            'progress_percent' => '50.00',
        ]);
        $entryB = $progress->record($project, $leafB, $executive, [
            'progress_date' => '2026-06-15',
            'progress_percent' => '25.00',
        ]);
        $calculator = app(ProgressCalculator::class);
        $taskSet = collect([$parent->refresh(), $leafA->refresh(), $leafB->refresh()]);
        $entrySet = collect([$entryA, $entryB]);
        $evaluatedAt = CarbonImmutable::parse('2026-06-15T12:00:00Z');

        $incompleteActual = $calculator->actual($taskSet, $entrySet, $evaluatedAt);
        $incompletePlanned = $calculator->planned($taskSet, $evaluatedAt);

        self::assertSame(ProgressCalculator::ACTUAL_VERSION, $incompleteActual['version']);
        self::assertSame(ProgressCalculator::PLANNED_VERSION, $incompletePlanned['version']);
        self::assertSame('DATA_INCOMPLETE', $incompleteActual['data_status']);
        self::assertSame('DATA_INCOMPLETE', $incompletePlanned['data_status']);
        self::assertNull($incompleteActual['value']);
        self::assertNull($incompletePlanned['value']);
        self::assertSame([$leafA->id, $leafB->id], $incompleteActual['source_scope']['task_ids']);
        self::assertNotContains($parent->id, $incompleteActual['source_scope']['task_ids']);

        $leafB = $tasks->update($project, $leafB, $executive, ['weight' => '40.0000']);
        $completeActual = $calculator->actual(
            collect([$parent->refresh(), $leafA->refresh(), $leafB->refresh()]),
            $entrySet,
            $evaluatedAt,
        );

        self::assertSame('COMPLETE', $completeActual['data_status']);
        self::assertSame(40.0, $completeActual['value']);
        self::assertCount(2, $completeActual['source_scope']['progress_entry_ids']);
        $this->assertDatabaseHas('audit_events', ['action' => 'progress.recorded', 'auditable_id' => $entryA->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'progress.recorded', 'auditable_id' => $entryB->id]);

        $this->expectException(LogicException::class);
        $entryA->forceFill(['progress_percent' => '99.00'])->save();
    }

    public function test_database_composite_constraints_reject_cross_project_parent_and_progress_assignment(): void
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $otherProject = $this->project('CED-PROJECT-002', 'CED-002');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $otherTask = WbsTask::query()->create([
            'id' => 'OTHER-TASK',
            'project_id' => $otherProject->id,
            ...$this->taskAttributes('1', 'Other task', '100.0000'),
            'is_leaf' => true,
        ]);

        try {
            DB::table('wbs_tasks')->insert([
                'id' => 'CROSS-PARENT-TASK',
                'project_id' => $project->id,
                'parent_id' => $otherTask->id,
                'wbs_code' => '9.1',
                'name' => 'Invalid cross-project child',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-12-31',
                'weight' => 100,
                'status' => 'NOT_STARTED',
                'is_baseline' => true,
                'is_leaf' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            self::fail('Database accepted a cross-project parent assignment.');
        } catch (QueryException) {
            $this->assertDatabaseMissing('wbs_tasks', ['id' => 'CROSS-PARENT-TASK']);
        }

        try {
            DB::table('progress_entries')->insert([
                'id' => 'CROSS-PROGRESS',
                'project_id' => $project->id,
                'task_id' => $otherTask->id,
                'progress_date' => '2026-06-01',
                'progress_percent' => 10,
                'recorded_by' => $member->id,
                'recorded_at' => now(),
            ]);
            self::fail('Database accepted cross-project progress.');
        } catch (QueryException) {
            $this->assertDatabaseMissing('progress_entries', ['id' => 'CROSS-PROGRESS']);
        }
    }

    public function test_rm03_migration_rolls_back_and_reapplies_on_the_isolated_test_database(): void
    {
        $migration = require database_path('migrations/2026_09_13_044122_extend_projects_and_wbs_tasks_for_rm03.php');

        $migration->down();
        self::assertFalse(Schema::hasColumn('projects', 'archived_at'));

        $migration->up();
        self::assertTrue(Schema::hasColumn('projects', 'archived_at'));

        $project = $this->project('CED-PROJECT-MIGRATION', 'CED-MIGRATION');
        $task = WbsTask::query()->create([
            'id' => 'MIGRATION-TASK',
            'project_id' => $project->id,
            ...$this->taskAttributes('1', 'Nullable weight task', null),
            'is_leaf' => true,
        ]);

        self::assertNull($task->weight);
    }

    private function project(string $id, string $code): Project
    {
        return Project::query()->create([
            'id' => $id,
            'code' => $code,
            'name' => "Project {$code}",
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);
    }

    private function taskAttributes(string $code, string $name, ?string $weight): array
    {
        return [
            'wbs_code' => $code,
            'name' => $name,
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'weight' => $weight,
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
        ];
    }

    private function assertDomainRejected(callable $operation, string $messageFragment): void
    {
        try {
            $operation();
            self::fail("Domain mutation was accepted; expected {$messageFragment} rejection.");
        } catch (DomainException $exception) {
            self::assertStringContainsString($messageFragment, $exception->getMessage());
        }
    }
}
