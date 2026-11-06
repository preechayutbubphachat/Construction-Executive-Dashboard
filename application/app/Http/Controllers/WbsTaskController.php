<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WbsTask;
use App\Services\WbsTaskManagement;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WbsTaskController extends Controller
{
    public function store(Request $request, Project $project, WbsTaskManagement $tasks): JsonResponse
    {
        Gate::authorize('update', $project);
        $validated = $request->validate($this->rules(true));

        try {
            $task = $tasks->create($project, $request->user(), $validated);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['wbs_task' => $exception->getMessage()]);
        }

        return response()->json(['task' => $task], 201);
    }

    public function update(
        Request $request,
        Project $project,
        WbsTask $task,
        WbsTaskManagement $tasks,
    ): JsonResponse {
        Gate::authorize('update', $project);
        abort_unless((string) $task->project_id === (string) $project->id, 404);
        $validated = $request->validate($this->rules(false));

        try {
            $updated = $tasks->update($project, $task, $request->user(), $validated);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['wbs_task' => $exception->getMessage()]);
        }

        return response()->json(['task' => $updated]);
    }

    private function rules(bool $creating): array
    {
        $presence = $creating ? 'required' : 'sometimes';

        return [
            'id' => ['prohibited'],
            'project_id' => ['prohibited'],
            'parent_id' => ['sometimes', 'nullable', 'string', 'max:36'],
            'wbs_code' => $creating ? ['required', 'string', 'max:40'] : ['prohibited'],
            'name' => [$presence, 'required', 'string', 'max:255'],
            'baseline_start_date' => [$presence, 'required', 'date'],
            'baseline_finish_date' => [$presence, 'required', 'date'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'gt:0', 'max:100'],
            'status' => ['sometimes', Rule::in(WbsTask::STATUSES)],
            'is_baseline' => ['sometimes', 'boolean'],
        ];
    }
}
