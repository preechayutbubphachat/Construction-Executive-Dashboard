<?php

namespace Tests\Unit;

use App\Services\ProjectHealthCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ProjectHealthCalculatorTest extends TestCase
{
    public function test_schedule_threshold_boundaries_are_deterministic(): void
    {
        $cases = [
            [-15.0, 'RED'],
            [-14.9999, 'AMBER'],
            [-5.0, 'AMBER'],
            [-4.9999, 'GREEN'],
        ];

        foreach ($cases as [$variance, $expectedStatus]) {
            $result = $this->evaluate(scheduleVariancePoints: $variance);

            self::assertSame($expectedStatus, $result['dimensions']['SCHEDULE']['status']);
        }
    }

    public function test_cost_threshold_boundaries_are_deterministic(): void
    {
        $cases = [
            [89.9999, 'GREEN'],
            [90.0, 'AMBER'],
            [100.0, 'AMBER'],
            [100.0001, 'RED'],
        ];

        foreach ($cases as [$usage, $expectedStatus]) {
            $result = $this->evaluate(costUsagePercent: $usage);

            self::assertSame($expectedStatus, $result['dimensions']['COST']['status']);
        }
    }

    public function test_risk_count_mappings_are_deterministic(): void
    {
        $cases = [
            [0, 'GREEN'],
            [1, 'AMBER'],
            [2, 'RED'],
            [3, 'RED'],
        ];

        foreach ($cases as [$count, $expectedStatus]) {
            $result = $this->evaluate(highRiskCount: $count);

            self::assertSame($expectedStatus, $result['dimensions']['RISK']['status']);
        }
    }

    public function test_freshness_threshold_boundaries_are_deterministic(): void
    {
        $cases = [
            [72.0, 'GREEN'],
            [72.0001, 'AMBER'],
            [168.0, 'AMBER'],
            [168.0001, 'RED'],
        ];

        foreach ($cases as [$age, $expectedStatus]) {
            $result = $this->evaluate(freshnessAgeHours: $age);

            self::assertSame($expectedStatus, $result['dimensions']['DATA_FRESHNESS']['status']);
        }
    }

    public function test_missing_required_data_propagates_unknown_and_never_green(): void
    {
        $scheduleUnknown = $this->evaluate(scheduleVariancePoints: null);
        $costUnknown = $this->evaluate(costUsagePercent: null);
        $freshnessUnknown = $this->evaluate(freshnessAgeHours: null);

        self::assertSame('UNKNOWN', $scheduleUnknown['dimensions']['SCHEDULE']['status']);
        self::assertSame('UNKNOWN', $scheduleUnknown['overall_status']);
        self::assertSame('UNKNOWN', $costUnknown['dimensions']['COST']['status']);
        self::assertSame('UNKNOWN', $costUnknown['overall_status']);
        self::assertSame('UNKNOWN', $freshnessUnknown['dimensions']['DATA_FRESHNESS']['status']);
        self::assertSame('UNKNOWN', $freshnessUnknown['overall_status']);
    }

    public function test_overall_status_uses_red_amber_unknown_green_precedence(): void
    {
        self::assertSame('GREEN', $this->evaluate()['overall_status']);
        self::assertSame('UNKNOWN', $this->evaluate(scheduleVariancePoints: null)['overall_status']);
        self::assertSame('AMBER', $this->evaluate(scheduleVariancePoints: null, highRiskCount: 1)['overall_status']);
        self::assertSame('RED', $this->evaluate(scheduleVariancePoints: null, highRiskCount: 2)['overall_status']);
    }

    public function test_result_binds_formula_version_reasons_timestamp_and_source_scope(): void
    {
        $sourceScope = [
            'project_id' => 'CED-PROJECT-001',
            'dimensions' => [
                'SCHEDULE' => ['task_ids' => ['CED-TASK-001']],
                'COST' => ['finance_transaction_ids' => ['CED-FIN-001']],
                'RISK' => ['risk_ids' => ['CED-RISK-001']],
                'DATA_FRESHNESS' => ['latest_progress_entry_id' => 'CED-PROGRESS-001'],
            ],
        ];
        $result = $this->evaluate(
            scheduleVariancePoints: -5.0,
            costUsagePercent: 90.0,
            highRiskCount: 1,
            freshnessAgeHours: 72.0,
            sourceScope: $sourceScope,
        );

        self::assertSame(ProjectHealthCalculator::VERSION, $result['version']);
        self::assertSame(ProjectHealthCalculator::VERSION, $result['formula_version']);
        self::assertSame(['SCHEDULE', 'COST', 'RISK', 'DATA_FRESHNESS'], array_keys($result['dimensions']));
        self::assertSame(['SCHEDULE', 'COST', 'RISK', 'DATA_FRESHNESS'], array_keys($result['reasons']));
        self::assertStringContainsString('amber <= -5.00 pp', $result['reasons']['SCHEDULE']);
        self::assertStringContainsString('amber >= 90.00%', $result['reasons']['COST']);
        self::assertStringContainsString('amber >= 1', $result['reasons']['RISK']);
        self::assertStringContainsString('amber > 72.00 hours', $result['reasons']['DATA_FRESHNESS']);
        self::assertSame('2026-03-01T00:00:00Z', $result['evaluated_at']);
        self::assertSame($sourceScope, $result['source_scope']);
    }

    public function test_unknown_dimensions_have_explainable_reasons(): void
    {
        $result = $this->evaluate(
            scheduleVariancePoints: null,
            costUsagePercent: null,
            freshnessAgeHours: null,
        );

        self::assertNull($result['dimensions']['SCHEDULE']['value']);
        self::assertStringContainsString('progress weights are incomplete', $result['reasons']['SCHEDULE']);
        self::assertStringContainsString('no positive project budget', $result['reasons']['COST']);
        self::assertStringContainsString('no progress history', $result['reasons']['DATA_FRESHNESS']);
    }

    private function evaluate(
        ?float $scheduleVariancePoints = 0.0,
        ?float $costUsagePercent = 0.0,
        int $highRiskCount = 0,
        ?float $freshnessAgeHours = 0.0,
        array $sourceScope = ['project_id' => 'CED-PROJECT-001'],
    ): array {
        return (new ProjectHealthCalculator)->evaluate(
            scheduleVariancePoints: $scheduleVariancePoints,
            costUsagePercent: $costUsagePercent,
            highRiskCount: $highRiskCount,
            freshnessAgeHours: $freshnessAgeHours,
            evaluatedAt: CarbonImmutable::parse('2026-03-01T00:00:00Z'),
            sourceScope: $sourceScope,
        );
    }
}
