<?php

namespace App\Http\Controllers;

use App\Models\HseSafetyLog;
use App\Models\PermitToWork;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\WbsTask;
use App\Services\HseSafetyOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HseSafetyOperationsController extends Controller
{
    public function index(Request $request, Project $project, HseSafetyOperationsService $service): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $permits = $project->permitToWorks()
            ->with(['task', 'constraint', 'creator', 'approver'])
            ->orderByDesc('ptw_number')
            ->get();

        $safetyLogs = $project->hseSafetyLogs()
            ->with(['creator'])
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->get();

        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_leaf', true)
            ->orderBy('wbs_code')
            ->get();

        $openPermitConstraints = $project->constraints()
            ->where('type', ProjectConstraint::TYPE_PERMIT)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->get();

        $metrics = $service->calculateSafetyMetrics($project);

        return view('projects.hse.index', compact(
            'project',
            'permits',
            'safetyLogs',
            'tasks',
            'openPermitConstraints',
            'metrics'
        ));
    }

    public function storePermit(
        Request $request,
        Project $project,
        HseSafetyOperationsService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'constraint_id' => ['nullable', 'string', 'max:36'],
            'permit_type' => ['required', 'in:'.implode(',', PermitToWork::TYPES)],
            'work_title' => ['required', 'string', 'max:255'],
            'location_zone' => ['nullable', 'string', 'max:255'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],
            'precautions_checklist' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            $permit = $service->requestPermit($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.hse.index', $project)
            ->with('status', "Permit-to-Work {$permit->reference_no} requested and awaiting Safety Officer sign-off.");
    }

    public function approvePermit(
        Request $request,
        Project $project,
        PermitToWork $permit,
        HseSafetyOperationsService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($permit->project_id === $project->id, 404);

        try {
            $result = $service->approvePermit($permit, $request->user(), $request->input('notes'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $resolved = $result['resolved_constraints_count'];
        $msg = "Permit-to-Work {$permit->reference_no} approved."
            .($resolved > 0 ? " Auto-resolved {$resolved} PERMIT constraint(s)!" : '');

        return redirect()->route('projects.hse.index', $project)->with('status', $msg);
    }

    public function revokePermit(
        Request $request,
        Project $project,
        PermitToWork $permit,
        HseSafetyOperationsService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($permit->project_id === $project->id, 404);

        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $service->revokePermit($permit, $request->user(), (string) $request->input('reason'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.hse.index', $project)
            ->with('status', "Permit-to-Work {$permit->reference_no} revoked.");
    }

    public function storeEvent(
        Request $request,
        Project $project,
        HseSafetyOperationsService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'log_date' => ['required', 'date'],
            'record_type' => ['required', 'in:'.implode(',', HseSafetyLog::RECORD_TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'attendees_count' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'lost_time_hours' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);

        try {
            $service->recordSafetyEvent($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.hse.index', $project)
            ->with('status', 'HSE safety event recorded.');
    }
}
