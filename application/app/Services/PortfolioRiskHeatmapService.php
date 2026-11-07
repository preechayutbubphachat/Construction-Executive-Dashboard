<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectConstraint;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PortfolioRiskHeatmapService
{
    public const VERSION = 'CED_PORTFOLIO_RISK_HEATMAP_V1';

    public function __construct(
        protected CedProjectHealthV1ReadModel $healthReadModel,
        protected CedFinanceV1ReadModel $financeReadModel,
        protected WeeklyLookaheadService $lookaheadService,
        protected EarlyWarningAlertEngine $alertEngine,
    ) {}

    /**
     * @param  Collection<int, Project>  $projects
     * @return array{
     *     version: string,
     *     evaluated_at: string,
     *     summary: array{
     *         total_projects: int,
     *         critical_count: int,
     *         high_count: int,
     *         medium_count: int,
     *         low_count: int,
     *         total_budget: float,
     *         total_expense: float,
     *         average_make_ready_rate: float
     *     },
     *     projects: list<array{
     *         id: string,
     *         code: string,
     *         name: string,
     *         health_status: string,
     *         risk_score: int,
     *         risk_level: string,
     *         quadrant: string,
     *         x_schedule_risk: int,
     *         y_finance_constraint_risk: int,
     *         make_ready_rate: float,
     *         lookahead_tasks_count: int,
     *         open_constraints_count: int,
     *         critical_constraints_count: int,
     *         alerts_count: int,
     *         critical_alerts_count: int,
     *         budget_amount: float,
     *         posted_expense: float,
     *         budget_utilization_rate: float
     *     }>
     * }
     */
    public function forProjects(Collection $projects, ?Carbon $now = null): array
    {
        $evalTime = $now ? $now->copy() : now();
        $items = [];
        $totalBudget = 0.0;
        $totalExpense = 0.0;
        $totalMakeReadyRate = 0.0;
        $criticalCount = 0;
        $highCount = 0;
        $mediumCount = 0;
        $lowCount = 0;

        foreach ($projects as $project) {
            $health = $this->healthReadModel->forProject($project);
            $finance = $this->financeReadModel->forProject($project);
            $lookahead = $this->lookaheadService->forProject($project, 3, $evalTime);
            $alertData = $this->alertEngine->forProject($project, $evalTime);

            $budget = (float) ($project->budget_amount ?? 0);
            $expense = (float) ($finance['expense'] ?? 0);
            $utilization = $budget > 0 ? ($expense / $budget) * 100 : 0.0;

            $makeReadyRate = (float) $lookahead['summary']['make_ready_rate'];
            $totalMakeReadyRate += $makeReadyRate;
            $totalBudget += $budget;
            $totalExpense += $expense;

            $openConstraints = $project->constraints()->open()->count();
            $criticalConstraints = $project->constraints()->open()->where('severity', ProjectConstraint::SEVERITY_CRITICAL)->count();

            $alertsCount = $alertData['summary']['total_alerts'];
            $criticalAlerts = $alertData['summary']['critical_count'];

            $xScheduleRisk = 0;
            if ($health['status'] === 'RED') {
                $xScheduleRisk += 40;
            } elseif ($health['status'] === 'AMBER') {
                $xScheduleRisk += 20;
            }
            if ($makeReadyRate < 60) {
                $xScheduleRisk += 30;
            } elseif ($makeReadyRate < 80) {
                $xScheduleRisk += 15;
            }
            $xScheduleRisk += min(30, $criticalAlerts * 15);
            $xScheduleRisk = min(100, max(0, $xScheduleRisk));

            $yFinanceConstraintRisk = 0;
            if ($utilization >= 100.0) {
                $yFinanceConstraintRisk += 40;
            } elseif ($utilization >= 90.0) {
                $yFinanceConstraintRisk += 25;
            } elseif ($utilization >= 75.0) {
                $yFinanceConstraintRisk += 10;
            }
            $yFinanceConstraintRisk += min(40, $criticalConstraints * 20);
            $yFinanceConstraintRisk += min(20, $openConstraints * 5);
            $yFinanceConstraintRisk = min(100, max(0, $yFinanceConstraintRisk));

            $compositeRiskScore = (int) round(($xScheduleRisk * 0.5) + ($yFinanceConstraintRisk * 0.5));

            if ($compositeRiskScore >= 60 || ($health['status'] === 'RED' && $criticalConstraints > 0)) {
                $riskLevel = 'CRITICAL';
                $criticalCount++;
            } elseif ($compositeRiskScore >= 40) {
                $riskLevel = 'HIGH';
                $highCount++;
            } elseif ($compositeRiskScore >= 20) {
                $riskLevel = 'MEDIUM';
                $mediumCount++;
            } else {
                $riskLevel = 'LOW';
                $lowCount++;
            }

            if ($xScheduleRisk >= 50 && $yFinanceConstraintRisk >= 50) {
                $quadrant = 'CRITICAL_QUADRANT';
            } elseif ($xScheduleRisk >= 50 && $yFinanceConstraintRisk < 50) {
                $quadrant = 'SCHEDULE_RISK_QUADRANT';
            } elseif ($xScheduleRisk < 50 && $yFinanceConstraintRisk >= 50) {
                $quadrant = 'FINANCE_CONSTRAINT_RISK_QUADRANT';
            } else {
                $quadrant = 'SOUND_QUADRANT';
            }

            $items[] = [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'health_status' => $health['status'],
                'risk_score' => $compositeRiskScore,
                'risk_level' => $riskLevel,
                'quadrant' => $quadrant,
                'x_schedule_risk' => $xScheduleRisk,
                'y_finance_constraint_risk' => $yFinanceConstraintRisk,
                'make_ready_rate' => $makeReadyRate,
                'lookahead_tasks_count' => $lookahead['summary']['total_tasks'],
                'open_constraints_count' => $openConstraints,
                'critical_constraints_count' => $criticalConstraints,
                'alerts_count' => $alertsCount,
                'critical_alerts_count' => $criticalAlerts,
                'budget_amount' => $budget,
                'posted_expense' => $expense,
                'budget_utilization_rate' => round($utilization, 1),
            ];
        }

        usort($items, fn ($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        $count = count($items);
        $avgMakeReadyRate = $count > 0 ? round($totalMakeReadyRate / $count, 1) : 0.0;

        return [
            'version' => self::VERSION,
            'evaluated_at' => $evalTime->toIso8601String(),
            'summary' => [
                'total_projects' => $count,
                'critical_count' => $criticalCount,
                'high_count' => $highCount,
                'medium_count' => $mediumCount,
                'low_count' => $lowCount,
                'total_budget' => round($totalBudget, 2),
                'total_expense' => round($totalExpense, 2),
                'average_make_ready_rate' => $avgMakeReadyRate,
            ],
            'projects' => $items,
        ];
    }
}
