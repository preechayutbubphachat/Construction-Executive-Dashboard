<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveAlertsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_report_surfaces_alerts_section_and_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'HVAC Duct Installation',
            'baseline_start_date' => now()->subDays(20)->toDateString(),
            'baseline_finish_date' => now()->subDays(5)->toDateString(),
            'planned_cost' => '35000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        $response = $this->get(route('projects.report', $project));
        $response->assertOk();
        $response->assertSee('Early-Warning Operational Alerts');
        $response->assertSee('HVAC Duct Installation');
        $response->assertSee(route('projects.alerts', $project));
    }

    public function test_portfolio_view_surfaces_alerts_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.portfolio'));
        $response->assertOk();
        $response->assertSee(route('projects.alerts', $project));
    }

    public function test_schedule_view_surfaces_alerts_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.schedule.show', $project));
        $response->assertOk();
        $response->assertSee(route('projects.alerts', $project));
    }

    public function test_constraints_manager_surfaces_alerts_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.constraints', $project));
        $response->assertOk();
        $response->assertSee(route('projects.alerts', $project));
    }

    public function test_lookahead_view_surfaces_alerts_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.lookahead', $project));
        $response->assertOk();
        $response->assertSee(route('projects.alerts', $project));
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'Alerts Integration Project',
            'baseline_start_date' => now()->subDays(30)->toDateString(),
            'baseline_finish_date' => now()->addMonths(2)->toDateString(),
            'budget_amount' => '450000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
