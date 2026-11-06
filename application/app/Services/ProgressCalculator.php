<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Collection;

class ProgressCalculator
{
    public const ACTUAL_VERSION = 'CED_PROGRESS_V1';

    public const PLANNED_VERSION = 'CED_PLANNED_PROGRESS_LINEAR_V1';

    public function actual(Collection $tasks, Collection $entries, CarbonImmutable $evaluatedAt): array
    {
        $tasks = $this->baselineLeafTasks($tasks);
        $taskIds = $tasks->pluck('id')->map(fn ($id) => (string) $id)->all();
        $cutoff = $evaluatedAt->toDateString();

        $latestByTask = $entries
            ->filter(fn ($entry) => in_array((string) $entry->task_id, $taskIds, true)
                && $entry->progress_date->toDateString() <= $cutoff)
            ->sortBy(fn ($entry) => implode('|', [
                $entry->progress_date->toDateString(),
                $entry->recorded_at?->format('Y-m-d H:i:s.u') ?? '',
                (string) $entry->id,
            ]))
            ->groupBy(fn ($entry) => (string) $entry->task_id)
            ->map(fn (Collection $history) => $history->last());

        $weightStatus = $this->weightStatus($tasks);
        if ($weightStatus['data_status'] === 'DATA_INCOMPLETE') {
            return [
                'version' => self::ACTUAL_VERSION,
                'data_status' => 'DATA_INCOMPLETE',
                'value' => null,
                'unit' => 'percent',
                'evaluated_at' => $this->timestamp($evaluatedAt),
                'source_scope' => [
                    'task_ids' => $taskIds,
                    'progress_entry_ids' => $latestByTask->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all(),
                ],
                'explanation' => $weightStatus['explanation'],
            ];
        }

        $weightedProgress = $tasks->sum(function ($task) use ($latestByTask): float {
            $entry = $latestByTask->get((string) $task->id);

            return (float) $task->weight * ($entry === null ? 0.0 : (float) $entry->progress_percent);
        });

        return [
            'version' => self::ACTUAL_VERSION,
            'data_status' => 'COMPLETE',
            'value' => round($weightedProgress / $weightStatus['total_weight'], 4),
            'unit' => 'percent',
            'evaluated_at' => $this->timestamp($evaluatedAt),
            'source_scope' => [
                'task_ids' => $taskIds,
                'progress_entry_ids' => $latestByTask->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all(),
            ],
            'explanation' => 'Weighted average of the latest progress record on or before the evaluation date for each baseline leaf task; missing history contributes zero.',
        ];
    }

    public function planned(Collection $tasks, CarbonImmutable $evaluatedAt): array
    {
        $tasks = $this->baselineLeafTasks($tasks);
        $taskIds = $tasks->pluck('id')->map(fn ($id) => (string) $id)->sort()->values()->all();
        $weightStatus = $this->weightStatus($tasks);
        if ($weightStatus['data_status'] === 'DATA_INCOMPLETE') {
            return [
                'version' => self::PLANNED_VERSION,
                'data_status' => 'DATA_INCOMPLETE',
                'value' => null,
                'unit' => 'percent',
                'evaluated_at' => $this->timestamp($evaluatedAt),
                'source_scope' => [
                    'task_ids' => $taskIds,
                ],
                'explanation' => $weightStatus['explanation'],
            ];
        }

        $weightedProgress = $tasks->sum(
            fn ($task): float => (float) $task->weight * $this->linearTaskProgress($task, $evaluatedAt),
        );

        return [
            'version' => self::PLANNED_VERSION,
            'data_status' => 'COMPLETE',
            'value' => round($weightedProgress / $weightStatus['total_weight'], 4),
            'unit' => 'percent',
            'evaluated_at' => $this->timestamp($evaluatedAt),
            'source_scope' => [
                'task_ids' => $taskIds,
            ],
            'explanation' => 'Weighted linear interpolation from 0% at baseline start to 100% at baseline finish for each baseline leaf task.',
        ];
    }

    private function linearTaskProgress(object $task, CarbonImmutable $evaluatedAt): float
    {
        $start = CarbonImmutable::parse($task->baseline_start_date)->startOfDay();
        $finish = CarbonImmutable::parse($task->baseline_finish_date)->startOfDay();
        $at = $evaluatedAt->startOfDay();

        if ($finish->lessThan($start)) {
            throw new DomainException("Task {$task->id} has an invalid baseline date range.");
        }

        if ($start->equalTo($finish)) {
            return $at->lessThan($start) ? 0.0 : 100.0;
        }

        if ($at->lessThanOrEqualTo($start)) {
            return 0.0;
        }

        if ($at->greaterThanOrEqualTo($finish)) {
            return 100.0;
        }

        return 100.0 * $start->diffInDays($at) / $start->diffInDays($finish);
    }

    private function baselineLeafTasks(Collection $tasks): Collection
    {
        $tasks = $tasks
            ->filter(fn ($task): bool => $task->is_baseline !== false && $task->is_leaf !== false)
            ->values();

        if ($tasks->isEmpty()) {
            throw new DomainException('At least one baseline leaf task is required.');
        }

        $taskIds = $tasks->pluck('id')->map(fn ($id) => (string) $id);
        if ($taskIds->unique()->count() !== $taskIds->count()) {
            throw new DomainException('Baseline leaf tasks must be unique to prevent double counting.');
        }

        return $tasks;
    }

    private function weightStatus(Collection $tasks): array
    {
        $missingTaskIds = [];
        foreach ($tasks as $task) {
            if ($task->weight === null) {
                $missingTaskIds[] = (string) $task->id;

                continue;
            }

            if ((float) $task->weight <= 0 || (float) $task->weight > 100) {
                throw new DomainException("Task {$task->id} must have a weight greater than zero and no more than 100.");
            }
        }

        if ($missingTaskIds !== []) {
            sort($missingTaskIds);

            return [
                'data_status' => 'DATA_INCOMPLETE',
                'total_weight' => null,
                'explanation' => 'DATA_INCOMPLETE: every baseline leaf task requires a weight before progress can be aggregated; no weighted subset was calculated. Missing task ids: '.implode(', ', $missingTaskIds).'.',
            ];
        }

        $totalWeight = (float) $tasks->sum(fn ($task) => (float) $task->weight);
        if (abs($totalWeight - 100.0) > 0.0001) {
            return [
                'data_status' => 'DATA_INCOMPLETE',
                'total_weight' => $totalWeight,
                'explanation' => sprintf('DATA_INCOMPLETE: baseline leaf weights total %.4f; exactly 100.0000 is required, so no partial aggregate was calculated.', $totalWeight),
            ];
        }

        return [
            'data_status' => 'COMPLETE',
            'total_weight' => $totalWeight,
            'explanation' => null,
        ];
    }

    private function timestamp(CarbonImmutable $evaluatedAt): string
    {
        return $evaluatedAt->utc()->format('Y-m-d\TH:i:s\Z');
    }
}
