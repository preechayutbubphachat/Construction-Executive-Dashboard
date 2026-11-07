<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\InterimPaymentCertificate;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\EarnedValueManagementService;
use App\Services\InterimPaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InterimPaymentCertificateWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $contractor;

    private User $siteEngineer;

    private User $quantitySurveyor;

    private User $projectDirector;

    private User $outsider;

    private WbsTask $taskA;

    private WbsTask $taskB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contractor = User::query()->create([
            'name' => 'Prasert Contractor',
            'email' => 'prasert@contractor.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->siteEngineer = User::query()->create([
            'name' => 'Wichai Site Engineer',
            'email' => 'wichai.site@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->quantitySurveyor = User::query()->create([
            'name' => 'Kanya Cost QS',
            'email' => 'kanya.qs@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->projectDirector = User::query()->create([
            'name' => 'Somchai Director',
            'email' => 'somchai.director@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->outsider = User::query()->create([
            'name' => 'Stranger Outside',
            'email' => 'stranger@other.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-IPC-01',
            'name' => 'High-Speed Rail Viaduct Section 4',
            'status' => 'ACTIVE',
            'budget_amount' => 5000000.00,
            'baseline_start_date' => CarbonImmutable::now()->subMonths(2)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
        ]);

        // Assign project roles
        $this->project->members()->attach($this->contractor, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->siteEngineer, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->quantitySurveyor, ['role' => 'PROJECT_MANAGER']);
        $this->project->members()->attach($this->projectDirector, ['role' => 'EXECUTIVE']);

        // Create leaf tasks
        $this->taskA = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.1',
            'name' => 'Bored Piling Works',
            'status' => 'COMPLETED',
            'baseline_start_date' => CarbonImmutable::now()->subMonths(2)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->subMonth()->toDateString(),
            'planned_cost' => 1000000.00,
            'weight' => 20.0,
            'is_leaf' => true,
        ]);

        $this->taskB = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.2',
            'name' => 'Substructure Pier Columns',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->subMonth()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(2)->toDateString(),
            'planned_cost' => 2000000.00,
            'weight' => 40.0,
            'is_leaf' => true,
        ]);
    }

    public function test_outsider_cannot_access_ipc_routes(): void
    {
        $response = $this->actingAs($this->outsider)
            ->get(route('projects.ipc.index', $this->project));
        $response->assertStatus(403);

        $response = $this->actingAs($this->outsider)
            ->get(route('projects.ipc.create', $this->project));
        $response->assertStatus(403);
    }

    public function test_authorized_member_can_view_ipc_register_and_create_form(): void
    {
        $response = $this->actingAs($this->contractor)
            ->get(route('projects.ipc.index', $this->project));
        $response->assertOk();
        $response->assertSee('Interim Payment Certificates');
        $response->assertSee('Gross Certified');

        $response = $this->actingAs($this->contractor)
            ->get(route('projects.ipc.create', $this->project));
        $response->assertOk();
        $response->assertSee('Bored Piling Works');
        $response->assertSee('Substructure Pier Columns');
    }

    public function test_submitting_ipc_application_calculates_retention_advance_and_vat_accurately(): void
    {
        $payload = [
            'contractor_name' => 'Siam Construction Consortium Ltd.',
            'contractor_invoice_no' => 'INV-2026/001',
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'retention_rate_percent' => 5.0,
            'advance_recovery_deduction' => 10000.00,
            'other_deductions' => 1000.00,
            'vat_rate_percent' => 7.0,
            'items' => [
                [
                    'wbs_task_id' => $this->taskA->id,
                    'claimed_amount' => 100000.00,
                    'claimed_progress_percent' => 10,
                    'remarks' => 'Completed 5 bored piles on Pier P12',
                ],
            ],
        ];

        $response = $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), $payload);

        $ipc = InterimPaymentCertificate::query()->where('project_id', $this->project->id)->first();
        $this->assertNotNull($ipc);

        $response->assertRedirect(route('projects.ipc.show', [$this->project, $ipc]));

        // Math Verification:
        // Current Work = 100,000.00
        // Retention 5% = 5,000.00
        // Advance recovery = 10,000.00
        // Other deductions = 1,000.00
        // Net Before VAT = 100,000 - 5,000 - 10,000 - 1,000 = 84,000.00
        // VAT 7% = 84,000 * 0.07 = 5,880.00
        // Net Payable = 84,000 + 5,880 = 89,880.00
        $this->assertEquals(100000.00, (float) $ipc->current_work_amount);
        $this->assertEquals(5000.00, (float) $ipc->retention_deduction);
        $this->assertEquals(10000.00, (float) $ipc->advance_recovery_deduction);
        $this->assertEquals(1000.00, (float) $ipc->other_deductions);
        $this->assertEquals(84000.00, (float) $ipc->net_amount_before_vat);
        $this->assertEquals(5880.00, (float) $ipc->vat_amount);
        $this->assertEquals(89880.00, (float) $ipc->net_payable_amount);
        $this->assertEquals('SUBMITTED', $ipc->status);
        $this->assertEquals(1, $ipc->certificate_number);
    }

    public function test_full_3_level_approval_workflow_and_ledger_posting(): void
    {
        // 1. Submit IPC
        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Italian-Thai Special Works',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'retention_rate_percent' => 10.0,
                'advance_recovery_deduction' => 0.00,
                'vat_rate_percent' => 7.0,
                'items' => [
                    [
                        'wbs_task_id' => $this->taskA->id,
                        'claimed_amount' => 200000.00,
                    ],
                ],
            ]);

        $ipc = InterimPaymentCertificate::query()->where('project_id', $this->project->id)->firstOrFail();
        $this->assertEquals('SUBMITTED', $ipc->status);

        // 2. Level 1: Site Engineer Verification
        $response = $this->actingAs($this->siteEngineer)
            ->post(route('projects.ipc.verify-site', [$this->project, $ipc]), [
                'notes' => 'Inspected reinforcement steel and core samples on site. Quality confirmed.',
            ]);
        $response->assertRedirect(route('projects.ipc.show', [$this->project, $ipc]));

        $ipc->refresh();
        $this->assertEquals('VERIFIED_SITE', $ipc->status);
        $this->assertEquals($this->siteEngineer->id, $ipc->site_engineer_id);
        $this->assertNotNull($ipc->site_verified_at);

        // 3. Level 2: Quantity Surveyor (QS) Valuation Certification
        $response = $this->actingAs($this->quantitySurveyor)
            ->post(route('projects.ipc.verify-qs', [$this->project, $ipc]), [
                'notes' => 'Cross-checked BOQ Bill 1.1 rates and arithmetic. Value certified.',
            ]);
        $response->assertRedirect(route('projects.ipc.show', [$this->project, $ipc]));

        $ipc->refresh();
        $this->assertEquals('VERIFIED_QS', $ipc->status);
        $this->assertEquals($this->quantitySurveyor->id, $ipc->quantity_surveyor_id);
        $this->assertNotNull($ipc->qs_verified_at);

        // 4. Level 3: Project Director Executive Approval & Ledger Posting
        $response = $this->actingAs($this->projectDirector)
            ->post(route('projects.ipc.approve-director', [$this->project, $ipc]), [
                'notes' => 'Authorized for financial disbursement from Q4 budget reserve.',
            ]);
        $response->assertRedirect(route('projects.ipc.show', [$this->project, $ipc]));

        $ipc->refresh();
        $this->assertEquals('POSTED', $ipc->status);
        $this->assertEquals($this->projectDirector->id, $ipc->project_director_id);
        $this->assertNotNull($ipc->director_approved_at);
        $this->assertNotNull($ipc->finance_transaction_id);

        // 5. Verify posted FinanceTransaction
        $transaction = FinanceTransaction::query()->find($ipc->finance_transaction_id);
        $this->assertNotNull($transaction);
        $this->assertEquals('EXPENSE', $transaction->direction);
        $this->assertEquals(FinanceStatus::Posted, $transaction->status);
        $this->assertEquals($ipc->net_payable_amount, (float) $transaction->amount);
        $this->assertEquals($this->contractor->id, $transaction->created_by);
        $this->assertEquals($this->projectDirector->id, $transaction->approved_by);

        // 6. Verify integration into EVM S-Curve (Actual Cost AC increases)
        /** @var EarnedValueManagementService $evmService */
        $evmService = app(EarnedValueManagementService::class);
        $evmData = $evmService->calculate($this->project);

        $this->assertGreaterThan(0, $evmData['metrics']['ac']);
        $this->assertEquals($ipc->net_payable_amount, $evmData['metrics']['ac']);
    }

    public function test_maker_checker_rule_prevents_creator_from_director_approving(): void
    {
        // Project Director creates the application
        $this->actingAs($this->projectDirector)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Self Contractor Inc',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [
                    ['wbs_task_id' => $this->taskA->id, 'claimed_amount' => 50000.00],
                ],
            ]);

        $ipc = InterimPaymentCertificate::query()->where('project_id', $this->project->id)->firstOrFail();

        // Site engineer verifies
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.ipc.verify-site', [$this->project, $ipc]));

        // QS verifies
        $this->actingAs($this->quantitySurveyor)
            ->post(route('projects.ipc.verify-qs', [$this->project, $ipc]));

        $ipc->refresh();
        $this->assertEquals('VERIFIED_QS', $ipc->status);

        // Director tries to approve their own IPC -> Must be rejected by Maker-Checker
        $response = $this->actingAs($this->projectDirector)
            ->post(route('projects.ipc.approve-director', [$this->project, $ipc]));

        $response->assertSessionHasErrors('error');
        $ipc->refresh();
        $this->assertNotEquals('POSTED', $ipc->status);
        $this->assertNull($ipc->finance_transaction_id);
    }

    public function test_cannot_skip_levels_in_workflow(): void
    {
        // Submit IPC
        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Skip Test Contractor',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [
                    ['wbs_task_id' => $this->taskA->id, 'claimed_amount' => 50000.00],
                ],
            ]);

        $ipc = InterimPaymentCertificate::query()->where('project_id', $this->project->id)->firstOrFail();
        $this->assertEquals('SUBMITTED', $ipc->status);

        // Director attempts to approve directly skipping Level 1 & Level 2
        $response = $this->actingAs($this->projectDirector)
            ->post(route('projects.ipc.approve-director', [$this->project, $ipc]));

        $response->assertSessionHasErrors('error');
        $ipc->refresh();
        $this->assertEquals('SUBMITTED', $ipc->status);
    }

    public function test_insufficient_role_cannot_perform_level_2_or_level_3(): void
    {
        // Submit IPC
        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Role Check Contractor',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [
                    ['wbs_task_id' => $this->taskA->id, 'claimed_amount' => 50000.00],
                ],
            ]);

        $ipc = InterimPaymentCertificate::query()->where('project_id', $this->project->id)->firstOrFail();

        // Level 1: Site verified
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.ipc.verify-site', [$this->project, $ipc]));

        // Site supervisor (Wichai) attempts Level 2 (QS requires PROJECT_MANAGER or EXECUTIVE)
        // Note: Wichai is SITE_SUPERVISOR only
        $response = $this->actingAs($this->siteEngineer)
            ->post(route('projects.ipc.verify-qs', [$this->project, $ipc]));

        $response->assertSessionHasErrors('error');
        $ipc->refresh();
        $this->assertEquals('VERIFIED_SITE', $ipc->status);

        // Advance to VERIFIED_QS via authorized QS
        $this->actingAs($this->quantitySurveyor)
            ->post(route('projects.ipc.verify-qs', [$this->project, $ipc]));
        $ipc->refresh();
        $this->assertEquals('VERIFIED_QS', $ipc->status);

        // QS (PROJECT_MANAGER) attempts Level 3 (Requires EXECUTIVE)
        $response = $this->actingAs($this->quantitySurveyor)
            ->post(route('projects.ipc.approve-director', [$this->project, $ipc]));

        $response->assertSessionHasErrors('error');
        $ipc->refresh();
        $this->assertEquals('VERIFIED_QS', $ipc->status);
    }

    public function test_rejection_flow_records_reason_and_audit(): void
    {
        $this->actingAs($this->contractor)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'Reject Test Contractor',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [
                    ['wbs_task_id' => $this->taskA->id, 'claimed_amount' => 50000.00],
                ],
            ]);

        $ipc = InterimPaymentCertificate::query()->where('project_id', $this->project->id)->firstOrFail();

        /** @var InterimPaymentService $service */
        $service = app(InterimPaymentService::class);
        $rejectedIpc = $service->reject($ipc, $this->siteEngineer, 'Work quality on pier column does not meet ASTM standards');

        $this->assertEquals('REJECTED', $rejectedIpc->status);
        $this->assertStringContainsString('ASTM standards', $rejectedIpc->director_notes);
    }
}
