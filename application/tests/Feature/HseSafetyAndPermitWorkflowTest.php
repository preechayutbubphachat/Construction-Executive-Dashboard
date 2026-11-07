<?php

namespace Tests\Feature;

use App\Models\PermitToWork;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\SiteManpowerLog;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\HseSafetyOperationsService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HseSafetyAndPermitWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $supervisor;

    private User $safetyOfficer;

    private User $outsider;

    private WbsTask $task;

    private ProjectConstraint $permitConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supervisor = User::query()->create([
            'name' => 'Site Supervisor',
            'email' => 'sup@hse.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->safetyOfficer = User::query()->create([
            'name' => 'Safety Officer',
            'email' => 'safety@hse.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->outsider = User::query()->create([
            'name' => 'Outsider',
            'email' => 'out@hse.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-HSE-01',
            'name' => 'Deep Tunnel Shaft B Excavation',
            'status' => 'ACTIVE',
            'budget_amount' => 15000000.00,
            'baseline_start_date' => CarbonImmutable::now()->subMonth()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
        ]);

        $this->project->members()->attach($this->supervisor, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->safetyOfficer, ['role' => 'SAFETY_OFFICER']);

        $this->task = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '5.1',
            'name' => 'Confined Space Cross-Passage Welding',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->subWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(2)->toDateString(),
            'weight' => 30.0,
            'is_leaf' => true,
        ]);

        $this->permitConstraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'type' => ProjectConstraint::TYPE_PERMIT,
            'title' => 'Awaiting Confined Space & Hot Work Permit Approval',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $this->supervisor->id,
        ]);
    }

    public function test_outsider_cannot_access_hse_routes(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('projects.hse.index', $this->project))
            ->assertStatus(403);
    }

    public function test_permit_to_work_approval_enforces_maker_checker_and_auto_resolves_permit_constraint(): void
    {
        // 1. Supervisor requests Permit-to-Work
        $this->actingAs($this->supervisor)
            ->post(route('projects.hse.permits.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'permit_type' => 'CONFINED_SPACE',
                'work_title' => 'Cross-Passage Steel Liner Welding',
                'location_zone' => 'Tunnel Ring 140 · -24m',
                'valid_from' => '2026-11-07',
                'valid_until' => '2026-11-10',
                'precautions_checklist' => 'Multi-gas detector calibrated, blower ventilation active.',
            ])->assertSessionHasNoErrors();

        $permit = PermitToWork::query()->firstOrFail();
        $this->assertSame('PTW-CED-HSE-01-001', $permit->reference_no);
        $this->assertSame('REQUESTED', $permit->status);
        $this->assertSame(ProjectConstraint::STATUS_OPEN, $this->permitConstraint->refresh()->status);

        // 2. Site Supervisor cannot approve PTW (role insufficient)
        $this->actingAs($this->supervisor)
            ->post(route('projects.hse.permits.approve', [$this->project, $permit]))
            ->assertSessionHasErrors('error');
        $this->assertSame('REQUESTED', $permit->refresh()->status);

        // 3. Safety Officer approves PTW -> status APPROVED & PERMIT constraint auto-resolves!
        $this->actingAs($this->safetyOfficer)
            ->post(route('projects.hse.permits.approve', [$this->project, $permit]), [
                'notes' => 'Oxygen 20.9%, LEL 0%. Approved for shift.',
            ])->assertSessionHasNoErrors();

        $permit->refresh();
        $this->permitConstraint->refresh();
        $this->assertSame('APPROVED', $permit->status);
        $this->assertSame($this->safetyOfficer->id, $permit->approved_by);
        $this->assertSame(ProjectConstraint::STATUS_RESOLVED, $this->permitConstraint->status);
        $this->assertNotNull($this->permitConstraint->resolved_at);
    }

    public function test_maker_checker_prevents_safety_officer_from_approving_own_permit_request(): void
    {
        $this->actingAs($this->safetyOfficer)
            ->post(route('projects.hse.permits.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'permit_type' => 'HOT_WORK',
                'work_title' => 'Self-Requested Torch Cutting',
                'valid_from' => '2026-11-07',
                'valid_until' => '2026-11-08',
            ]);

        $permit = PermitToWork::query()->firstOrFail();

        $this->actingAs($this->safetyOfficer)
            ->post(route('projects.hse.permits.approve', [$this->project, $permit]))
            ->assertSessionHasErrors('error');

        $this->assertSame('REQUESTED', $permit->refresh()->status);
    }

    public function test_safe_man_hours_tracks_cumulative_hours_and_resets_after_lti(): void
    {
        // Day 1 & Day 2 manpower logs: 200 hrs + 300 hrs = 500 Safe Man-Hours
        SiteManpowerLog::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'log_date' => '2026-11-01',
            'trade' => 'WELDER',
            'planned_headcount' => 25,
            'actual_headcount' => 25,
            'working_hours' => 8.0,
            'total_man_hours' => 200.0,
            'shift' => 'DAY',
            'created_by' => $this->supervisor->id,
        ]);

        SiteManpowerLog::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'log_date' => '2026-11-03',
            'trade' => 'STEEL_FIXER',
            'planned_headcount' => 30,
            'actual_headcount' => 30,
            'working_hours' => 10.0,
            'total_man_hours' => 300.0,
            'shift' => 'DAY',
            'created_by' => $this->supervisor->id,
        ]);

        // Log a Toolbox Talk on Nov 2 (does NOT reset Safe Man-Hours)
        $this->actingAs($this->safetyOfficer)
            ->post(route('projects.hse.events.store', $this->project), [
                'log_date' => '2026-11-02',
                'record_type' => 'TOOLBOX_TALK',
                'title' => 'Confined Space Rescue Drill',
                'attendees_count' => 55,
            ])->assertSessionHasNoErrors();

        $service = app(HseSafetyOperationsService::class);
        $metricsBeforeLti = $service->calculateSafetyMetrics($this->project);
        $this->assertEquals(500.0, $metricsBeforeLti['safe_man_hours']);
        $this->assertSame(0, $metricsBeforeLti['lti_count']);

        // Log an LTI on Nov 2 -> Safe Man-Hours now counts only logs AFTER Nov 2 (i.e. Nov 3 = 300 hrs)
        $this->actingAs($this->safetyOfficer)
            ->post(route('projects.hse.events.store', $this->project), [
                'log_date' => '2026-11-02',
                'record_type' => 'LOST_TIME_INJURY',
                'title' => 'Hand finger pinch during steel plate handling',
                'lost_time_hours' => 16.0,
            ])->assertSessionHasNoErrors();

        $metricsAfterLti = $service->calculateSafetyMetrics($this->project);
        $this->assertEquals(500.0, $metricsAfterLti['total_man_hours']);
        $this->assertEquals(300.0, $metricsAfterLti['safe_man_hours']);
        $this->assertSame(1, $metricsAfterLti['lti_count']);
    }
}
