<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectManagement;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function update(Request $request, Project $project, ProjectManagement $projects): JsonResponse
    {
        Gate::authorize('update', $project);
        $validated = $request->validate([
            'id' => ['prohibited'],
            'code' => ['prohibited'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'baseline_start_date' => ['sometimes', 'required', 'date'],
            'baseline_finish_date' => ['sometimes', 'required', 'date'],
            'budget_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
        ]);

        try {
            $updated = $projects->updateMetadata($project, $request->user(), $validated);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['project' => $exception->getMessage()]);
        }

        return response()->json(['project' => $updated]);
    }

    public function transition(Request $request, Project $project, ProjectManagement $projects): JsonResponse
    {
        Gate::authorize('update', $project);
        $validated = $request->validate([
            'id' => ['prohibited'],
            'code' => ['prohibited'],
            'status' => ['required', Rule::in(array_values(array_diff(Project::STATUSES, ['ARCHIVED'])))],
        ]);

        try {
            $updated = $projects->transition($project, $request->user(), $validated['status']);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['status' => $exception->getMessage()]);
        }

        return response()->json(['project' => $updated]);
    }

    public function archive(Request $request, Project $project, ProjectManagement $projects): JsonResponse
    {
        Gate::authorize('archive', $project);

        try {
            $archived = $projects->transition($project, $request->user(), 'ARCHIVED');
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['status' => $exception->getMessage()]);
        }

        return response()->json(['project' => $archived]);
    }
}
