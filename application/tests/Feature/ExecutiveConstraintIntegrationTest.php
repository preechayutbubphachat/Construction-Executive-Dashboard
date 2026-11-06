<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutiveConstraintIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_report_surfaces_open_constraints_and_link(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $task = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Substructure Excavation',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-05-20',
            'planned_cost' => '30000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        $constraint = ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'task_id' => $task->id,
            'type' => ProjectConstraint::TYPE_PERMIT,
            'title' => 'Excavation soil dump permit delayed',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $member->id,
        ]);

        $response = $this->get(route('projects.report', $project));
        $response->assertOk();
        $response->assertSee('Active Constraints', false);
        $response->assertSee('Excavation soil dump permit delayed');
        $response->assertSee(route('projects.constraints', $project));
    }

    public function test_schedule_view_surfaces_task_impediment_warning(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $task = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '2.1',
            'name' => 'Tower Crane Erection',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-05-15',
            'planned_cost' => '40000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'task_id' => $task->id,
            'type' => ProjectConstraint::TYPE_EQUIPMENT,
            'title' => 'Crane motor cable fault',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $member->id,
        ]);

        $response = $this->get(route('projects.schedule.show', $project));
        $response->assertOk();
        $response->assertSee('Impediment');
        $response->assertSee(route('projects.constraints', $project));
    }

    public function test_portfolio_view_provides_link_to_constraints_register(): void
    {
        [$project, $member] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($member);

        $response = $this->get(route('projects.portfolio'));
        $response->assertOk();
        $response->assertSee(route('projects.constraints', $project));
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.random_int(100, 999),
            'name' => 'Executive Constraint Integration',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '300000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
