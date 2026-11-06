<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WbsTask;
use App\Services\BaselineScheduleReadModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectScheduleController extends Controller
{
    public function __invoke(Request $request, Project $project, BaselineScheduleReadModel $schedule): JsonResponse|View
    {
        Gate::authorize('view', $project);

        $scheduleData = $schedule->forProject($project);

        if ($request->wantsJson()) {
            return response()->json($scheduleData);
        }

        $tasks = $project->wbsTasks()->orderBy('wbs_code')->get();

        $latestProgressByTask = $project->progressEntries()
            ->whereIn('task_id', $tasks->pluck('id'))
            ->orderBy('progress_date')
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($entry): string => (string) $entry->task_id)
            ->map(fn ($history) => $history->last());

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'COMPLETED')->count();
        $inProgressTasks = $tasks->where('status', 'IN_PROGRESS')->count();

        $nowDate = now()->toDateString();
        $delayedTasks = $tasks->filter(function (WbsTask $task) use ($nowDate, $latestProgressByTask): bool {
            if ($task->status === 'COMPLETED') {
                return false;
            }
            $progress = $latestProgressByTask->get((string) $task->id);
            $progressPercent = $progress !== null ? (float) $progress->progress_percent : 0.0;
            if ($progressPercent >= 100.0) {
                return false;
            }

            return $task->baseline_finish_date !== null && $task->baseline_finish_date->toDateString() < $nowDate;
        })->count();

        return view('projects.schedule', [
            'project' => $project,
            'scheduleData' => $scheduleData,
            'tasks' => $tasks,
            'latestProgressByTask' => $latestProgressByTask,
            'stats' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'in_progress' => $inProgressTasks,
                'delayed' => $delayedTasks,
            ],
        ]);
    }
}
