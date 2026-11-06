<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectScheduleViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_request_returns_schedule_json(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-SCHED-001',
            'code' => 'SCHED-001',
            'name' => 'Schedule Test Project',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);

        $response = $this->actingAs($member)->getJson(route('projects.schedule.show', $project));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_html_request_renders_timeline_view_with_task_summary(): void
    {
        $this->withoutVite();

        $project = Project::query()->create([
            'id' => 'PRJ-SCHED-002',
            'code' => 'SCHED-002',
            'name' => 'Timeline View Project',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);

        WbsTask::query()->create([
            'id' => 'TASK-SCHED-001',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Piling Work',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-15',
            'weight' => '50.0000',
            'status' => 'COMPLETED',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $response = $this->actingAs($member)->get(route('projects.schedule.show', $project));
        $response->assertOk();
        $response->assertSee('SCHED-002');
        $response->assertSee('Schedule & Timeline', false);
        $response->assertSee('Piling Work');
        $response->assertSee('Total Tasks');
    }

    public function test_unauthenticated_and_non_member_forbidden(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-SCHED-003',
            'code' => 'SCHED-003',
            'name' => 'Forbidden Test Project',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'status' => 'ACTIVE',
        ]);

        $this->get(route('projects.schedule.show', $project))->assertForbidden();

        $outsider = User::factory()->create();
        $this->actingAs($outsider)->get(route('projects.schedule.show', $project))->assertForbidden();
    }
}
