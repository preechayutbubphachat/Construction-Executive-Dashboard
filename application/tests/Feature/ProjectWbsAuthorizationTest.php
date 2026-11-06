<?php

namespace Tests\Feature;

use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWbsAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_projects_are_isolated_for_reads_writes_nested_resources_and_audits(): void
    {
        $projectA = $this->project('CED-PROJECT-A', 'CED-A');
        $projectB = $this->project('CED-PROJECT-B', 'CED-B');
        $executiveA = User::factory()->create();
        $executiveB = User::factory()->create();
        $viewerA = User::factory()->create();
        $projectA->members()->attach($executiveA, ['role' => 'EXECUTIVE']);
        $projectA->members()->attach($viewerA, ['role' => 'VIEWER']);
        $projectB->members()->attach($executiveB, ['role' => 'EXECUTIVE']);
        $taskA = $this->task($projectA, 'TASK-A', '1.0', 'Project A task');
        $taskB = $this->task($projectB, 'TASK-B', '1.0', 'Project B secret task');
        ProgressEntry::query()->create([
            'id' => 'PROGRESS-A',
            'project_id' => $projectA->id,
            'task_id' => $taskA->id,
            'progress_date' => '2026-06-01',
            'progress_percent' => '25.00',
            'recorded_by' => $executiveA->id,
            'recorded_at' => '2026-06-01 12:00:00',
        ]);

        $ownRead = $this->actingAs($executiveA)->getJson(route('projects.wbs.show', $projectA));
        $ownRead->assertOk()
            ->assertJsonPath('project.id', $projectA->id)
            ->assertJsonPath('wbs.0.id', $taskA->id)
            ->assertJsonPath('wbs.0.latest_progress.id', 'PROGRESS-A')
            ->assertJsonMissing(['id' => $taskB->id])
            ->assertJsonMissing(['name' => 'Project B secret task']);

        $this->actingAs($executiveA)
            ->getJson(route('projects.wbs.show', $projectB))
            ->assertForbidden();
        $this->actingAs($executiveA)
            ->patchJson(route('projects.update', $projectB), ['name' => 'Leaked write'])
            ->assertForbidden();
        $this->actingAs($executiveA)
            ->postJson(route('projects.wbs-tasks.store', $projectB), $this->taskPayload('2.0', 'Leaked task'))
            ->assertForbidden();

        $this->actingAs($executiveA)
            ->patchJson(route('projects.wbs-tasks.update', [$projectA, $taskB]), ['name' => 'Cross-project mutation'])
            ->assertNotFound();
        $this->actingAs($executiveA)
            ->postJson(route('projects.wbs-tasks.progress.store', [$projectA, $taskB]), [
                'progress_date' => '2026-06-02',
                'progress_percent' => '50.00',
            ])
            ->assertNotFound();

        $this->actingAs($viewerA)
            ->getJson(route('projects.wbs.show', $projectA))
            ->assertOk();
        $this->actingAs($viewerA)
            ->patchJson(route('projects.update', $projectA), ['name' => 'Viewer write'])
            ->assertForbidden();

        $created = $this->actingAs($executiveA)
            ->postJson(route('projects.wbs-tasks.store', $projectA), [
                ...$this->taskPayload('1.1', 'Authorized child task'),
                'parent_id' => $taskA->id,
            ])
            ->assertCreated()
            ->assertJsonPath('task.project_id', $projectA->id)
            ->assertJsonPath('task.name', 'Authorized child task');
        $createdTaskId = $created->json('task.id');

        $recorded = $this->actingAs($executiveA)
            ->postJson(route('projects.wbs-tasks.progress.store', [$projectA, $createdTaskId]), [
                'progress_date' => '2026-06-02',
                'progress_percent' => '50.00',
                'note' => 'Authorized scoped update',
            ])
            ->assertCreated()
            ->assertJsonPath('progress_entry.project_id', $projectA->id)
            ->assertJsonPath('progress_entry.task_id', $createdTaskId)
            ->assertJsonPath('progress_entry.recorded_by', $executiveA->id);
        $recordedProgressId = $recorded->json('progress_entry.id');

        $this->actingAs($executiveA)
            ->getJson(route('projects.wbs.show', $projectA))
            ->assertOk()
            ->assertJsonPath('wbs.0.id', $taskA->id)
            ->assertJsonPath('wbs.0.children.0.id', $createdTaskId)
            ->assertJsonPath('wbs.0.children.0.latest_progress.id', $recordedProgressId)
            ->assertJsonMissing(['id' => $taskB->id]);

        $this->actingAs($executiveA)
            ->patchJson(route('projects.update', $projectA), ['name' => 'Project A updated'])
            ->assertOk();

        $this->assertDatabaseHas('wbs_tasks', [
            'id' => $createdTaskId,
            'project_id' => $projectA->id,
            'name' => 'Authorized child task',
        ]);
        $this->assertDatabaseHas('progress_entries', [
            'id' => $recordedProgressId,
            'project_id' => $projectA->id,
            'task_id' => $createdTaskId,
            'recorded_by' => $executiveA->id,
        ]);
        $this->assertDatabaseHas('projects', ['id' => $projectA->id, 'name' => 'Project A updated']);
        $this->assertDatabaseHas('projects', ['id' => $projectB->id, 'name' => 'Project CED-B']);
        $this->assertDatabaseHas('wbs_tasks', ['id' => $taskB->id, 'name' => 'Project B secret task']);
        $this->assertDatabaseMissing('progress_entries', [
            'project_id' => $projectA->id,
            'task_id' => $taskB->id,
        ]);
        self::assertGreaterThan(0, $projectA->auditEvents()->count());
        self::assertSame(0, $projectB->auditEvents()->count());
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

    private function task(Project $project, string $id, string $code, string $name): WbsTask
    {
        return WbsTask::query()->create([
            'id' => $id,
            'project_id' => $project->id,
            ...$this->taskPayload($code, $name),
            'is_leaf' => true,
        ]);
    }

    private function taskPayload(string $code, string $name): array
    {
        return [
            'wbs_code' => $code,
            'name' => $name,
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
        ];
    }
}
