<?php

namespace Tests\Feature;

use App\Livewire\WeeklyLookaheadView;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WeeklyLookaheadSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_member_can_access_lookahead_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.lookahead', $project));
        $response->assertOk();
        $response->assertSeeLivewire(WeeklyLookaheadView::class);
    }

    public function test_outsider_is_forbidden_from_lookahead_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $outsider = User::factory()->create();
        $this->actingAs($outsider);

        $response = $this->get(route('projects.lookahead', $project));
        $response->assertForbidden();
    }

    public function test_guest_is_forbidden_from_lookahead_page(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');

        $response = $this->get(route('projects.lookahead', $project));
        $response->assertForbidden();
    }

    public function test_livewire_lookahead_toggles_weeks_and_filters(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $anchor = now()->startOfWeek();

        // Task 1: In Week 1 - Make Ready
        $task1 = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Grade Beam Concrete',
            'baseline_start_date' => $anchor->copy()->addDays(1)->toDateString(),
            'baseline_finish_date' => $anchor->copy()->addDays(4)->toDateString(),
            'planned_cost' => '30000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        // Task 2: In Week 1 - Blocked by Constraint
        $task2 = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.2',
            'name' => 'Slab Rebar Laying',
            'baseline_start_date' => $anchor->copy()->addDays(2)->toDateString(),
            'baseline_finish_date' => $anchor->copy()->addDays(5)->toDateString(),
            'planned_cost' => '25000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'task_id' => $task2->id,
            'type' => ProjectConstraint::TYPE_MATERIAL,
            'title' => 'Grade 40 rebar shortage',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $member->id,
        ]);

        $component = Livewire::test(WeeklyLookaheadView::class, ['project' => $project]);

        // Initial default: 3 weeks, ALL filter
        $component->assertSee('Grade Beam Concrete')
            ->assertSee('Slab Rebar Laying')
            ->assertSee('Grade 40 rebar shortage')
            ->assertSee('Make-Ready Rate');

        // Filter: MAKE_READY only
        $component->set('filter', 'MAKE_READY')
            ->assertSee('Grade Beam Concrete')
            ->assertDontSee('Slab Rebar Laying');

        // Filter: CONSTRAINED only
        $component->set('filter', 'CONSTRAINED')
            ->assertDontSee('Grade Beam Concrete')
            ->assertSee('Slab Rebar Laying');

        // Toggle weeks to 6 weeks
        $component->set('weeks', 6)
            ->assertSet('weeks', 6);
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'WLS Surface Test Project',
            'baseline_start_date' => now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => now()->addMonths(3)->toDateString(),
            'budget_amount' => '350000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
