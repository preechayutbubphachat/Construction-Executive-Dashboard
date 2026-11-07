<?php

namespace Tests\Feature;

use App\Models\InterimPaymentCertificate;
use App\Models\NonConformanceReport;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NonConformanceReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $contractor;

    private User $qcInspector;

    private User $director;

    private User $outsider;

    private WbsTask $taskA;

    private WbsTask $taskB;

    protected function setUp(): void
    {
        parent::setUp();

        $make = fn (string $name, string $email) => User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $this->contractor = $make('Contractor Engineer', 'contractor@ncr.example.com');
        $this->qcInspector = $make('QC Manager', 'qc@ncr.example.com');
        $this->director = $make('Project Director', 'director@ncr.example.com');
        $this->outsider = $make('Outsider User', 'outsider@ncr.example.com');

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-NCR-01',
            'name' => 'Viaduct Superstructure Segment 2',
            'status' => 'ACTIVE',
            'budget_amount' => 4000000.00,
            'baseline_start_date' => CarbonImmutable::parse('2026-01-01')->toDateString(),
            'baseline_finish_date' => CarbonImmutable::parse('2026-12-31')->toDateString(),
        ]);

        $this->project->members()->attach($this->contractor, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->qcInspector, ['role' => 'PROJECT_MANAGER']);
        $this->project->members()->attach($this->director, ['role' => 'EXECUTIVE']);

        $this->taskA = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '2.1',
            'name' => 'Pier Cap Concrete Pouring',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => '2026-03-01',
            'baseline_finish_date' => '2026-06-30',
            'weight' => 30.0,
            'is_leaf' => true,
        ]);

        $this->taskB = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '2.2',
            'name' => 'Precast Parapet Installation',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-09-30',
            'weight' => 20.0,
            'is_leaf' => true,
        ]);
    }

    private function issueBlockingNcr(?WbsTask $task = null): NonConformanceReport
    {
        $task ??= $this->taskA;

        $this->actingAs($this->qcInspector)
            ->post(route('projects.ncr.store', $this->project), [
                'wbs_task_id' => $task->id,
                'title' => 'Honeycomb defect on Pier Cap P4',
                'description' => 'Voids exceeding 25mm depth found after formwork stripping.',
                'category' => 'STRUCTURAL',
                'severity' => 'CRITICAL',
                'hold_payment' => '1',
                'location_zone' => 'Pier P4 · North Face',
            ])->assertSessionHasNoErrors();

        return NonConformanceReport::query()->orderByDesc('ncr_number')->firstOrFail();
    }

    public function test_outsider_cannot_access_ncr_routes(): void
    {
        $this->actingAs($this->outsider)->get(route('projects.ncr.index', $this->project))->assertStatus(403);
        $this->actingAs($this->outsider)->get(route('projects.ncr.create', $this->project))->assertStatus(403);
    }

    public function test_member_can_issue_ncr_and_view_register(): void
    {
        $ncr = $this->issueBlockingNcr();

        $this->assertSame('NCR-CED-NCR-01-001', $ncr->reference_no);
        $this->assertSame('OPEN', $ncr->status);
        $this->assertTrue($ncr->blocksPayment());

        $this->actingAs($this->contractor)
            ->get(route('projects.ncr.index', $this->project))
            ->assertOk()
            ->assertSee('NCR-CED-NCR-01-001')
            ->assertSee('HOLD PAYMENT');
    }

    public function test_open_blocking_ncr_prevents_ipc_payment_claim_for_that_wbs_task(): void
    {
        $ncr = $this->issueBlockingNcr($this->taskA);

        // IPC create view shows the Payment Hold warning badge
        $this->actingAs($this->contractor)
            ->get(route('projects.ipc.create', $this->project))
            ->assertOk()
            ->assertSee('Payment Hold: '.$ncr->reference_no);

        // Attempting to claim payment on Task A (blocked) fails
        $response = $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Bridge Builders Co., Ltd.',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [
                    [
                        'wbs_task_id' => $this->taskA->id,
                        'claimed_amount' => 150000.00,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertSame(0, InterimPaymentCertificate::query()->count());

        // Claiming on Task B (unaffected by Task A's NCR) succeeds
        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Bridge Builders Co., Ltd.',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [
                    [
                        'wbs_task_id' => $this->taskB->id,
                        'claimed_amount' => 80000.00,
                    ],
                ],
            ])->assertSessionHasNoErrors();

        $this->assertSame(1, InterimPaymentCertificate::query()->count());
    }

    public function test_capa_rectification_and_qc_close_releases_ipc_payment_hold(): void
    {
        $ncr = $this->issueBlockingNcr($this->taskA);

        // 1. Contractor submits CAPA rectification
        $this->actingAs($this->contractor)
            ->post(route('projects.ncr.rectify', [$this->project, $ncr]), [
                'root_cause' => 'Insufficient concrete vibration near congested rebar.',
                'corrective_action' => 'Chipped loose concrete and repaired with high-strength non-shrink grout.',
                'preventive_action' => 'Added secondary vibrator crew and pre-pour checklist.',
            ])->assertSessionHasNoErrors();

        $ncr->refresh();
        $this->assertSame('RECTIFICATION_SUBMITTED', $ncr->status);
        // Payment is STILL held until QC inspector verifies and closes
        $this->assertTrue($ncr->blocksPayment());

        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Bridge Builders Co., Ltd.',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [['wbs_task_id' => $this->taskA->id, 'claimed_amount' => 100000.00]],
            ])->assertSessionHasErrors('error');

        // 2. QC Inspector verifies and closes the NCR
        $this->actingAs($this->qcInspector)
            ->post(route('projects.ncr.close', [$this->project, $ncr]), [
                'notes' => 'Rebound hammer test passed 45 MPa. Closed.',
            ])->assertSessionHasNoErrors();

        $ncr->refresh();
        $this->assertSame('CLOSED', $ncr->status);
        $this->assertFalse($ncr->blocksPayment());

        // 3. Now IPC claim on Task A succeeds!
        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Bridge Builders Co., Ltd.',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [['wbs_task_id' => $this->taskA->id, 'claimed_amount' => 100000.00]],
            ])->assertSessionHasNoErrors();

        $this->assertSame(1, InterimPaymentCertificate::query()->count());
    }

    public function test_maker_checker_and_role_enforcement_on_ncr_closure(): void
    {
        $ncr = $this->issueBlockingNcr($this->taskA);

        // QC Manager submits rectification themselves
        $this->actingAs($this->qcInspector)
            ->post(route('projects.ncr.rectify', [$this->project, $ncr]), [
                'corrective_action' => 'Patched with epoxy mortar.',
            ])->assertSessionHasNoErrors();

        // Same QC Manager cannot verify & close their own rectification (Maker-Checker)
        $this->actingAs($this->qcInspector)
            ->post(route('projects.ncr.close', [$this->project, $ncr]))
            ->assertSessionHasErrors('error');
        $this->assertSame('RECTIFICATION_SUBMITTED', $ncr->refresh()->status);

        // Site Supervisor (insufficient role) cannot close NCR
        $this->actingAs($this->contractor)
            ->post(route('projects.ncr.close', [$this->project, $ncr]))
            ->assertSessionHasErrors('error');
        $this->assertSame('RECTIFICATION_SUBMITTED', $ncr->refresh()->status);

        // Project Director (independent Executive) can close it
        $this->actingAs($this->director)
            ->post(route('projects.ncr.close', [$this->project, $ncr]))
            ->assertSessionHasNoErrors();
        $this->assertSame('CLOSED', $ncr->refresh()->status);
    }

    public function test_rejected_rectification_keeps_payment_hold_until_resubmitted_and_closed(): void
    {
        $ncr = $this->issueBlockingNcr($this->taskA);

        $this->actingAs($this->contractor)
            ->post(route('projects.ncr.rectify', [$this->project, $ncr]), [
                'corrective_action' => 'Surface plastering only.',
            ]);

        $this->actingAs($this->qcInspector)
            ->post(route('projects.ncr.reject-rectification', [$this->project, $ncr]), [
                'reason' => 'Cosmetic plastering is unacceptable for structural pier cap.',
            ])->assertSessionHasNoErrors();

        $ncr->refresh();
        $this->assertSame('REJECTED_RECTIFICATION', $ncr->status);
        $this->assertTrue($ncr->blocksPayment());
    }
}
