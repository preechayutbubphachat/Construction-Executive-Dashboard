<?php

namespace Tests\Feature;

use App\Livewire\ProjectConstraintManager;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectConstraintSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_project_member_can_access_constraints_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.constraints', $project));
        $response->assertOk();
        $response->assertSeeLivewire(ProjectConstraintManager::class);
    }

    public function test_outsider_is_forbidden_from_constraints_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->get(route('projects.constraints', $project));
        $response->assertForbidden();
    }

    public function test_guest_is_forbidden_from_constraints_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');

        $response = $this->get(route('projects.constraints', $project));
        $response->assertForbidden();
    }

    public function test_member_can_log_constraint_via_livewire(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $task = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Foundation Pouring',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-05-15',
            'planned_cost' => '50000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        Livewire::test(ProjectConstraintManager::class, ['project' => $project])
            ->set('type', ProjectConstraint::TYPE_MATERIAL)
            ->set('title', 'Ready-mix batch delay')
            ->set('description', 'Supplier plant down for 48 hours')
            ->set('severity', ProjectConstraint::SEVERITY_HIGH)
            ->set('task_id', $task->id)
            ->set('target_resolution_date', '2026-05-05')
            ->call('createConstraint')
            ->assertHasNoErrors()
            ->assertSee('Ready-mix batch delay');

        $this->assertDatabaseHas('project_constraints', [
            'project_id' => $project->id,
            'task_id' => $task->id,
            'type' => ProjectConstraint::TYPE_MATERIAL,
            'title' => 'Ready-mix batch delay',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $member->id,
        ]);
    }

    public function test_validation_fails_for_missing_required_fields(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        Livewire::test(ProjectConstraintManager::class, ['project' => $project])
            ->set('title', '')
            ->set('type', 'INVALID_TYPE')
            ->set('severity', 'INVALID_SEV')
            ->call('createConstraint')
            ->assertHasErrors(['title', 'type', 'severity']);

        $this->assertDatabaseCount('project_constraints', 0);
    }

    public function test_member_can_resolve_constraint(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $constraint = ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'type' => ProjectConstraint::TYPE_PERMIT,
            'title' => 'Municipality permit pending',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $member->id,
        ]);

        Livewire::test(ProjectConstraintManager::class, ['project' => $project])
            ->call('openResolveModal', $constraint->id)
            ->assertSet('resolvingId', $constraint->id)
            ->set('resolutionNotes', 'Permit granted by district engineering department')
            ->call('resolveConstraint')
            ->assertHasNoErrors();

        $fresh = $constraint->fresh();
        $this->assertSame(ProjectConstraint::STATUS_RESOLVED, $fresh->status);
        $this->assertSame('Permit granted by district engineering department', $fresh->resolution_notes);
        $this->assertNotNull($fresh->resolved_at);
    }

    public function test_filtering_constraints_by_status(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $openConstraint = ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'type' => ProjectConstraint::TYPE_SITE_ACCESS,
            'title' => 'Excavation access blocked',
            'severity' => ProjectConstraint::SEVERITY_MEDIUM,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $member->id,
        ]);

        $resolvedConstraint = ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'type' => ProjectConstraint::TYPE_EQUIPMENT,
            'title' => 'Crane hydraulic repair',
            'severity' => ProjectConstraint::SEVERITY_LOW,
            'status' => ProjectConstraint::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_notes' => 'Parts replaced',
            'created_by' => $member->id,
        ]);

        $component = Livewire::test(ProjectConstraintManager::class, ['project' => $project]);

        // Default 'ALL' shows both
        $component->assertSee('Excavation access blocked')
            ->assertSee('Crane hydraulic repair');

        // Filter 'OPEN'
        $component->set('filter', 'OPEN')
            ->assertSee('Excavation access blocked')
            ->assertDontSee('Crane hydraulic repair');

        // Filter 'RESOLVED'
        $component->set('filter', 'RESOLVED')
            ->assertDontSee('Excavation access blocked')
            ->assertSee('Crane hydraulic repair');
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'Constraint Surface Project',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '200000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
