<?php

namespace Tests\Feature;

use App\Models\MaterialRequisition;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MaterialProcurementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $siteEngineer;

    private User $projectManager;

    private User $outsider;

    private WbsTask $task;

    private ProjectConstraint $materialConstraint;

    protected function setUp(): void
    {
        parent::setUp();

        $make = fn (string $name, string $email) => User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        $this->siteEngineer = $make('Site Engineer', 'site@proc.example.com');
        $this->projectManager = $make('Project Manager', 'pm@proc.example.com');
        $this->outsider = $make('Outsider', 'out@proc.example.com');

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-PRC-01',
            'name' => 'Underground Station Box Excavation',
            'status' => 'ACTIVE',
            'budget_amount' => 8000000.00,
            'baseline_start_date' => CarbonImmutable::now()->subMonth()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
        ]);

        $this->project->members()->attach($this->siteEngineer, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->projectManager, ['role' => 'PROJECT_MANAGER']);

        $this->task = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '3.1',
            'name' => 'Diaphragm Wall Rebar Cage Fabrication',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->subWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(2)->toDateString(),
            'weight' => 25.0,
            'is_leaf' => true,
        ]);

        $this->materialConstraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'type' => ProjectConstraint::TYPE_MATERIAL,
            'title' => 'Awaiting DB32 Reinforcement Steel Delivery',
            'description' => 'Cage assembly halted pending 50 tons of DB32 rebar.',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'target_resolution_date' => CarbonImmutable::now()->addDays(4)->toDateString(),
            'created_by' => $this->siteEngineer->id,
        ]);
    }

    public function test_outsider_cannot_access_procurement_routes(): void
    {
        $this->actingAs($this->outsider)->get(route('projects.procurement.index', $this->project))->assertStatus(403);
        $this->actingAs($this->outsider)->get(route('projects.procurement.create', $this->project))->assertStatus(403);
    }

    public function test_creating_requisition_flags_7_day_lookahead_shortage_risk(): void
    {
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'constraint_id' => $this->materialConstraint->id,
                'material_name' => 'Deformed Steel Rebar SD50 DB32',
                'category' => 'STEEL_REBAR',
                'unit' => 'TON',
                'required_qty' => 50.00,
                'unit_cost' => 24000.00,
                'supplier_name' => 'Siam Yamato Steel',
                'required_on_site_date' => CarbonImmutable::now()->addDays(4)->toDateString(),
            ])->assertSessionHasNoErrors();

        $mr = MaterialRequisition::query()->firstOrFail();
        $this->assertSame('MR-CED-PRC-01-001', $mr->reference_no);
        $this->assertEquals(1200000.00, (float) $mr->total_estimated_cost);
        $this->assertTrue($mr->isLookaheadShortageRisk());

        $this->actingAs($this->siteEngineer)
            ->get(route('projects.procurement.index', $this->project))
            ->assertOk()
            ->assertSee('7-Day Lookahead Material Shortage Early Warning')
            ->assertSee('MR-CED-PRC-01-001');
    }

    public function test_po_issuance_and_full_delivery_auto_resolves_linked_material_constraint(): void
    {
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'material_name' => 'Deformed Steel Rebar SD50 DB32',
                'category' => 'STEEL_REBAR',
                'unit' => 'TON',
                'required_qty' => 50.00,
                'unit_cost' => 24000.00,
                'required_on_site_date' => CarbonImmutable::now()->addDays(3)->toDateString(),
            ]);

        $mr = MaterialRequisition::query()->firstOrFail();

        // Site supervisor cannot issue PO (requires PM or Executive)
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.issue-po', [$this->project, $mr]), [
                'po_number' => 'PO-2026-101',
            ])->assertSessionHasErrors('error');

        // Project Manager issues PO
        $this->actingAs($this->projectManager)
            ->post(route('projects.procurement.issue-po', [$this->project, $mr]), [
                'po_number' => 'PO-2026-101',
                'supplier_name' => 'Tata Steel Thailand',
            ])->assertSessionHasNoErrors();

        $mr->refresh();
        $this->assertSame('PO_ISSUED', $mr->status);
        $this->assertSame('PO-2026-101', $mr->po_number);

        // Partial delivery (20 / 50 TON) -> status PARTIALLY_DELIVERED, constraint still OPEN
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.deliver', [$this->project, $mr]), [
                'received_qty' => 20.00,
                'delivery_order_no' => 'DO-5001',
                'mill_cert_ref' => 'MTC-001',
                'qc_passed' => '1',
            ])->assertSessionHasNoErrors();

        $mr->refresh();
        $this->materialConstraint->refresh();
        $this->assertSame('PARTIALLY_DELIVERED', $mr->status);
        $this->assertEquals(20.00, (float) $mr->delivered_qty);
        $this->assertSame(ProjectConstraint::STATUS_OPEN, $this->materialConstraint->status);

        // Remaining delivery (30 / 50 TON) -> status DELIVERED, constraint auto-resolves!
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.deliver', [$this->project, $mr]), [
                'received_qty' => 30.00,
                'delivery_order_no' => 'DO-5002',
                'mill_cert_ref' => 'MTC-002',
                'qc_passed' => '1',
            ])->assertSessionHasNoErrors();

        $mr->refresh();
        $this->materialConstraint->refresh();
        $this->assertSame('DELIVERED', $mr->status);
        $this->assertEquals(50.00, (float) $mr->delivered_qty);
        $this->assertFalse($mr->isLookaheadShortageRisk());
        $this->assertSame(ProjectConstraint::STATUS_RESOLVED, $this->materialConstraint->status);
        $this->assertNotNull($this->materialConstraint->resolved_at);
        $this->assertStringContainsString('MR-CED-PRC-01-001', (string) $this->materialConstraint->resolution_notes);
    }

    public function test_rejected_delivery_does_not_increase_delivered_qty_or_resolve_constraint(): void
    {
        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.store', $this->project), [
                'wbs_task_id' => $this->task->id,
                'material_name' => 'Ready-Mix Concrete 400 ksc',
                'category' => 'CONCRETE',
                'unit' => 'CU.M',
                'required_qty' => 100.00,
                'required_on_site_date' => CarbonImmutable::now()->addDays(2)->toDateString(),
            ]);

        $mr = MaterialRequisition::query()->firstOrFail();

        $this->actingAs($this->siteEngineer)
            ->post(route('projects.procurement.deliver', [$this->project, $mr]), [
                'received_qty' => 100.00,
                'delivery_order_no' => 'DO-BAD-01',
                'inspection_notes' => 'Slump test failed (exceeded 210mm). Truck turned back.',
                'qc_passed' => '0',
            ])->assertSessionHasNoErrors();

        $mr->refresh();
        $this->materialConstraint->refresh();
        $this->assertSame('REJECTED_DELIVERY', $mr->status);
        $this->assertEquals(0.00, (float) $mr->delivered_qty);
        $this->assertSame(ProjectConstraint::STATUS_OPEN, $this->materialConstraint->status);
    }
}
