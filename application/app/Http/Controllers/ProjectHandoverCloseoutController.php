<?php

namespace App\Http\Controllers;

use App\Models\HandoverPunchItem;
use App\Models\Project;
use App\Models\ProjectHandoverCertificate;
use App\Services\ProjectHandoverCloseoutService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectHandoverCloseoutController extends Controller
{
    public function __construct(
        private readonly ProjectHandoverCloseoutService $service,
    ) {}

    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $dashboard = $this->service->buildDashboard($project);
        $wbsTasks = $project->wbsTasks()->orderBy('wbs_code')->get();

        return view('projects.handover.index', [
            'project' => $project,
            'dashboard' => $dashboard,
            'wbsTasks' => $wbsTasks,
            'disciplines' => HandoverPunchItem::DISCIPLINES,
            'severities' => HandoverPunchItem::SEVERITIES,
            'certificateTypes' => ProjectHandoverCertificate::CERTIFICATE_TYPES,
        ]);
    }

    public function storePunchItem(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string'],
            'location_zone' => ['required', 'string', 'max:160'],
            'discipline' => ['required', 'string', Rule::in(HandoverPunchItem::DISCIPLINES)],
            'severity' => ['required', 'string', Rule::in(HandoverPunchItem::SEVERITIES)],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        try {
            $punch = $this->service->createPunchItem($project, $request->user(), $validated);

            return redirect()
                ->route('projects.handover.index', $project)
                ->with('status', "Punch List Item {$punch->punch_code} logged at {$punch->location_zone}.");
        } catch (DomainException $e) {
            return redirect()
                ->route('projects.handover.index', $project)
                ->withErrors(['handover' => $e->getMessage()]);
        }
    }

    public function rectifyPunchItem(Request $request, Project $project, HandoverPunchItem $punchItem): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($punchItem->project_id === $project->id, 404);

        $validated = $request->validate([
            'rectification_notes' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $updated = $this->service->rectifyPunchItem($punchItem, $request->user(), $validated['rectification_notes']);

            return redirect()
                ->route('projects.handover.index', $project)
                ->with('status', "Punch List Item {$updated->punch_code} marked as rectified and ready for inspection.");
        } catch (DomainException $e) {
            return redirect()
                ->route('projects.handover.index', $project)
                ->withErrors(['handover' => $e->getMessage()]);
        }
    }

    public function verifyPunchItem(Request $request, Project $project, HandoverPunchItem $punchItem): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($punchItem->project_id === $project->id, 404);

        $validated = $request->validate([
            'verification_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $updated = $this->service->verifyPunchItemClosed(
                $punchItem,
                $request->user(),
                $validated['verification_notes'] ?? null
            );

            return redirect()
                ->route('projects.handover.index', $project)
                ->with('status', "Punch List Item {$updated->punch_code} verified and closed.");
        } catch (DomainException $e) {
            return redirect()
                ->route('projects.handover.index', $project)
                ->withErrors(['handover' => $e->getMessage()]);
        }
    }

    public function issueCertificate(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'certificate_type' => ['required', 'string', Rule::in(ProjectHandoverCertificate::CERTIFICATE_TYPES)],
            'handover_date' => ['required', 'date'],
            'dlp_months' => ['required', 'integer', 'min:0', 'max:120'],
            'retention_release_amount' => ['required', 'numeric', 'min:0'],
            'scope_remarks' => ['nullable', 'string', 'max:3000'],
        ]);

        try {
            $cert = $this->service->issueCertificate($project, $request->user(), $validated);

            return redirect()
                ->route('projects.handover.index', $project)
                ->with('status', "Handover Certificate {$cert->certificate_number} ({$cert->certificate_type}) issued.");
        } catch (DomainException $e) {
            return redirect()
                ->route('projects.handover.index', $project)
                ->withErrors(['handover' => $e->getMessage()]);
        }
    }

    public function releaseRetention(Request $request, Project $project, ProjectHandoverCertificate $certificate): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($certificate->project_id === $project->id, 404);

        $markCompleted = $request->boolean('mark_project_completed', false);

        try {
            $updated = $this->service->releaseRetentionAndCloseout(
                $certificate,
                $request->user(),
                $markCompleted
            );

            return redirect()
                ->route('projects.handover.index', $project)
                ->with('status', "Retention Money released for {$updated->certificate_number} and posted to Finance Ledger.");
        } catch (DomainException $e) {
            return redirect()
                ->route('projects.handover.index', $project)
                ->withErrors(['handover' => $e->getMessage()]);
        }
    }
}
