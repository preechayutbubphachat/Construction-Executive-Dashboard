<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutivePortfolioEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_renders_kpi_metrics_and_interactive_filter(): void
    {
        $this->withoutVite();

        $member = User::factory()->create();

        $project1 = Project::query()->create([
            'id' => 'PRJ-TEST-001',
            'code' => 'PRJ-001',
            'name' => 'Alpha Tower',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '500000.00',
            'status' => 'ACTIVE',
        ]);
        $project1->members()->attach($member, ['role' => 'EXECUTIVE']);

        $project2 = Project::query()->create([
            'id' => 'PRJ-TEST-002',
            'code' => 'PRJ-002',
            'name' => 'Beta Bridge',
            'baseline_start_date' => '2026-02-01',
            'baseline_finish_date' => '2026-08-31',
            'budget_amount' => '300000.00',
            'status' => 'ACTIVE',
        ]);
        $project2->members()->attach($member, ['role' => 'EXECUTIVE']);

        $response = $this->actingAs($member)->get(route('projects.portfolio'));

        $response->assertOk();
        // Check for portfolio-level KPI summaries
        $response->assertSee('Total Projects');
        $response->assertSee('Total Budget');
        // Check for Alpine.js filter/search component bindings
        $response->assertSee('x-data="{ search: \'\', filter: \'ALL\' }"', false);
        $response->assertSee('Search projects...');
        // Check for quick action navigation links
        $response->assertSee(route('projects.executive', $project1));
        $response->assertSee(route('projects.wbs.show', $project1));
        $response->assertSee(route('projects.finance', $project1));
        $response->assertSee(route('projects.report', $project1));
    }
}
