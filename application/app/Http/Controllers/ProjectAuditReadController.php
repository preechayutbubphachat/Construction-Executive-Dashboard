<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectAuditReadController extends Controller
{
    public function show(Project $project, AuditEvent $audit): View
    {
        Gate::authorize('view', $project);
        abort_unless((string) $audit->project_id === (string) $project->id, 403);
        $audit->load('actor');

        return view('projects.audit', compact('project', 'audit'));
    }
}
