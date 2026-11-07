<?php

namespace Tests\Feature;

use App\Livewire\SiteSupervisorQuickCaptureView;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSupervisorQuickCaptureSurfaceTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Project $project;

    private WbsTask $leafTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-SITE-01',
            'name' => 'Riverside Tower A',
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(3)->toDateString(),
            'budget_amount' => '500000.00',
            'status' => 'ACTIVE',
        ]);

        $this->member = User::factory()->create([
            'name' => 'Site Foreman Somchai',
            'email' => 'somchai.foreman@example.com',
        ]);

        $this->project->members()->attach($this->member, ['role' => 'EXECUTIVE']);

        $this->leafTask = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.1.1',
            'name' => 'Concrete Pouring Ground Floor',
            'baseline_start_date' => CarbonImmutable::now()->subDays(2)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addDays(5)->toDateString(),
            'weight' => 15.0,
            'status' => 'NOT_STARTED',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);
    }

    public function test_unauthenticated_request_is_denied(): void
    {
        $response = $this->get('/projects/'.$this->project->id.'/site');
        $response->assertStatus(403);
    }

    public function test_non_member_request_is_forbidden(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)->get('/projects/'.$this->project->id.'/site');
        $response->assertStatus(403);
    }

    public function test_authorized_member_can_view_site_quick_capture_surface(): void
    {
        $response = $this->actingAs($this->member)->get('/projects/'.$this->project->id.'/site');

        $response->assertStatus(200)
            ->assertSee('Riverside Tower A')
            ->assertSee('CED-SITE-01')
            ->assertSee('Site Quick-Capture')
            ->assertSee('Concrete Pouring Ground Floor');
    }

    public function test_supervisor_can_quick_log_constraint(): void
    {
        Livewire::actingAs($this->member)
            ->test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->set('constraintType', 'MATERIAL')
            ->set('constraintTitle', 'Missing Rebar 16mm Batch C')
            ->set('constraintSeverity', 'HIGH')
            ->set('constraintTaskId', $this->leafTask->id)
            ->call('quickLogConstraint')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Site constraint logged successfully.');

        $this->assertDatabaseHas('project_constraints', [
            'project_id' => $this->project->id,
            'task_id' => $this->leafTask->id,
            'title' => 'Missing Rebar 16mm Batch C',
            'type' => 'MATERIAL',
            'severity' => 'HIGH',
            'status' => 'OPEN',
        ]);
    }

    public function test_supervisor_can_quick_resolve_constraint(): void
    {
        $constraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $this->leafTask->id,
            'title' => 'Access Crane Blocked by Delivery Truck',
            'type' => 'EQUIPMENT',
            'severity' => 'CRITICAL',
            'status' => 'OPEN',
            'created_by' => $this->member->id,
        ]);

        Livewire::actingAs($this->member)
            ->test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->set('resolvingConstraintId', $constraint->id)
            ->set('resolutionNote', 'Truck moved to parking zone B. Access restored.')
            ->call('quickResolveConstraint')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Constraint cleared and marked resolved.');

        $this->assertDatabaseHas('project_constraints', [
            'id' => $constraint->id,
            'status' => 'RESOLVED',
        ]);
    }

    public function test_supervisor_can_transition_task_status(): void
    {
        Livewire::actingAs($this->member)
            ->test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->call('quickTransitionTask', $this->leafTask->id, 'IN_PROGRESS')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Task status updated to IN_PROGRESS.');

        $this->assertEquals('IN_PROGRESS', $this->leafTask->fresh()->status);
    }
}
