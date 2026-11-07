<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\SiteSpatial3DProjectionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteSpatial3DProjectionServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteSpatial3DProjectionService $service;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SiteSpatial3DProjectionService;

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-3D-01',
            'name' => 'Metro Sky Tower 3D',
            'baseline_start_date' => CarbonImmutable::now()->subMonths(1)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
            'budget_amount' => '1200000.00',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_projects_3d_spatial_elements_for_project_tasks(): void
    {
        $task1 = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.1',
            'name' => 'Foundation & Piling',
            'baseline_start_date' => CarbonImmutable::now()->subDays(10)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->subDays(2)->toDateString(),
            'weight' => 20.0,
            'status' => 'COMPLETED',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $task2 = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.2',
            'name' => 'Ground Floor Structural Frame',
            'baseline_start_date' => CarbonImmutable::now()->subDays(1)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addDays(14)->toDateString(),
            'weight' => 30.0,
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $user = User::factory()->create();

        // Attach blocker constraint to task 2
        ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $task2->id,
            'title' => 'Concrete pump breakdown',
            'type' => 'EQUIPMENT',
            'severity' => 'CRITICAL',
            'status' => 'OPEN',
            'created_by' => $user->id,
        ]);

        $result = $this->service->project($this->project);

        $this->assertEquals('CED_SITE_SPATIAL_3D_V1', $result['version']);
        $this->assertEquals($this->project->id, $result['project_id']);
        $this->assertNotEmpty($result['elements']);

        // Check task1 element
        $element1 = collect($result['elements'])->firstWhere('task_id', $task1->id);
        $this->assertNotNull($element1);
        $this->assertEquals('COMPLETED', $element1['status']);
        $this->assertFalse($element1['is_blocked']);
        $this->assertEquals('#10b981', $element1['color_hex']);

        // Check task2 element with blocker
        $element2 = collect($result['elements'])->firstWhere('task_id', $task2->id);
        $this->assertNotNull($element2);
        $this->assertTrue($element2['is_blocked']);
        $this->assertEquals('CRITICAL', $element2['constraint_severity']);
        $this->assertEquals('#f43f5e', $element2['color_hex']);
        $this->assertArrayHasKey('position', $element2);
        $this->assertArrayHasKey('dimensions', $element2);
    }
}
