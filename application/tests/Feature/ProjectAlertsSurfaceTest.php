<?php

namespace Tests\Feature;

use App\Livewire\ProjectAlertsCenter;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectAlertsSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_member_can_access_alerts_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.alerts', $project));
        $response->assertOk();
        $response->assertSeeLivewire(ProjectAlertsCenter::class);
    }

    public function test_outsider_is_forbidden_from_alerts_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->get(route('projects.alerts', $project));
        $response->assertForbidden();
    }

    public function test_guest_is_forbidden_from_alerts_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');

        $response = $this->get(route('projects.alerts', $project));
        $response->assertForbidden();
    }

    public function test_livewire_alerts_center_renders_alerts_and_filters(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        // Create overdue task
        $task = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Basement Waterproofing',
            'baseline_start_date' => now()->subDays(15)->toDateString(),
            'baseline_finish_date' => now()->subDays(5)->toDateString(),
            'planned_cost' => '30000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        // Create critical stale constraint
        $constraint = ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'type' => ProjectConstraint::TYPE_PERMIT,
            'title' => 'Waterproofing inspection sign-off',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'target_resolution_date' => now()->subDays(2)->toDateString(),
            'created_by' => $member->id,
        ]);

        $component = Livewire::test(ProjectAlertsCenter::class, ['project' => $project]);

        // Default 'ALL' shows both
        $component->assertSee('Basement Waterproofing')
            ->assertSee('Waterproofing inspection sign-off')
            ->assertSee('Active Early-Warning Alerts');

        // Filter: SCHEDULE
        $component->set('filter', 'SCHEDULE')
            ->assertSee('Basement Waterproofing')
            ->assertDontSee('Waterproofing inspection sign-off');

        // Filter: CONSTRAINT
        $component->set('filter', 'CONSTRAINT')
            ->assertDontSee('Basement Waterproofing')
            ->assertSee('Waterproofing inspection sign-off');

        // Filter: CRITICAL
        $component->set('filter', 'CRITICAL')
            ->assertSee('Basement Waterproofing')
            ->assertSee('Waterproofing inspection sign-off');
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'Alerts Surface Test Project',
            'baseline_start_date' => now()->subDays(20)->toDateString(),
            'baseline_finish_date' => now()->addMonths(2)->toDateString(),
            'budget_amount' => '300000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
