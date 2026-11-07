<?php

namespace Tests\Feature;

use App\Models\EngineeringRfi;
use App\Models\EngineeringSubmittal;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EngineeringRfiAndSubmittalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $siteEngineer;

    private User $consultantPm;

    private User $outsider;

    private WbsTask $task;

    private ProjectConstraint $designConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siteEngineer = User::query()->create([
            'name' => 'Site Engineer',
            'email' => 'site@eng.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->consultantPm = User::query()->create([
            'name' => 'Consultant Lead PM',
            'email' => 'pm@eng.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->outsider = User::query()->create([
            'name' => 'Outsider',
            'email' => 'out@eng.example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-ENG-01',
            'name' => 'Cable-Stayed Pylon Foundation',
            'status' => 'ACTIVE',
            'budget_amount' => 12000000.00,
            'baseline_start_date' => CarbonImmutable::now()->subMonth()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
        ]);

        $this->project->members()->attach($this->siteEngineer, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->consultantPm, ['role' => 'PROJECT_MANAGER']);

        $this->task = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '6.1',
            'name' => 'Pylon Base Post-Tensioning Duct Installation',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->subWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(2)->toDateString(),
            'weight' => 30.0,
            'is_leaf' => true,
        ]);

        $this->designConstraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'type' => ProjectConstraint::TYPE_DESIGN_RFI,
            'title' => 'Drawing Discrepancy on Tendon Anchorage Zone',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $this->siteEngineer->id,
        ]);
    }

    public function test_outsider_cannot_access_engineering_routes(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('projects.engineering.index', $this->project))
            ->assertStatus(403);
    }

    public function test_rfi_lifecycle_overdue_detection_maker_checker_and_auto_constraint_resolution(): void
    {
        // 1. Issue RFI with past due_date to verify overdue detection
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.engineering.rfi.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'discipline' => 'STRUCTURAL',
                'subject' => 'Tendon Duct Radius Conflict at Grid P2',
                'drawing_ref' => 'ST-401 Rev.2',
                'question' => 'Drawing ST-401 shows 2.5m radius which clashes with shear links.',
                'proposed_solution' => 'Shift duct centerline +75mm upward.',
                'due_date' => CarbonImmutable::now()->subDays(2)->toDateString(),
            ])->assertSessionHasNoErrors();

        $rfi = EngineeringRfi::query()->firstOrFail();
        $this->assertSame('RFI-CED-ENG-01-001', $rfi->reference_no);
        $this->assertTrue($rfi->isOverdue());

        $this->actingAs($this->siteEngineer)
            ->get(route('projects.engineering.index', $this->project))
            ->assertOk()
            ->assertSee('Overdue Engineering RFIs')
            ->assertSee('RFI-CED-ENG-01-001');

        // 2. Site engineer cannot answer RFI (insufficient role)
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.engineering.rfi.answer', [$this->project, $rfi]), [
                'official_response' => 'Self-approved.',
            ])->assertSessionHasErrors('error');
        $this->assertSame('OPEN', $rfi->refresh()->status);

        // 3. Consultant PM answers & closes RFI -> DESIGN_RFI constraint auto-resolves!
        $this->actingAs($this->consultantPm)
            ->post(route('projects.engineering.rfi.answer', [$this->project, $rfi]), [
                'official_response' => 'Approved to shift duct centerline +75mm per sketch SK-14.',
            ])->assertSessionHasNoErrors();

        $rfi->refresh();
        $this->designConstraint->refresh();
        $this->assertSame('CLOSED', $rfi->status);
        $this->assertFalse($rfi->isOverdue());
        $this->assertSame(ProjectConstraint::STATUS_RESOLVED, $this->designConstraint->status);
        $this->assertNotNull($this->designConstraint->resolved_at);
    }

    public function test_submittal_review_revision_resubmission_and_maker_checker(): void
    {
        // 1. Site engineer logs Rev.0 shop drawing submittal
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.engineering.submittals.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'submittal_type' => 'SHOP_DRAWING',
                'discipline' => 'STRUCTURAL',
                'title' => 'Pylon Anchorage Reinforcing Bar Schedule',
                'spec_section' => 'Section 03200',
                'revision' => 'Rev.0',
            ])->assertSessionHasNoErrors();

        $sub = EngineeringSubmittal::query()->firstOrFail();
        $this->assertSame('SUB-CED-ENG-01-001', $sub->reference_no);
        $this->assertSame('Rev.0', $sub->revision);
        $this->assertSame('PENDING_REVIEW', $sub->status);

        // 2. Consultant PM reviews with REVISE_AND_RESUBMIT
        $this->actingAs($this->consultantPm)
            ->post(route('projects.engineering.submittals.review', [$this->project, $sub]), [
                'decision' => 'REVISE_AND_RESUBMIT',
                'review_comments' => 'Increase lap splice length for DB32 bars to 45d.',
            ])->assertSessionHasNoErrors();

        $sub->refresh();
        $this->assertSame('REVISE_AND_RESUBMIT', $sub->status);
        $this->assertFalse($sub->isApprovedForConstruction());

        // 3. Site engineer resubmits as Rev.1
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.engineering.submittals.resubmit', [$this->project, $sub]), [
                'revision' => 'Rev.1',
            ])->assertSessionHasNoErrors();

        $sub->refresh();
        $this->assertSame('Rev.1', $sub->revision);
        $this->assertSame('PENDING_REVIEW', $sub->status);

        // 4. Consultant PM approves Rev.1 as APPROVED_AS_NOTED
        $this->actingAs($this->consultantPm)
            ->post(route('projects.engineering.submittals.review', [$this->project, $sub]), [
                'decision' => 'APPROVED_AS_NOTED',
                'review_comments' => 'Approved for construction subject to field cover check.',
            ])->assertSessionHasNoErrors();

        $sub->refresh();
        $this->assertSame('APPROVED_AS_NOTED', $sub->status);
        $this->assertTrue($sub->isApprovedForConstruction());
    }
}
