<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectConstraint;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyLookaheadService
{
    public const VERSION = 'CED_WEEKLY_LOOKAHEAD_V1';

    /**
     * @return array{
     *     version: string,
     *     project: array{id: string, code: string, name: string},
     *     window: array{weeks_count: int, start_date: string, end_date: string},
     *     weeks: list<array{week_number: int, label: string, start_date: string, end_date: string}>,
     *     tasks: list<array{
     *         id: string,
     *         wbs_code: string,
     *         name: string,
     *         status: string,
     *         baseline_start_date: string,
     *         baseline_finish_date: string,
     *         weight: string|null,
     *         progress_percent: float,
     *         is_make_ready: bool,
     *         open_constraints: list<array{id: string, type: string, title: string, severity: string, status: string}>,
     *         active_weeks: list<int>
     *     }>,
     *     summary: array{
     *         total_tasks: int,
     *         make_ready_count: int,
     *         constrained_count: int,
     *         critical_blockers_count: int,
     *         make_ready_rate: float
     *     }
     * }
     */
    public function forProject(Project $project, int $weeks = 3, ?Carbon $anchorDate = null): array
    {
        $weeks = max(1, min(12, $weeks));
        $anchor = $anchorDate ? $anchorDate->copy() : now();
        $windowStart = $anchor->copy()->startOfWeek();
        $windowEnd = $windowStart->copy()->addWeeks($weeks)->subDay()->endOfDay();

        $weekBuckets = [];
        for ($w = 0; $w < $weeks; $w++) {
            $wStart = $windowStart->copy()->addWeeks($w)->startOfDay();
            $wEnd = $wStart->copy()->addDays(6)->endOfDay();
            $weekBuckets[] = [
                'week_number' => $w + 1,
                'label' => 'W'.($w + 1).' ('.$wStart->format('d M').' - '.$wEnd->format('d M').')',
                'start_date' => $wStart->toDateString(),
                'end_date' => $wEnd->toDateString(),
            ];
        }

        $wbsTasks = $project->wbsTasks()
            ->with(['constraints' => fn ($q) => $q->open()])
            ->where('is_leaf', true)
            ->whereDate('baseline_start_date', '<=', $windowEnd->toDateString())
            ->whereDate('baseline_finish_date', '>=', $windowStart->toDateString())
            ->orderBy('baseline_start_date')
            ->orderBy('wbs_code')
            ->get();

        $latestProgressByTask = $project->progressEntries()
            ->whereIn('task_id', $wbsTasks->pluck('id'))
            ->orderBy('progress_date')
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($entry): string => (string) $entry->task_id)
            ->map(fn (Collection $history) => $history->last());

        $tasks = [];
        $makeReadyCount = 0;
        $constrainedCount = 0;
        $criticalBlockersCount = 0;

        foreach ($wbsTasks as $task) {
            $progress = $latestProgressByTask->get((string) $task->id);
            $progressPercent = $progress !== null ? (float) $progress->progress_percent : 0.0;

            $openConstraints = $task->constraints->map(fn (ProjectConstraint $c): array => [
                'id' => $c->id,
                'type' => $c->type,
                'title' => $c->title,
                'severity' => $c->severity,
                'status' => $c->status,
            ])->values()->all();

            $isMakeReady = count($openConstraints) === 0;

            if ($isMakeReady) {
                $makeReadyCount++;
            } else {
                $constrainedCount++;
                foreach ($openConstraints as $c) {
                    if ($c['severity'] === ProjectConstraint::SEVERITY_CRITICAL) {
                        $criticalBlockersCount++;
                    }
                }
            }

            // Determine active weeks
            $activeWeeks = [];
            $taskStart = Carbon::parse($task->baseline_start_date);
            $taskFinish = Carbon::parse($task->baseline_finish_date);

            foreach ($weekBuckets as $wb) {
                $bStart = Carbon::parse($wb['start_date']);
                $bEnd = Carbon::parse($wb['end_date']);
                if ($taskStart->lte($bEnd) && $taskFinish->gte($bStart)) {
                    $activeWeeks[] = $wb['week_number'];
                }
            }

            $tasks[] = [
                'id' => $task->id,
                'wbs_code' => $task->wbs_code,
                'name' => $task->name,
                'status' => $task->status,
                'baseline_start_date' => $task->baseline_start_date?->toDateString() ?? '',
                'baseline_finish_date' => $task->baseline_finish_date?->toDateString() ?? '',
                'weight' => $task->weight !== null ? (string) $task->weight : null,
                'progress_percent' => $progressPercent,
                'is_make_ready' => $isMakeReady,
                'open_constraints' => $openConstraints,
                'active_weeks' => $activeWeeks,
            ];
        }

        $totalTasks = count($tasks);
        $makeReadyRate = $totalTasks > 0 ? round(($makeReadyCount / $totalTasks) * 100, 1) : 100.0;

        return [
            'version' => self::VERSION,
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
            ],
            'window' => [
                'weeks_count' => $weeks,
                'start_date' => $windowStart->toDateString(),
                'end_date' => $windowEnd->toDateString(),
            ],
            'weeks' => $weekBuckets,
            'tasks' => $tasks,
            'summary' => [
                'total_tasks' => $totalTasks,
                'make_ready_count' => $makeReadyCount,
                'constrained_count' => $constrainedCount,
                'critical_blockers_count' => $criticalBlockersCount,
                'make_ready_rate' => $makeReadyRate,
            ],
        ];
    }
}
