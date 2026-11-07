<?php

namespace App\Http\Controllers;

use App\Models\InterimPaymentCertificate;
use App\Models\Project;
use App\Models\WbsTask;
use App\Services\InterimPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InterimPaymentController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $ipcs = $project->ipcs()
            ->with(['items', 'siteEngineer', 'quantitySurveyor', 'projectDirector', 'creator'])
            ->orderByDesc('certificate_number')
            ->get();

        $stats = [
            'total_ipcs' => $ipcs->count(),
            'total_gross_certified' => $ipcs->whereIn('status', ['APPROVED', 'POSTED'])->sum('current_work_amount'),
            'total_retention_held' => $ipcs->whereIn('status', ['APPROVED', 'POSTED'])->sum('retention_deduction'),
            'total_advance_recovered' => $ipcs->whereIn('status', ['APPROVED', 'POSTED'])->sum('advance_recovery_deduction'),
            'total_net_disbursed' => $ipcs->where('status', 'POSTED')->sum('net_payable_amount'),
            'pending_approval' => $ipcs->whereIn('status', ['SUBMITTED', 'VERIFIED_SITE', 'VERIFIED_QS'])->count(),
        ];

        return view('projects.ipc.index', [
            'project' => $project,
            'ipcs' => $ipcs,
            'stats' => $stats,
        ]);
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

        $lastIpc = $project->ipcs()->orderByDesc('certificate_number')->first();
        $nextNumber = ($lastIpc?->certificate_number ?? 0) + 1;
        $nextRef = sprintf('IPC-%s-%03d', $project->code, $nextNumber);

        return view('projects.ipc.create', [
            'project' => $project,
            'tasks' => $tasks,
            'nextNumber' => $nextNumber,
            'nextRef' => $nextRef,
            'lastIpc' => $lastIpc,
        ]);
    }

    public function store(Request $request, Project $project, InterimPaymentService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'contractor_name' => ['required', 'string', 'max:255'],
            'contractor_invoice_no' => ['nullable', 'string', 'max:100'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'retention_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'advance_recovery_deduction' => ['nullable', 'numeric', 'min:0'],
            'other_deductions' => ['nullable', 'numeric', 'min:0'],
            'vat_rate_percent' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'items' => ['nullable', 'array'],
        ]);

        try {
            $ipc = $service->createApplication($project, $request->user(), $validated);

            return redirect()->route('projects.ipc.show', [$project, $ipc])
                ->with('status', "Payment Application {$ipc->reference_no} submitted successfully.");
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request, Project $project, InterimPaymentCertificate $ipc): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($ipc->project_id === $project->id, 404);

        $ipc->load([
            'items.task',
            'siteEngineer',
            'quantitySurveyor',
            'projectDirector',
            'financeTransaction',
            'creator',
        ]);

        return view('projects.ipc.show', [
            'project' => $project,
            'ipc' => $ipc,
        ]);
    }

    public function verifySite(
        Request $request,
        Project $project,
        InterimPaymentCertificate $ipc,
        InterimPaymentService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($ipc->project_id === $project->id, 404);

        try {
            $service->verifyBySiteEngineer($ipc, $request->user(), $request->input('notes'));

            return redirect()->route('projects.ipc.show', [$project, $ipc])
                ->with('status', 'Level 1: Site physical verification successfully recorded.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function verifyQs(
        Request $request,
        Project $project,
        InterimPaymentCertificate $ipc,
        InterimPaymentService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($ipc->project_id === $project->id, 404);

        try {
            $service->verifyByQuantitySurveyor($ipc, $request->user(), $request->input('notes'));

            return redirect()->route('projects.ipc.show', [$project, $ipc])
                ->with('status', 'Level 2: Quantity Surveying & Valuation certified successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function approveDirector(
        Request $request,
        Project $project,
        InterimPaymentCertificate $ipc,
        InterimPaymentService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        abort_unless($ipc->project_id === $project->id, 404);

        try {
            $service->approveByDirector($ipc, $request->user(), $request->input('notes'));

            return redirect()->route('projects.ipc.show', [$project, $ipc])
                ->with('status', 'Level 3: Executive Final Approval signed. Payment transaction posted into Ledger & S-Curve.');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
