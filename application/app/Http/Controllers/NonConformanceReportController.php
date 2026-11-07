<?php

namespace App\Http\Controllers;

use App\Models\NonConformanceReport;
use App\Models\Project;
use App\Models\WbsTask;
use App\Services\NonConformanceReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NonConformanceReportController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $ncrs = $project->ncrs()
            ->with(['task', 'creator', 'rectifier', 'verifier'])
            ->orderByDesc('ncr_number')
            ->get();

        $unclosed = $ncrs->whereIn('status', NonConformanceReport::UNCLOSED_STATUSES);

        $stats = [
            'total' => $ncrs->count(),
            'open' => $ncrs->whereIn('status', ['OPEN', 'REJECTED_RECTIFICATION'])->count(),
            'awaiting_verification' => $ncrs->where('status', 'RECTIFICATION_SUBMITTED')->count(),
            'closed' => $ncrs->where('status', 'CLOSED')->count(),
            'blocking_payment' => $unclosed->where('hold_payment', true)->count(),
        ];

        return view('projects.ncr.index', compact('project', 'ncrs', 'stats'));
    }

    public function create(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_leaf', true)
            ->orderBy('wbs_code')
            ->get();

        $next = ($project->ncrs()->max('ncr_number') ?? 0) + 1;

        return view('projects.ncr.create', [
            'project' => $project,
            'tasks' => $tasks,
            'nextRef' => sprintf('NCR-%s-%03d', $project->code, $next),
        ]);
    }

    public function store(Request $request, Project $project, NonConformanceReportService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category' => ['required', 'in:'.implode(',', NonConformanceReport::CATEGORIES)],
            'severity' => ['required', 'in:'.implode(',', NonConformanceReport::SEVERITIES)],
            'hold_payment' => ['nullable', 'boolean'],
            'location_zone' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['hold_payment'] = $request->boolean('hold_payment');

        try {
            $ncr = $service->issueNcr($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.ncr.show', [$project, $ncr])
            ->with('status', "Non-Conformance Report {$ncr->reference_no} issued.");
    }

    public function show(Request $request, Project $project, NonConformanceReport $ncr): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($ncr->project_id === $project->id, 404);

        $ncr->load(['task', 'creator', 'rectifier', 'verifier']);

        return view('projects.ncr.show', compact('project', 'ncr'));
    }

    public function submitRectification(Request $request, Project $project, NonConformanceReport $ncr, NonConformanceReportService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($ncr->project_id === $project->id, 404);

        $validated = $request->validate([
            'root_cause' => ['nullable', 'string', 'max:3000'],
            'corrective_action' => ['required', 'string', 'max:3000'],
            'preventive_action' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            $service->submitRectification($ncr, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.ncr.show', [$project, $ncr])
            ->with('status', 'CAPA rectification submitted for QC inspection.');
    }

    public function verifyClose(Request $request, Project $project, NonConformanceReport $ncr, NonConformanceReportService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($ncr->project_id === $project->id, 404);

        try {
            $service->verifyAndClose($ncr, $request->user(), $request->input('notes'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.ncr.show', [$project, $ncr])
            ->with('status', 'NCR verified and closed. Payment hold on linked WBS task has been released.');
    }

    public function rejectRectification(Request $request, Project $project, NonConformanceReport $ncr, NonConformanceReportService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($ncr->project_id === $project->id, 404);

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $service->rejectRectification($ncr, $request->user(), (string) $request->input('reason'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.ncr.show', [$project, $ncr])
            ->with('status', 'Rectification rejected. Payment hold remains active.');
    }
}
