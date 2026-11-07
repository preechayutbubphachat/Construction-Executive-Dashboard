<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\SiteMachineryLog;
use App\Models\SiteManpowerLog;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteWorkforceAndMachineryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $supervisor;

    private User $outsider;

    private WbsTask $task;

    private ProjectConstraint $laborConstraint;

    private ProjectConstraint $equipmentConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::query()->create([
            'name' => 'Site Supervisor',
            'email' => 'sup@res.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->outsider = User::query()->create([
            'name' => 'Outsider',
            'email' => 'out@res.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-RES-01',
            'name' => 'Main Station Roof Truss Assembly',
            'status' => 'ACTIVE',
            'budget_amount' => 10000000.00,
            'baseline_start_date' => CarbonImmutable::now()->subMonth()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(5)->toDateString(),
        ]);

        $this->project->members()->attach($this->supervisor, ['role' => 'SITE_SUPERVISOR']);

        $this->task = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '4.1',
            'name' => 'Heavy Steel Truss Erection',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->subWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(2)->toDateString(),
            'weight' => 35.0,
            'is_leaf' => true,
        ]);

        $this->laborConstraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'type' => ProjectConstraint::TYPE_LABOR_CREW,
            'title' => 'Shortage of Certified Structural Welders',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $this->supervisor->id,
        ]);

        $this->equipmentConstraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'type' => ProjectConstraint::TYPE_EQUIPMENT,
            'title' => 'Tower Crane TC-01 Hydraulic Pump Fault',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $this->supervisor->id,
        ]);
    }

    public function test_outsider_cannot_access_resources_routes(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('projects.resources.index', $this->project))
            ->assertStatus(403);
    }

    public function test_manpower_log_calculates_man_hours_and_auto_resolves_labor_constraint_when_target_met(): void
    {
        // 1. Partial crew (10 / 20 welders) -> does NOT resolve LABOR_CREW constraint
        $this->actingAs($this->supervisor)
            ->post(route('projects.resources.manpower.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'log_date' => '2026-11-06',
                'trade' => 'WELDER',
                'contractor_company' => 'Siam Steel Welders Co.',
                'planned_headcount' => 20,
                'actual_headcount' => 10,
                'working_hours' => 8.0,
                'shift' => 'DAY',
            ])->assertSessionHasNoErrors();

        $this->laborConstraint->refresh();
        $this->assertSame(ProjectConstraint::STATUS_OPEN, $this->laborConstraint->status);

        // 2. Full crew mobilized (22 / 20 welders, 10 hrs overtime shift = 220 man-hours) -> auto-resolves LABOR_CREW constraint!
        $this->actingAs($this->supervisor)
            ->post(route('projects.resources.manpower.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'log_date' => '2026-11-07',
                'trade' => 'WELDER',
                'contractor_company' => 'Siam Steel Welders Co.',
                'planned_headcount' => 20,
                'actual_headcount' => 22,
                'working_hours' => 10.0,
                'shift' => 'DAY',
            ])->assertSessionHasNoErrors();

        $latestLog = SiteManpowerLog::query()->orderByDesc('log_date')->firstOrFail();
        $this->assertEquals(220.00, (float) $latestLog->total_man_hours);
        $this->assertEquals(110.0, $latestLog->attendancePercent());

        $this->laborConstraint->refresh();
        $this->assertSame(ProjectConstraint::STATUS_RESOLVED, $this->laborConstraint->status);
        $this->assertNotNull($this->laborConstraint->resolved_at);
        // Equipment constraint remains untouched
        $this->assertSame(ProjectConstraint::STATUS_OPEN, $this->equipmentConstraint->refresh()->status);
    }

    public function test_machinery_log_tracks_availability_and_auto_resolves_equipment_constraint_when_operational(): void
    {
        // 1. Logged as BREAKDOWN -> does NOT resolve EQUIPMENT constraint
        $this->actingAs($this->supervisor)
            ->post(route('projects.resources.machinery.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'log_date' => '2026-11-06',
                'equipment_code' => 'tc-01',
                'equipment_name' => 'Potain Tower Crane MC310',
                'category' => 'TOWER_CRANE',
                'status' => 'BREAKDOWN',
                'operating_hours' => 0.0,
                'downtime_hours' => 8.0,
            ])->assertSessionHasNoErrors();

        $this->equipmentConstraint->refresh();
        $this->assertSame(ProjectConstraint::STATUS_OPEN, $this->equipmentConstraint->status);

        // 2. Repaired and logged as OPERATIONAL with 8 operating hours -> auto-resolves EQUIPMENT constraint!
        $this->actingAs($this->supervisor)
            ->post(route('projects.resources.machinery.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'log_date' => '2026-11-07',
                'equipment_code' => 'tc-01',
                'equipment_name' => 'Potain Tower Crane MC310',
                'category' => 'TOWER_CRANE',
                'status' => 'OPERATIONAL',
                'operating_hours' => 8.0,
                'downtime_hours' => 2.0,
            ])->assertSessionHasNoErrors();

        $latestEq = SiteMachineryLog::query()->orderByDesc('log_date')->firstOrFail();
        $this->assertSame('TC-01', $latestEq->equipment_code);
        $this->assertEquals(80.0, $latestEq->availabilityPercent());

        $this->equipmentConstraint->refresh();
        $this->assertSame(ProjectConstraint::STATUS_RESOLVED, $this->equipmentConstraint->status);
        $this->assertNotNull($this->equipmentConstraint->resolved_at);
    }
}
