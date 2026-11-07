<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveLookaheadIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_report_surfaces_lookahead_summary_and_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $now = now()->startOfWeek();

        WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Steel Frame Assembly',
            'baseline_start_date' => $now->copy()->addDays(2)->toDateString(),
            'baseline_finish_date' => $now->copy()->addDays(6)->toDateString(),
            'planned_cost' => '45000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        $response = $this->get(route('projects.report', $project));
        $response->assertOk();
        $response->assertSee('Weekly Lookahead (3-Week Window)');
        $response->assertSee('Make-Ready Readiness');
        $response->assertSee(route('projects.lookahead', $project));
    }

    public function test_schedule_view_provides_link_to_lookahead(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.schedule.show', $project));
        $response->assertOk();
        $response->assertSee(route('projects.lookahead', $project));
    }

    public function test_portfolio_view_provides_link_to_lookahead(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.portfolio'));
        $response->assertOk();
        $response->assertSee(route('projects.lookahead', $project));
    }

    public function test_constraints_manager_provides_link_to_lookahead(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.constraints', $project));
        $response->assertOk();
        $response->assertSee(route('projects.lookahead', $project));
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'WLS Exec Integration Project',
            'baseline_start_date' => now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => now()->addMonths(2)->toDateString(),
            'budget_amount' => '400000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
