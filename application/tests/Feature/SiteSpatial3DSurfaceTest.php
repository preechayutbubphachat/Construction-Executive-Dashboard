<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteSpatial3DSurfaceTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-3D-TEST',
            'name' => 'Metro Sky Tower 3D Test',
            'baseline_start_date' => CarbonImmutable::now()->subMonths(1)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
            'budget_amount' => '850000.00',
            'status' => 'ACTIVE',
        ]);

        $this->member = User::factory()->create();
        $this->project->members()->attach($this->member, ['role' => 'EXECUTIVE']);

        WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.0',
            'name' => 'Main Structural Frame',
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addWeeks(4)->toDateString(),
            'weight' => 25.0,
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);
    }

    public function test_unauthenticated_request_is_denied(): void
    {
        $response = $this->get('/projects/'.$this->project->id.'/3d-twin');
        $response->assertStatus(403);
    }

    public function test_non_member_request_is_forbidden(): void
    {
        $outsider = User::factory()->create();

        $response = $this->actingAs($outsider)->get('/projects/'.$this->project->id.'/3d-twin');
        $response->assertStatus(403);
    }

    public function test_authorized_member_can_view_3d_spatial_twin_page(): void
    {
        $response = $this->actingAs($this->member)->get('/projects/'.$this->project->id.'/3d-twin');

        $response->assertStatus(200)
            ->assertSee('Metro Sky Tower 3D Test')
            ->assertSee('CED-3D-TEST')
            ->assertSee('3D Spatial Digital Twin');
    }

    public function test_authorized_member_can_fetch_3d_spatial_data(): void
    {
        $response = $this->actingAs($this->member)->getJson('/projects/'.$this->project->id.'/3d-twin/data');

        $response->assertStatus(200)
            ->assertJson([
                'version' => 'CED_SITE_SPATIAL_3D_V1',
                'project_id' => $this->project->id,
                'project_code' => 'CED-3D-TEST',
            ])
            ->assertJsonStructure([
                'version',
                'project_id',
                'summary' => [
                    'total_elements',
                    'completed_count',
                    'in_progress_count',
                    'blocked_count',
                ],
                'camera_bounds',
                'elements',
            ]);
    }
}
