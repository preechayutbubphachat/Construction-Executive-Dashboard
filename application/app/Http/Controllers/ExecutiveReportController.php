<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\CedFinanceExceptionV1Projection;
use App\Services\CedFinanceV1ReadModel;
use App\Services\CedProjectHealthV1ReadModel;
use App\Services\CedScheduleProgressExceptionV1Projection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ExecutiveReportController extends Controller
{
    public function __invoke(
        Project $project,
        CedProjectHealthV1ReadModel $healthBoundary,
        CedScheduleProgressExceptionV1Projection $scheduleProgressProjection,
        CedFinanceV1ReadModel $financeBoundary,
        CedFinanceExceptionV1Projection $financeExceptionProjection,
    ): View {
        abort_unless(request()->user() !== null, 403);
        Gate::authorize('view', $project);

        $health = $healthBoundary->forProject($project);
        $scheduleProgressException = $scheduleProgressProjection->forProject($project, $health);
        $finance = $financeBoundary->forProject($project);
        $financeException = $financeExceptionProjection->forProject($project, $health, $finance);
        $financeTransactions = $project->financeTransactions()->orderBy('id')->get(['id', 'project_id', 'direction', 'description', 'amount', 'status']);
        $tasks = $project->wbsTasks()->where('is_baseline', true)->whereDoesntHave('children')->orderBy('wbs_code')->get();
        $evidenceDocuments = $project->evidenceDocuments()->orderBy('id')->get(['id', 'project_id', 'original_name', 'mime_type', 'byte_size', 'sha256', 'access_class', 'review_state']);
        $auditEvents = $project->auditEvents()->with('actor')->latest('occurred_at')->limit(10)->get();
        $constraints = $project->constraints()->with(['task', 'assignee'])->open()->orderBy('severity')->get();

        return view('projects.report', compact(
            'project',
            'health',
            'scheduleProgressException',
            'finance',
            'financeException',
            'financeTransactions',
            'tasks',
            'evidenceDocuments',
            'auditEvents',
            'constraints',
        ));
    }
}
