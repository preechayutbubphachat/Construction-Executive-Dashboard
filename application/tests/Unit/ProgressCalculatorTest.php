<?php

namespace Tests\Unit;

use App\Models\ProgressEntry;
use App\Models\WbsTask;
use App\Services\ProgressCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class ProgressCalculatorTest extends TestCase
{
    public function test_latest_history_is_weighted_and_planned_progress_is_linear(): void
    {
        $tasks = collect([
            new WbsTask([
                'id' => 'TASK-A',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-01-11',
                'weight' => '60.0000',
            ]),
            new WbsTask([
                'id' => 'TASK-B',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-01-11',
                'weight' => '40.0000',
            ]),
        ]);
        $entries = collect([
            new ProgressEntry([
                'id' => 'PROGRESS-A-OLD',
                'task_id' => 'TASK-A',
                'progress_date' => '2026-01-03',
                'progress_percent' => '20.00',
                'recorded_at' => '2026-01-03 08:00:00',
            ]),
            new ProgressEntry([
                'id' => 'PROGRESS-A-LATEST',
                'task_id' => 'TASK-A',
                'progress_date' => '2026-01-06',
                'progress_percent' => '50.00',
                'recorded_at' => '2026-01-06 08:00:00',
            ]),
            new ProgressEntry([
                'id' => 'PROGRESS-B-LATEST',
                'task_id' => 'TASK-B',
                'progress_date' => '2026-01-05',
                'progress_percent' => '25.00',
                'recorded_at' => '2026-01-05 08:00:00',
            ]),
            new ProgressEntry([
                'id' => 'PROGRESS-B-FUTURE',
                'task_id' => 'TASK-B',
                'progress_date' => '2026-01-07',
                'progress_percent' => '90.00',
                'recorded_at' => '2026-01-07 08:00:00',
            ]),
        ]);
        $evaluatedAt = CarbonImmutable::parse('2026-01-06T12:00:00Z');
        $calculator = new ProgressCalculator;

        $actual = $calculator->actual($tasks, $entries, $evaluatedAt);
        $planned = $calculator->planned($tasks, $evaluatedAt);

        self::assertSame(ProgressCalculator::ACTUAL_VERSION, $actual['version']);
        self::assertSame('COMPLETE', $actual['data_status']);
        self::assertSame(40.0, $actual['value']);
        self::assertSame(
            ['PROGRESS-A-LATEST', 'PROGRESS-B-LATEST'],
            $actual['source_scope']['progress_entry_ids'],
        );
        self::assertSame(ProgressCalculator::PLANNED_VERSION, $planned['version']);
        self::assertSame('COMPLETE', $planned['data_status']);
        self::assertSame(50.0, $planned['value']);
        self::assertSame('2026-01-06T12:00:00Z', $planned['evaluated_at']);
    }

    public function test_single_day_task_is_zero_before_start_and_complete_on_start(): void
    {
        $tasks = collect([
            new WbsTask([
                'id' => 'TASK-SINGLE-DAY',
                'baseline_start_date' => '2026-02-10',
                'baseline_finish_date' => '2026-02-10',
                'weight' => '100.0000',
            ]),
        ]);
        $calculator = new ProgressCalculator;

        self::assertSame(0.0, $calculator->planned($tasks, CarbonImmutable::parse('2026-02-09'))['value']);
        self::assertSame(100.0, $calculator->planned($tasks, CarbonImmutable::parse('2026-02-10'))['value']);
    }

    public function test_mixed_leaf_weights_are_data_incomplete_and_non_leaf_weight_is_not_double_counted(): void
    {
        $tasks = collect([
            new WbsTask([
                'id' => 'PARENT',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-01-11',
                'weight' => '100.0000',
                'is_leaf' => false,
            ]),
            new WbsTask([
                'id' => 'LEAF-A',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-01-11',
                'weight' => '60.0000',
                'is_leaf' => true,
            ]),
            new WbsTask([
                'id' => 'LEAF-B',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-01-11',
                'weight' => null,
                'is_leaf' => true,
            ]),
        ]);
        $calculator = new ProgressCalculator;

        $result = $calculator->planned($tasks, CarbonImmutable::parse('2026-01-06'));

        self::assertSame('DATA_INCOMPLETE', $result['data_status']);
        self::assertNull($result['value']);
        self::assertSame(['LEAF-A', 'LEAF-B'], $result['source_scope']['task_ids']);
        self::assertStringContainsString('no weighted subset was calculated', $result['explanation']);
    }
}
