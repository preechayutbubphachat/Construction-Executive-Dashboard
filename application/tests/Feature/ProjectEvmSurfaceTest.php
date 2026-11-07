<?php

namespace Tests\Feature;

use App\Livewire\ProjectEvmSCurveView;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectEvmSurfaceTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-EVM-TEST',
            'name' => 'Metro Expressway EVM Surface Test',
            'baseline_start_date' => CarbonImmutable::now()->subWeeks(4)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addWeeks(8)->toDateString(),
            'budget_amount' => '1500000.00',
            'status' => 'ACTIVE',
        ]);

        $this->member = User::factory()->create();
        $this->project->members()->attach($this->member, ['role' => 'EXECUTIVE']);

        WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.0',
            'name' => 'Highway Bridge Decking',
            'baseline_start_date' => $this->project->baseline_start_date->toDateString(),
            'baseline_finish_date' => $this->project->baseline_finish_date->toDateString(),
            'weight' => 50.0,
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);
    }

    public function test_unauthenticated_request_is_denied(): void
    {
        $response = $this->get('/projects/'.$this->project->id.'/evm');
        $response->assertStatus(403);
    }

    public function test_non_member_request_is_forbidden(): void
    {
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)->get('/projects/'.$this->project->id.'/evm');
        $response->assertStatus(403);
    }

    public function test_authorized_member_can_view_evm_s_curve_page(): void
    {
        $response = $this->actingAs($this->member)->get('/projects/'.$this->project->id.'/evm');

        $response->assertStatus(200)
            ->assertSee('Metro Expressway EVM Surface Test')
            ->assertSee('CED-EVM-TEST')
            ->assertSee('Earned Value Management (EVM)', false)
            ->assertSee('BAC (Total Budget)')
            ->assertSee('฿1,500,000.00')
            ->assertSee('CPI:')
            ->assertSee('SPI:')
            ->assertSeeLivewire(ProjectEvmSCurveView::class);
    }
}
