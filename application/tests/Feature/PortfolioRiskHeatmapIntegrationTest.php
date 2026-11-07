<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioRiskHeatmapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_header_provides_link_to_heatmap(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.portfolio'));
        $response->assertOk();
        $response->assertSee(route('projects.heatmap'));
    }

    public function test_executive_report_provides_link_to_heatmap(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.report', $project));
        $response->assertOk();
        $response->assertSee(route('projects.heatmap'));
    }

    public function test_schedule_view_provides_link_to_heatmap(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.schedule.show', $project));
        $response->assertOk();
        $response->assertSee(route('projects.heatmap'));
    }

    public function test_alerts_center_provides_link_to_heatmap(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.alerts', $project));
        $response->assertOk();
        $response->assertSee(route('projects.heatmap'));
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'Heatmap Integration Project',
            'baseline_start_date' => now()->subDays(10)->toDateString(),
            'baseline_finish_date' => now()->addMonths(2)->toDateString(),
            'budget_amount' => '600000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
