<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WbsTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ProjectWbsController extends Controller
{
    public function __invoke(Project $project): JsonResponse
    {
        Gate::authorize('view', $project);
        $tasks = $project->wbsTasks()->orderBy('wbs_code')->get();
        $latestProgressByTask = $project->progressEntries()
            ->whereIn('task_id', $tasks->pluck('id'))
            ->orderBy('progress_date')
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($entry): string => (string) $entry->task_id)
            ->map(fn (Collection $history) => $history->last());
        $childrenByParent = $tasks->groupBy(
            fn (WbsTask $task): string => $task->parent_id === null ? '__ROOT__' : (string) $task->parent_id,
        );

        $build = function (?string $parentId) use (&$build, $childrenByParent, $latestProgressByTask): array {
            $key = $parentId ?? '__ROOT__';

            return $childrenByParent->get($key, collect())
                ->map(function (WbsTask $task) use (&$build, $latestProgressByTask): array {
                    $progress = $latestProgressByTask->get((string) $task->id);

                    return [
                        'id' => $task->id,
                        'parent_id' => $task->parent_id,
                        'wbs_code' => $task->wbs_code,
                        'name' => $task->name,
                        'baseline_start_date' => $task->baseline_start_date->toDateString(),
                        'baseline_finish_date' => $task->baseline_finish_date->toDateString(),
                        'weight' => $task->weight,
                        'status' => $task->status,
                        'is_baseline' => $task->is_baseline,
                        'is_leaf' => $task->is_leaf,
                        'latest_progress' => $progress === null ? null : [
                            'id' => $progress->id,
                            'progress_date' => $progress->progress_date->toDateString(),
                            'progress_percent' => $progress->progress_percent,
                            'recorded_at' => $progress->recorded_at->utc()->format('Y-m-d\TH:i:s\Z'),
                        ],
                        'children' => $build((string) $task->id),
                    ];
                })
                ->values()
                ->all();
        };

        return response()->json([
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
                'baseline_start_date' => $project->baseline_start_date->toDateString(),
                'baseline_finish_date' => $project->baseline_finish_date->toDateString(),
                'budget_amount' => $project->budget_amount,
                'status' => $project->status,
                'archived_at' => $project->archived_at?->utc()->format('Y-m-d\TH:i:s\Z'),
            ],
            'wbs' => $build(null),
        ]);
    }
}
