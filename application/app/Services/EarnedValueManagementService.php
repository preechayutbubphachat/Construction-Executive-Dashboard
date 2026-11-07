<?php

namespace App\Services;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\WbsTask;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class EarnedValueManagementService
{
    public const VERSION = 'CED_EVM_SCURVE_V1';

    public function __construct(
        private readonly ProgressCalculator $progressCalculator = new ProgressCalculator,
        private readonly CedFinanceV1ReadModel $financeReadModel = new CedFinanceV1ReadModel,
    ) {}

    /**
     * Calculates comprehensive Earned Value Management (EVM) metrics,
     * performance indices, completion forecasts, and weekly S-Curve time series.
     *
     * @return array<string, mixed>
     */
    public function calculate(Project $project, ?CarbonImmutable $asOf = null): array
    {
        $evaluatedAt = $asOf ?? CarbonImmutable::now();
        $bac = (float) $project->budget_amount;

        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_baseline', true)
            ->get();

        $entries = ProgressEntry::query()
            ->where('project_id', $project->id)
            ->get();

        // 1. Progress Percents
        $actualProgressResult = $this->progressCalculator->actual($tasks, $entries, $evaluatedAt);
        $actualProgressPercent = (float) ($actualProgressResult['value'] ?? 0.0);

        $plannedProgressResult = $this->progressCalculator->planned($tasks, $evaluatedAt);
        $plannedProgressPercent = (float) ($plannedProgressResult['value'] ?? 0.0);

        // 2. Financial Costs
        $financeData = $this->financeReadModel->forProject($project);
        $ac = (float) ($financeData['expense'] ?? 0.0);

        // 3. Core EVM Values
        $pv = round($bac * ($plannedProgressPercent / 100.0), 2);
        $ev = round($bac * ($actualProgressPercent / 100.0), 2);

        // 4. Variances
        $cv = round($ev - $ac, 2);
        $sv = round($ev - $pv, 2);

        // 5. Performance Indices
        $cpi = $ac > 0 ? round($ev / $ac, 4) : 1.0;
        $spi = $pv > 0 ? round($ev / $pv, 4) : 1.0;

        // 6. Forecasts At Completion
        $eac = $cpi > 0.01 ? round($bac / $cpi, 2) : $bac;
        $vac = round($bac - $eac, 2);
        $etc = max(0.0, round($eac - $ac, 2));

        $remainingWork = $bac - $ev;
        $remainingFunds = $bac - $ac;
        $tcpi = $remainingFunds > 0 ? round($remainingWork / $remainingFunds, 4) : 1.0;

        // Schedule Forecast
        $startDate = CarbonImmutable::parse($project->baseline_start_date);
        $finishDate = CarbonImmutable::parse($project->baseline_finish_date);
        $totalBaselineDays = max(1, $startDate->diffInDays($finishDate));
        $projectedDays = $spi > 0.05 ? (int) round($totalBaselineDays / $spi) : $totalBaselineDays;
        $estimatedFinishDate = $startDate->addDays($projectedDays)->toDateString();

        // Health Classifications
        $costHealth = $cpi >= 1.0 ? 'HEALTHY' : ($cpi >= 0.90 ? 'WARNING' : 'CRITICAL');
        $scheduleHealth = $spi >= 1.0 ? 'HEALTHY' : ($spi >= 0.90 ? 'WARNING' : 'CRITICAL');

        // 7. Weekly S-Curve Time Series
        $sCurveSeries = $this->generateSCurveSeries($project, $tasks, $entries, $bac, $evaluatedAt, $cpi, $spi);

        return [
            'version' => self::VERSION,
            'project_id' => $project->id,
            'project_code' => $project->code,
            'evaluated_at' => $evaluatedAt->toIso8601String(),
            'progress' => [
                'planned_percent' => $plannedProgressPercent,
                'actual_percent' => $actualProgressPercent,
                'variance_percent' => round($actualProgressPercent - $plannedProgressPercent, 2),
            ],
            'metrics' => [
                'bac' => $bac,
                'pv' => $pv,
                'ev' => $ev,
                'ac' => $ac,
            ],
            'variances' => [
                'cv' => $cv,
                'sv' => $sv,
                'is_cost_overrun' => $cv < 0,
                'is_schedule_delayed' => $sv < 0,
            ],
            'indices' => [
                'cpi' => $cpi,
                'spi' => $spi,
                'tcpi' => $tcpi,
                'cost_health' => $costHealth,
                'schedule_health' => $scheduleHealth,
            ],
            'forecasts' => [
                'eac' => $eac,
                'vac' => $vac,
                'etc' => $etc,
                'baseline_finish_date' => $project->baseline_finish_date->toDateString(),
                'estimated_finish_date' => $estimatedFinishDate,
                'delay_days' => max(0, $projectedDays - $totalBaselineDays),
            ],
            's_curve' => [
                'series' => $sCurveSeries,
            ],
        ];
    }

    /**
     * @param  Collection<int, WbsTask>  $tasks
     * @param  Collection<int, ProgressEntry>  $entries
     * @return list<array<string, mixed>>
     */
    private function generateSCurveSeries(
        Project $project,
        $tasks,
        $entries,
        float $bac,
        CarbonImmutable $evaluatedAt,
        float $cpi,
        float $spi,
    ): array {
        $startDate = CarbonImmutable::parse($project->baseline_start_date)->startOfWeek();
        $finishDate = CarbonImmutable::parse($project->baseline_finish_date)->endOfWeek();

        // Retrieve all posted expenses with their posted dates
        $postedExpenses = FinanceTransaction::query()
            ->where('project_id', $project->id)
            ->where('direction', 'EXPENSE')
            ->where('status', FinanceStatus::Posted)
            ->get();

        $series = [];
        $currentDate = $startDate;
        $weekNumber = 1;

        while ($currentDate->lte($finishDate)) {
            $dateString = $currentDate->toDateString();
            $isPastOrToday = $currentDate->lte($evaluatedAt);

            // Planned Value at this week
            $plannedRes = $this->progressCalculator->planned($tasks, $currentDate);
            $plannedPct = (float) ($plannedRes['value'] ?? 0.0);
            $pvWeek = round($bac * ($plannedPct / 100.0), 2);

            $evWeek = null;
            $acWeek = null;

            if ($isPastOrToday) {
                // Actual Value at this week
                $actRes = $this->progressCalculator->actual($tasks, $entries, $currentDate);
                $actPct = (float) ($actRes['value'] ?? 0.0);
                $evWeek = round($bac * ($actPct / 100.0), 2);

                // Actual Cost accumulated up to this week
                $acWeek = (float) $postedExpenses
                    ->filter(fn ($tx) => $tx->posted_at && CarbonImmutable::parse($tx->posted_at)->lte($currentDate->endOfDay()))
                    ->sum('amount');
            }

            // Projected S-Curve (Forecast)
            $forecastEv = null;
            if ($isPastOrToday) {
                $forecastEv = $evWeek;
            } else {
                // Forecast continues from current EV using SPI trajectory
                $forecastEv = min($bac, round(($evWeek ?? $pvWeek) * $spi, 2));
            }

            $series[] = [
                'week_number' => $weekNumber,
                'date' => $dateString,
                'is_actual' => $isPastOrToday,
                'pv' => $pvWeek,
                'ev' => $evWeek,
                'ac' => $acWeek,
                'forecast_ev' => $forecastEv,
            ];

            $currentDate = $currentDate->addWeek();
            $weekNumber++;
        }

        return $series;
    }
}
