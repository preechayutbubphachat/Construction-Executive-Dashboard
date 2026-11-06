<?php

namespace Tests\Feature;

use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\BaselineScheduleReadModel;
use App\Services\WbsTaskManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_receives_a_deterministic_project_scoped_baseline_schedule(): void
    {
        $project = $this->project('CED-PROJECT-A', 'CED-A');
        $otherProject = $this->project('CED-PROJECT-B', 'CED-B');
        $executive = User::factory()->create();
        $project->members()->attach($executive, ['role' => 'EXECUTIVE']);
        $tasks = app(WbsTaskManagement::class);
        $parent = $tasks->create($project, $executive, [
            ...$this->taskAttributes('2', 'Structure', '100.0000'),
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
        ]);
        $child = $tasks->create($project, $executive, [
            ...$this->taskAttributes('2.1', 'Install structure', '50.0000'),
            'parent_id' => $parent->id,
            'baseline_start_date' => '2026-06-10',
            'baseline_finish_date' => '2026-06-12',
        ]);
        $singleDay = $tasks->create($project, $executive, [
            ...$this->taskAttributes('1', 'Single-day inspection', '50.0000'),
            'baseline_start_date' => '2026-06-05',
            'baseline_finish_date' => '2026-06-05',
        ]);
        $nonBaseline = WbsTask::query()->create([
            'id' => 'NON-BASELINE-TASK',
            'project_id' => $project->id,
            ...$this->taskAttributes('0', 'Working forecast only', '100.0000'),
            'is_baseline' => false,
            'is_leaf' => true,
        ]);
        $otherTask = WbsTask::query()->create([
            'id' => 'OTHER-PROJECT-TASK',
            'project_id' => $otherProject->id,
            ...$this->taskAttributes('1', 'Other project secret', '100.0000'),
            'is_leaf' => true,
        ]);
        $this->progress($project, $child, $executive, 'PROGRESS-OLD', '2026-06-10', '20.00', '2026-06-10 08:00:00');
        $this->progress($project, $child, $executive, 'PROGRESS-LATEST-A', '2026-06-11', '40.00', '2026-06-11 09:00:00');
        $this->progress($project, $child, $executive, 'PROGRESS-LATEST-B', '2026-06-11', '60.00', '2026-06-11 09:00:00');

        $response = $this->actingAs($executive)
            ->getJson(route('projects.schedule.show', $project))
            ->assertOk()
            ->assertJsonPath('version', BaselineScheduleReadModel::VERSION)
            ->assertJsonPath('project.id', $project->id)
            ->assertJsonPath('schedule.0.id', $singleDay->id)
            ->assertJsonPath('schedule.0.parent_id', null)
            ->assertJsonPath('schedule.0.wbs_code', '1')
            ->assertJsonPath('schedule.0.name', 'Single-day inspection')
            ->assertJsonPath('schedule.0.baseline_start_date', '2026-06-05')
            ->assertJsonPath('schedule.0.baseline_finish_date', '2026-06-05')
            ->assertJsonPath('schedule.0.status', 'IN_PROGRESS')
            ->assertJsonPath('schedule.0.is_baseline', true)
            ->assertJsonPath('schedule.0.is_leaf', true)
            ->assertJsonPath('schedule.0.weight', '50.0000')
            ->assertJsonPath('schedule.0.latest_progress', null)
            ->assertJsonPath('schedule.1.id', $parent->id)
            ->assertJsonPath('schedule.1.is_leaf', false)
            ->assertJsonPath('schedule.1.weight', null)
            ->assertJsonPath('schedule.2.id', $child->id)
            ->assertJsonPath('schedule.2.parent_id', $parent->id)
            ->assertJsonPath('schedule.2.latest_progress.id', 'PROGRESS-LATEST-B')
            ->assertJsonPath('schedule.2.latest_progress.progress_date', '2026-06-11')
            ->assertJsonPath('schedule.2.latest_progress.progress_percent', '60.00')
            ->assertJsonPath('schedule.2.latest_progress.recorded_at', '2026-06-11T09:00:00Z')
            ->assertJsonMissing(['id' => $nonBaseline->id])
            ->assertJsonMissing(['id' => $otherTask->id])
            ->assertJsonMissing(['name' => 'Other project secret']);

        self::assertCount(3, $response->json('schedule'));
        self::assertSame('2026-06-10', $child->refresh()->baseline_start_date->toDateString());
        self::assertSame('2026-06-12', $child->baseline_finish_date->toDateString());
    }

    public function test_schedule_is_default_deny_for_guests_and_non_members(): void
    {
        $project = $this->project('CED-PROJECT-A', 'CED-A');
        $nonMember = User::factory()->create();

        $this->getJson(route('projects.schedule.show', $project))
            ->assertForbidden();

        $this->actingAs($nonMember)
            ->getJson(route('projects.schedule.show', $project))
            ->assertForbidden();
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

    private function progress(
        Project $project,
        WbsTask $task,
        User $recorder,
        string $id,
        string $progressDate,
        string $progressPercent,
        string $recordedAt,
    ): ProgressEntry {
        return ProgressEntry::query()->create([
            'id' => $id,
            'project_id' => $project->id,
            'task_id' => $task->id,
            'progress_date' => $progressDate,
            'progress_percent' => $progressPercent,
            'recorded_by' => $recorder->id,
            'recorded_at' => $recordedAt,
        ]);
    }
}
