<?php

namespace App\Http\Controllers;

use App\Models\EngineeringRfi;
use App\Models\EngineeringSubmittal;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\WbsTask;
use App\Services\EngineeringDocumentControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EngineeringDocumentControlController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $rfis = $project->engineeringRfis()
            ->with(['task', 'constraint', 'creator', 'responder'])
            ->orderByDesc('rfi_number')
            ->get();

        $submittals = $project->engineeringSubmittals()
            ->with(['task', 'creator', 'reviewer'])
            ->orderByDesc('submittal_number')
            ->get();

        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_leaf', true)
            ->orderBy('wbs_code')
            ->get();

        $openDesignConstraints = $project->constraints()
            ->where('type', ProjectConstraint::TYPE_DESIGN_RFI)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->get();

        $overdueRfis = $rfis->filter(fn (EngineeringRfi $rfi) => $rfi->isOverdue());

        $stats = [
            'total_rfis' => $rfis->count(),
            'open_rfis' => $rfis->where('status', 'OPEN')->count(),
            'overdue_rfis' => $overdueRfis->count(),
            'total_submittals' => $submittals->count(),
            'approved_submittals' => $submittals->filter(fn (EngineeringSubmittal $s) => $s->isApprovedForConstruction())->count(),
            'open_design_constraints' => $openDesignConstraints->count(),
        ];

        return view('projects.engineering.index', compact(
            'project',
            'rfis',
            'submittals',
            'tasks',
            'openDesignConstraints',
            'overdueRfis',
            'stats'
        ));
    }

    public function storeRfi(
        Request $request,
        Project $project,
        EngineeringDocumentControlService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'constraint_id' => ['nullable', 'string', 'max:36'],
            'discipline' => ['required', 'in:'.implode(',', EngineeringRfi::DISCIPLINES)],
            'subject' => ['required', 'string', 'max:255'],
            'drawing_ref' => ['nullable', 'string', 'max:120'],
            'question' => ['required', 'string', 'max:5000'],
            'proposed_solution' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['required', 'date'],
        ]);

        try {
            $rfi = $service->createRfi($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.engineering.index', $project)
            ->with('status', "Engineering RFI {$rfi->reference_no} issued.");
    }

    public function answerRfi(
        Request $request,
        Project $project,
        EngineeringRfi $rfi,
        EngineeringDocumentControlService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($rfi->project_id === $project->id, 404);

        $validated = $request->validate([
            'official_response' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $result = $service->answerAndCloseRfi($rfi, $request->user(), $validated['official_response']);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $resolved = $result['resolved_constraints_count'];
        $msg = "RFI {$rfi->reference_no} answered and closed."
            .($resolved > 0 ? " Auto-resolved {$resolved} DESIGN_RFI constraint(s)!" : '');

        return redirect()->route('projects.engineering.index', $project)->with('status', $msg);
    }

    public function storeSubmittal(
        Request $request,
        Project $project,
        EngineeringDocumentControlService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'submittal_type' => ['required', 'in:'.implode(',', EngineeringSubmittal::TYPES)],
            'discipline' => ['required', 'in:'.implode(',', EngineeringSubmittal::DISCIPLINES)],
            'title' => ['required', 'string', 'max:255'],
            'spec_section' => ['nullable', 'string', 'max:120'],
            'revision' => ['nullable', 'string', 'max:16'],
        ]);

        try {
            $sub = $service->createSubmittal($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.engineering.index', $project)
            ->with('status', "Submittal {$sub->reference_no} ({$sub->revision}) logged for review.");
    }

    public function reviewSubmittal(
        Request $request,
        Project $project,
        EngineeringSubmittal $submittal,
        EngineeringDocumentControlService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($submittal->project_id === $project->id, 404);

        $validated = $request->validate([
            'decision' => ['required', 'in:'.implode(',', EngineeringSubmittal::REVIEW_DECISIONS)],
            'review_comments' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            $service->reviewSubmittal($submittal, $request->user(), $validated['decision'], $validated['review_comments'] ?? null);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.engineering.index', $project)
            ->with('status', "Submittal {$submittal->reference_no} reviewed: {$validated['decision']}.");
    }

    public function resubmitSubmittal(
        Request $request,
        Project $project,
        EngineeringSubmittal $submittal,
        EngineeringDocumentControlService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($submittal->project_id === $project->id, 404);

        $validated = $request->validate([
            'revision' => ['required', 'string', 'max:16'],
        ]);

        try {
            $service->resubmitRevision($submittal, $request->user(), $validated['revision']);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.engineering.index', $project)
            ->with('status', "Submittal {$submittal->reference_no} resubmitted as {$validated['revision']}.");
    }
}
