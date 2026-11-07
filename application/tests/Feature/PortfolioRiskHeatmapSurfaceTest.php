<?php

namespace Tests\Feature;

use App\Livewire\PortfolioRiskHeatmapView;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortfolioRiskHeatmapSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_heatmap_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('projects.heatmap'));
        $response->assertOk();
        $response->assertSeeLivewire(PortfolioRiskHeatmapView::class);
    }

    public function test_unauthenticated_user_is_forbidden_from_heatmap(): void
    {
        $response = $this->get(route('projects.heatmap'));
        $response->assertForbidden();
    }

    public function test_user_only_sees_permitted_projects_in_heatmap(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $permittedProject = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-PERM-1',
            'name' => 'Permitted High Rise',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-10-31',
            'budget_amount' => '800000.00',
            'status' => 'ACTIVE',
        ]);
        $permittedProject->members()->attach($user, ['role' => 'PROJECT_MEMBER']);

        $forbiddenProject = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-FORBID-2',
            'name' => 'Secret Tunnel Complex',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-10-31',
            'budget_amount' => '900000.00',
            'status' => 'ACTIVE',
        ]);
        $forbiddenProject->members()->attach($otherUser, ['role' => 'PROJECT_MEMBER']);

        $this->actingAs($user);

        $response = $this->get(route('projects.heatmap'));
        $response->assertOk();
        $response->assertSee('Permitted High Rise');
        $response->assertDontSee('Secret Tunnel Complex');
    }

    public function test_livewire_heatmap_sorting_and_filtering(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $p1 = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-SORT-1',
            'name' => 'Alpha Commercial Tower',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-10-31',
            'budget_amount' => '500000.00',
            'status' => 'ACTIVE',
        ]);
        $p1->members()->attach($user, ['role' => 'PROJECT_MEMBER']);

        $p2 = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-SORT-2',
            'name' => 'Beta Warehouse Depot',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-10-31',
            'budget_amount' => '300000.00',
            'status' => 'ACTIVE',
        ]);
        $p2->members()->attach($user, ['role' => 'PROJECT_MEMBER']);

        $component = Livewire::test(PortfolioRiskHeatmapView::class);

        // Initial default shows both
        $component->assertSee('Alpha Commercial Tower')
            ->assertSee('Beta Warehouse Depot')
            ->assertSee('Executive Risk Heatmap');

        // Test sort toggle
        $component->call('sortBy', 'budget_amount')
            ->assertSet('sortField', 'budget_amount');

        // Test filter
        $component->call('setFilter', 'LOW')
            ->assertSet('filter', 'LOW');
    }
}
