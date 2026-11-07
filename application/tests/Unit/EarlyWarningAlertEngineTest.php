<?php

namespace Tests\Unit;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\EarlyWarningAlertEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarlyWarningAlertEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_detects_schedule_overdue_alert_for_uncompleted_task(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-ALRT-1',
            'name' => 'Overdue Test Project',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);

        $task = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Site Drainage Excavation',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-05-10',
            'planned_cost' => '20000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        $engine = app(EarlyWarningAlertEngine::class);
        $result = $engine->forProject($project, Carbon::parse('2026-05-15'));

        $this->assertSame(1, $result['summary']['total_alerts']);
        $this->assertSame(1, $result['summary']['critical_count']);

        $alert = $result['alerts'][0];
        $this->assertSame('SCHEDULE_OVERDUE', $alert['category']);
        $this->assertSame('CRITICAL', $alert['severity']);
        $this->assertSame($task->id, $alert['entity_id']);
        $this->assertStringContainsString('Site Drainage Excavation', $alert['title']);
    }

    public function test_does_not_flag_completed_tasks_as_overdue(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-ALRT-2',
            'name' => 'Completed Task Test',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);

        WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.1',
            'name' => 'Completed Foundation',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-05-10',
            'planned_cost' => '20000.00',
            'weight' => '1.00',
            'status' => 'COMPLETED',
            'is_leaf' => true,
        ]);

        $engine = app(EarlyWarningAlertEngine::class);
        $result = $engine->forProject($project, Carbon::parse('2026-05-15'));

        $this->assertSame(0, $result['summary']['total_alerts']);
    }

    public function test_detects_lookahead_blocked_task_with_open_constraints(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-ALRT-3',
            'name' => 'Lookahead Alert Test',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create();

        // Task starting in 5 days from evaluation date (May 15 -> starts May 20)
        $task = WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'wbs_code' => '1.2',
            'name' => 'Retaining Wall Formwork',
            'baseline_start_date' => '2026-05-20',
            'baseline_finish_date' => '2026-05-28',
            'planned_cost' => '30000.00',
            'weight' => '1.00',
            'status' => 'NOT_STARTED',
            'is_leaf' => true,
        ]);

        ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'task_id' => $task->id,
            'type' => ProjectConstraint::TYPE_MATERIAL,
            'title' => 'Formwork timber missing',
            'severity' => ProjectConstraint::SEVERITY_HIGH,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $user->id,
        ]);

        $engine = app(EarlyWarningAlertEngine::class);
        $result = $engine->forProject($project, Carbon::parse('2026-05-15'));

        $lookaheadAlerts = collect($result['alerts'])->where('category', 'LOOKAHEAD_BLOCKED');
        $this->assertCount(1, $lookaheadAlerts);
        $alert = $lookaheadAlerts->first();
        $this->assertSame('WARNING', $alert['severity']);
        $this->assertSame($task->id, $alert['entity_id']);
    }

    public function test_detects_stale_critical_constraint(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-ALRT-4',
            'name' => 'Stale Constraint Test',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create();

        $constraint = ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'type' => ProjectConstraint::TYPE_PERMIT,
            'title' => 'Structural engineering approval overdue',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'target_resolution_date' => '2026-05-10',
            'created_by' => $user->id,
        ]);

        $engine = app(EarlyWarningAlertEngine::class);
        $result = $engine->forProject($project, Carbon::parse('2026-05-15'));

        $constraintAlerts = collect($result['alerts'])->where('category', 'CRITICAL_CONSTRAINT_STALE');
        $this->assertCount(1, $constraintAlerts);
        $alert = $constraintAlerts->first();
        $this->assertSame('CRITICAL', $alert['severity']);
        $this->assertSame($constraint->id, $alert['entity_id']);
    }

    public function test_detects_budget_burnout(): void
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-ALRT-5',
            'name' => 'Budget Burnout Test',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create();

        FinanceTransaction::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'direction' => 'EXPENSE',
            'description' => 'Heavy equipment mobilization',
            'amount' => '95000.00',
            'status' => FinanceStatus::Posted,
            'created_by' => $user->id,
        ]);

        $engine = app(EarlyWarningAlertEngine::class);
        $result = $engine->forProject($project, Carbon::parse('2026-05-15'));

        $financeAlerts = collect($result['alerts'])->where('category', 'BUDGET_BURNOUT');
        $this->assertCount(1, $financeAlerts);
        $alert = $financeAlerts->first();
        $this->assertSame('WARNING', $alert['severity']);
        $this->assertStringContainsString('95.0%', $alert['message']);
    }
}
