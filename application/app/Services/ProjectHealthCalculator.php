<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class ProjectHealthCalculator
{
    public const VERSION = 'CED_HEALTH_V1_0_0';

    private const RANK = ['GREEN' => 0, 'UNKNOWN' => 1, 'AMBER' => 2, 'RED' => 3];

    public function evaluate(
        ?float $scheduleVariancePoints,
        ?float $costUsagePercent,
        int $highRiskCount,
        ?float $freshnessAgeHours,
        CarbonImmutable $evaluatedAt,
        array $sourceScope,
    ): array {
        $dimensions = [
            'SCHEDULE' => $this->schedule($scheduleVariancePoints),
            'COST' => $this->cost($costUsagePercent),
            'RISK' => $this->risk($highRiskCount),
            'DATA_FRESHNESS' => $this->freshness($freshnessAgeHours),
        ];

        $overall = collect($dimensions)
            ->sortByDesc(fn (array $dimension) => self::RANK[$dimension['status']])
            ->first()['status'];

        return [
            'version' => self::VERSION,
            'formula_version' => self::VERSION,
            'overall_status' => $overall,
            'dimensions' => $dimensions,
            'reasons' => collect($dimensions)->mapWithKeys(
                fn (array $dimension, string $name) => [$name => $dimension['reason']],
            )->all(),
            'evaluated_at' => $evaluatedAt->utc()->format('Y-m-d\TH:i:s\Z'),
            'source_scope' => $sourceScope,
        ];
    }

    private function schedule(?float $variance): array
    {
        if ($variance === null) {
            return [
                'status' => 'UNKNOWN',
                'value' => null,
                'unit' => 'percentage_points',
                'reason' => 'Schedule status is unknown because progress weights are incomplete.',
            ];
        }

        $status = $variance <= -15.0 ? 'RED' : ($variance <= -5.0 ? 'AMBER' : 'GREEN');

        return [
            'status' => $status,
            'value' => round($variance, 4),
            'unit' => 'percentage_points',
            'reason' => sprintf('Actual minus planned is %.2f pp; amber <= -5.00 pp, red <= -15.00 pp.', $variance),
        ];
    }

    private function cost(?float $usage): array
    {
        if ($usage === null) {
            return [
                'status' => 'UNKNOWN',
                'value' => null,
                'unit' => 'percent_of_budget',
                'reason' => 'Cost status is unknown because no positive project budget is recorded.',
            ];
        }

        $status = $usage > 100.0 ? 'RED' : ($usage >= 90.0 ? 'AMBER' : 'GREEN');

        return [
            'status' => $status,
            'value' => round($usage, 4),
            'unit' => 'percent_of_budget',
            'reason' => sprintf('Posted expense is %.2f%% of budget; amber >= 90.00%%, red > 100.00%%.', $usage),
        ];
    }

    private function risk(int $count): array
    {
        $status = $count >= 2 ? 'RED' : ($count >= 1 ? 'AMBER' : 'GREEN');

        return [
            'status' => $status,
            'value' => $count,
            'unit' => 'open_high_or_critical_risks',
            'reason' => sprintf('%d open high/critical risk(s); amber >= 1, red >= 2.', $count),
        ];
    }

    private function freshness(?float $age): array
    {
        if ($age === null) {
            return [
                'status' => 'UNKNOWN',
                'value' => null,
                'unit' => 'hours_since_progress',
                'reason' => 'Data freshness status is unknown because no progress history is available.',
            ];
        }

        $status = $age > 168.0 ? 'RED' : ($age > 72.0 ? 'AMBER' : 'GREEN');

        return [
            'status' => $status,
            'value' => round($age, 4),
            'unit' => 'hours_since_progress',
            'reason' => sprintf('Latest progress is %.2f hours old; amber > 72.00 hours, red > 168.00 hours.', $age),
        ];
    }
}
