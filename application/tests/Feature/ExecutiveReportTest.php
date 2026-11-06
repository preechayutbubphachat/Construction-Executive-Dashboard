<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_forbidden(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-REP-001',
            'code' => 'REP-001',
            'name' => 'Report Test Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);

        $this->get(route('projects.report', $project))->assertForbidden();
    }

    public function test_non_member_cannot_access_executive_report(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-REP-002',
            'code' => 'REP-002',
            'name' => 'Non Member Report Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'status' => 'ACTIVE',
        ]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->get(route('projects.report', $project))->assertForbidden();
    }

    public function test_authorized_executive_renders_executive_briefing_report(): void
    {
        $this->withoutVite();

        $project = Project::query()->create([
            'id' => 'PRJ-REP-003',
            'code' => 'REP-003',
            'name' => 'Briefing Report Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'budget_amount' => '750000.00',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);

        WbsTask::query()->create([
            'id' => 'TASK-REP-001',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Earthwork',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-02-15',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        $response = $this->actingAs($member)->get(route('projects.report', $project));

        $response->assertOk();
        $response->assertSee('REP-003');
        $response->assertSee('Briefing Report Project');
        $response->assertSee('Executive Project Briefing');
        $response->assertSee('Print / Save as PDF');
        $response->assertSee('Financial Performance');
        $response->assertSee('Schedule & Milestones', false);
    }
}
