<?php

namespace Tests\Unit;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\PortfolioRiskHeatmapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioRiskHeatmapServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_portfolio_risk_heatmap_calculates_scores_and_quadrants(): void
    {
        $user = User::factory()->create();

        // Project 1: Low risk healthy project
        $p1 = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-HTM-1',
            'name' => 'Low Risk Hospital',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-08-31',
            'budget_amount' => '500000.00',
            'status' => 'ACTIVE',
        ]);
        $p1->members()->attach($user, ['role' => 'PROJECT_MEMBER']);

        WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $p1->id,
            'wbs_code' => '1.1',
            'name' => 'Survey & Staking',
            'baseline_start_date' => '2026-05-02',
            'baseline_finish_date' => '2026-05-20',
            'planned_cost' => '30000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        // Project 2: High/Critical risk project with overdue tasks, critical constraint & budget burnout
        $p2 = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-HTM-2',
            'name' => 'Critical Risk Bridge',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-07-31',
            'budget_amount' => '200000.00',
            'status' => 'ACTIVE',
        ]);
        $p2->members()->attach($user, ['role' => 'PROJECT_MEMBER']);

        // Overdue task
        WbsTask::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $p2->id,
            'wbs_code' => '1.1',
            'name' => 'Pier Foundation',
            'baseline_start_date' => '2026-05-01',
            'baseline_finish_date' => '2026-05-05',
            'planned_cost' => '80000.00',
            'weight' => '1.00',
            'status' => 'IN_PROGRESS',
            'is_leaf' => true,
        ]);

        // Critical open constraint
        ProjectConstraint::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $p2->id,
            'type' => ProjectConstraint::TYPE_SITE_ACCESS,
            'title' => 'River barge access blocked by municipality',
            'severity' => ProjectConstraint::SEVERITY_CRITICAL,
            'status' => ProjectConstraint::STATUS_OPEN,
            'created_by' => $user->id,
        ]);

        // Expense exceeding budget (220,000 / 200,000 = 110%)
        FinanceTransaction::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $p2->id,
            'direction' => 'EXPENSE',
            'description' => 'Emergency cofferdam sheet piling',
            'amount' => '220000.00',
            'status' => FinanceStatus::Posted,
            'created_by' => $user->id,
        ]);

        $service = app(PortfolioRiskHeatmapService::class);
        $evalTime = Carbon::parse('2026-05-15');
        $result = $service->forProjects(collect([$p1, $p2]), $evalTime);

        $this->assertSame(2, $result['summary']['total_projects']);
        $this->assertSame(1, $result['summary']['critical_count']);
        $this->assertSame(1, $result['summary']['low_count']);
        $this->assertSame(700000.0, (float) $result['summary']['total_budget']);

        $byCode = collect($result['projects'])->keyBy('code');
        $this->assertSame('LOW', $byCode->get('CED-HTM-1')['risk_level']);
        $this->assertSame('CRITICAL', $byCode->get('CED-HTM-2')['risk_level']);
        $this->assertGreaterThan(60, $byCode->get('CED-HTM-2')['risk_score']);
        $this->assertSame('CRITICAL_QUADRANT', $byCode->get('CED-HTM-2')['quadrant']);
    }

    public function test_empty_portfolio_returns_safe_defaults(): void
    {
        $service = app(PortfolioRiskHeatmapService::class);
        $result = $service->forProjects(collect([]));

        $this->assertSame(0, $result['summary']['total_projects']);
        $this->assertSame(0, $result['summary']['critical_count']);
        $this->assertSame(0.0, (float) $result['summary']['average_make_ready_rate']);
        $this->assertEmpty($result['projects']);
    }
}
