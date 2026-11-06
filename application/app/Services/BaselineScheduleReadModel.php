<?php

namespace App\Services;

use App\Models\Project;
use App\Models\WbsTask;
use Illuminate\Support\Collection;

class BaselineScheduleReadModel
{
    public const VERSION = 'CED_BASELINE_SCHEDULE_V1';

    /**
     * @return array{
     *     version: string,
     *     project: array{
     *         id: string,
     *         code: string,
     *         name: string,
     *         baseline_start_date: string,
     *         baseline_finish_date: string,
     *         status: string
     *     },
     *     schedule: list<array{
     *         id: string,
     *         parent_id: string|null,
     *         wbs_code: string,
     *         name: string,
     *         baseline_start_date: string,
     *         baseline_finish_date: string,
     *         status: string,
     *         is_baseline: bool,
     *         is_leaf: bool,
     *         weight: string|null,
     *         latest_progress: array{id: string, progress_date: string, progress_percent: string, recorded_at: string}|null
     *     }>
     * }
     */
    public function forProject(Project $project): array
    {
        $tasks = $project->wbsTasks()
            ->where('is_baseline', true)
            ->orderBy('wbs_code')
            ->orderBy('id')
            ->get();
        $latestProgressByTask = $project->progressEntries()
            ->whereIn('task_id', $tasks->pluck('id'))
            ->orderBy('progress_date')
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($entry): string => (string) $entry->task_id)
            ->map(fn (Collection $history) => $history->last());

        return [
            'version' => self::VERSION,
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'baseline_start_date' => $project->baseline_start_date->toDateString(),
                'baseline_finish_date' => $project->baseline_finish_date->toDateString(),
                'status' => $project->status,
            ],
            'schedule' => $tasks
                ->map(function (WbsTask $task) use ($latestProgressByTask): array {
                    $progress = $latestProgressByTask->get((string) $task->id);

                    return [
                        'id' => $task->id,
                        'parent_id' => $task->parent_id,
                        'wbs_code' => $task->wbs_code,
                        'name' => $task->name,
                        'baseline_start_date' => $task->baseline_start_date->toDateString(),
                        'baseline_finish_date' => $task->baseline_finish_date->toDateString(),
                        'status' => $task->status,
                        'is_baseline' => $task->is_baseline,
                        'is_leaf' => $task->is_leaf,
                        'weight' => $task->weight,
                        'latest_progress' => $progress === null ? null : [
                            'id' => $progress->id,
                            'progress_date' => $progress->progress_date->toDateString(),
                            'progress_percent' => $progress->progress_percent,
                            'recorded_at' => $progress->recorded_at->utc()->format('Y-m-d\TH:i:s\Z'),
                        ],
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
