<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\WeeklyLookaheadService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyLookaheadServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookahead_calculates_correct_window_and_buckets(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-WLS-1',
            'name' => 'WLS Test Project',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-08-31',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);

        $service = new WeeklyLookaheadService;
        $anchor = Carbon::parse('2026-05-11'); // Monday

        $result3Weeks = $service->forProject($project, 3, $anchor);
        $this->assertSame(3, $result3Weeks['window']['weeks_count']);
        $this->assertSame('2026-05-11', $result3Weeks['window']['start_date']);
        $this->assertSame('2026-05-31', $result3Weeks['window']['end_date']);
        $this->assertCount(3, $result3Weeks['weeks']);

        $result6Weeks = $service->forProject($project, 6, $anchor);
        $this->assertSame(6, $result6Weeks['window']['weeks_count']);
        $this->assertSame('2026-05-11', $result6Weeks['window']['start_date']);
        $this->assertSame('2026-06-21', $result6Weeks['window']['end_date']);
        $this->assertCount(6, $result6Weeks['weeks']);
    }

    public function test_lookahead_identifies_overlapping_tasks_and_evaluates_make_ready_status(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-WLS-2',
            'name' => 'WLS Overlap Project',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-08-31',
            'budget_amount' => '200000.00',
            'status' => 'ACTIVE',
        ]);

        $creator = User::factory()->create();

        // Task 1: Inside Week 1 (May 12 - May 15) - Sound (no constraints)
        $task1 = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Footing Reinforcement',
            'baseline_start_date' => '2026-05-12',
            'baseline_finish_date' => '2026-05-15',
            'planned_cost' => '20000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        // Task 2: Starts before window, ends inside Week 1 (May 05 - May 14) - Constrained by open critical constraint
        $task2 = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.2',
            'name' => 'Excavation Shoring',
            'baseline_start_date' => '2026-05-05',
            'baseline_finish_date' => '2026-05-14',
            'planned_cost' => '30000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'task_id' => $task2->id,
            'type' => ProjectConstraint::TYPE_PERMIT,
            'title' => 'Shoring inspection permit required',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $creator->id,
        ]);

        // Task 3: In Week 2 (May 19 - May 22) - Has a resolved constraint -> Still Make Ready
        $task3 = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.3',
            'name' => 'Column Pouring',
            'baseline_start_date' => '2026-05-19',
            'baseline_finish_date' => '2026-05-22',
            'planned_cost' => '25000.00',
            'weight' => '1.00',
            'status' => 'NOT_STARTED',
            'is_leaf' => true,
        ]);

        ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'task_id' => $task3->id,
            'type' => ProjectConstraint::TYPE_MATERIAL,
            'title' => 'Column rebar delivery delay',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_notes' => 'Shipment arrived on site',
            'created_by' => $creator->id,
        ]);

        // Task 4: Far outside window (July 01 - July 15) -> Should be excluded from 3-week lookahead
        WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '2.1',
            'name' => 'Roof Truss Installation',
            'baseline_start_date' => '2026-07-01',
            'baseline_finish_date' => '2026-07-15',
            'planned_cost' => '40000.00',
            'weight' => '1.00',
            'status' => 'NOT_STARTED',
            'is_leaf' => true,
        ]);

        $service = new WeeklyLookaheadService;
        $anchor = Carbon::parse('2026-05-11');
        $lookahead = $service->forProject($project, 3, $anchor);

        $this->assertSame(3, $lookahead['summary']['total_tasks']);
        $this->assertSame(2, $lookahead['summary']['make_ready_count']);
        $this->assertSame(1, $lookahead['summary']['constrained_count']);
        $this->assertSame(1, $lookahead['summary']['critical_blockers_count']);
        $this->assertEqualsWithDelta(66.7, (float) $lookahead['summary']['make_ready_rate'], 0.1);

        $taskItems = collect($lookahead['tasks'])->keyBy('id');
        $this->assertTrue($taskItems->get($task1->id)['is_make_ready']);
        $this->assertFalse($taskItems->get($task2->id)['is_make_ready']);
        $this->assertCount(1, $taskItems->get($task2->id)['open_constraints']);
        $this->assertTrue($taskItems->get($task3->id)['is_make_ready']);
    }

    public function test_empty_schedule_returns_safe_zero_metrics(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-WLS-EMPTY',
            'name' => 'Empty Project',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-08-31',
            'budget_amount' => '50000.00',
            'status' => 'ACTIVE',
        ]);

        $service = new WeeklyLookaheadService;
        $lookahead = $service->forProject($project, 3, Carbon::parse('2026-05-11'));

        $this->assertSame(0, $lookahead['summary']['total_tasks']);
        $this->assertSame(0, $lookahead['summary']['make_ready_count']);
        $this->assertSame(0, $lookahead['summary']['constrained_count']);
        $this->assertSame(100.0, (float) $lookahead['summary']['make_ready_rate']);
        $this->assertEmpty($lookahead['tasks']);
    }
}
