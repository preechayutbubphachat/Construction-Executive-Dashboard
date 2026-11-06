<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WbsTask;
use App\Services\ProgressRecorder;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ProgressEntryController extends Controller
{
    public function store(
        Request $request,
        Project $project,
        WbsTask $task,
        ProgressRecorder $progress,
    ): JsonResponse {
        Gate::authorize('update', $project);
        abort_unless((string) $task->project_id === (string) $project->id, 404);
        $validated = $request->validate([
            'id' => ['prohibited'],
            'project_id' => ['prohibited'],
            'task_id' => ['prohibited'],
            'recorded_by' => ['prohibited'],
            'progress_date' => ['required', 'date'],
            'progress_percent' => ['required', 'numeric', 'between:0,100'],
            'note' => ['sometimes', 'nullable', 'string', 'max:4000'],
        ]);

        try {
            $entry = $progress->record($project, $task, $request->user(), $validated);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['progress' => $exception->getMessage()]);
        }

        return response()->json(['progress_entry' => $entry], 201);
    }
}
