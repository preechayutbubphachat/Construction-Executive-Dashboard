<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WbsTask;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WbsTaskReadController extends Controller
{
    public function show(Project $project, WbsTask $task): View
    {
        Gate::authorize('view', $project);
        abort_unless((string) $task->project_id === (string) $project->id, 403);

        $task->load(['progressEntries' => fn ($query) => $query->where('project_id', $project->id)->orderByDesc('progress_date')->orderByDesc('recorded_at')]);

        return view('projects.wbs-task', compact('project', 'task'));
    }
}
