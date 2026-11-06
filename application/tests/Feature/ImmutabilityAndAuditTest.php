<?php

namespace Tests\Feature;

use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ImmutabilityAndAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_and_wbs_task_identity_fields_are_immutable(): void
    {
        $project = $this->project();
        $project->code = 'CHANGED';

        try {
            $project->save();
            self::fail('Project identity fields were mutable.');
        } catch (LogicException $exception) {
            self::assertStringContainsString('id and code are immutable', $exception->getMessage());
        }

        $task = WbsTask::query()->create([
            'id' => 'CED-TASK-001',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Baseline leaf',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);
        $task->id = 'CED-TASK-CHANGED';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('WbsTask identifiers are immutable.');
        $task->save();
    }

    public function test_progress_history_and_audit_events_reject_update_and_delete(): void
    {
        $project = $this->project();
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => 'EXECUTIVE']);
        $task = WbsTask::query()->create([
            'id' => 'CED-TASK-001',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Baseline leaf',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);
        $progress = ProgressEntry::query()->create([
            'id' => 'CED-PROGRESS-001',
            'project_id' => $project->id,
            'task_id' => $task->id,
            'progress_date' => '2026-06-10',
            'progress_percent' => '25.00',
            'recorded_by' => $user->id,
            'recorded_at' => '2026-06-10 10:00:00',
        ]);
        $audit = app(AuditRecorder::class)->record($project->id, $user, 'progress.recorded', $progress);

        foreach ([
            fn () => $progress->forceFill(['progress_percent' => '99.00'])->save(),
            fn () => $progress->delete(),
            fn () => $audit->forceFill(['action' => 'tampered'])->save(),
            fn () => $audit->delete(),
        ] as $mutation) {
            try {
                $mutation();
                self::fail('An append-only record accepted mutation.');
            } catch (LogicException $exception) {
                self::assertStringContainsString('append-only', $exception->getMessage());
            }
        }

        $this->assertDatabaseHas('progress_entries', [
            'id' => 'CED-PROGRESS-001',
            'progress_percent' => 25,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'id' => $audit->id,
            'action' => 'progress.recorded',
        ]);
    }

    private function project(): Project
    {
        return Project::query()->create([
            'id' => 'CED-PROJECT-001',
            'code' => 'CED-001',
            'name' => 'Immutable Project',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '1000.00',
            'status' => 'ACTIVE',
        ]);
    }
}
