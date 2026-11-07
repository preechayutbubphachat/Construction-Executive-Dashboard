<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequisition;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\WbsTask;
use App\Services\MaterialProcurementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MaterialProcurementController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $requisitions = $project->materialRequisitions()
            ->with(['task', 'constraint', 'creator', 'approver', 'receiver'])
            ->orderByDesc('mr_number')
            ->get();

        $shortageRisks = $requisitions->filter(fn (MaterialRequisition $mr) => $mr->isLookaheadShortageRisk());

        $openMaterialConstraintsCount = $project->constraints()
            ->where('type', ProjectConstraint::TYPE_MATERIAL)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->count();

        $stats = [
            'total' => $requisitions->count(),
            'total_estimated_cost' => (float) $requisitions->sum('total_estimated_cost'),
            'delivered' => $requisitions->where('status', 'DELIVERED')->count(),
            'shortage_risks' => $shortageRisks->count(),
            'open_material_constraints' => $openMaterialConstraintsCount,
        ];

        return view('projects.procurement.index', compact('project', 'requisitions', 'shortageRisks', 'stats'));
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

        $materialConstraints = $project->constraints()
            ->where('type', ProjectConstraint::TYPE_MATERIAL)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->orderBy('created_at')
            ->get();

        $next = ($project->materialRequisitions()->max('mr_number') ?? 0) + 1;

        return view('projects.procurement.create', [
            'project' => $project,
            'tasks' => $tasks,
            'materialConstraints' => $materialConstraints,
            'nextRef' => sprintf('MR-%s-%03d', $project->code, $next),
        ]);
    }

    public function store(Request $request, Project $project, MaterialProcurementService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'constraint_id' => ['nullable', 'string', 'max:36'],
            'material_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', MaterialRequisition::CATEGORIES)],
            'unit' => ['required', 'string', 'max:24'],
            'required_qty' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'required_on_site_date' => ['required', 'date'],
        ]);

        try {
            $mr = $service->createRequisition($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.procurement.show', [$project, $mr])
            ->with('status', "Material Requisition {$mr->reference_no} created.");
    }

    public function show(Request $request, Project $project, MaterialRequisition $mr): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($mr->project_id === $project->id, 404);

        $mr->load(['task', 'constraint', 'creator', 'approver', 'receiver']);

        return view('projects.procurement.show', compact('project', 'mr'));
    }

    public function issuePo(Request $request, Project $project, MaterialRequisition $mr, MaterialProcurementService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($mr->project_id === $project->id, 404);

        $validated = $request->validate([
            'po_number' => ['required', 'string', 'max:80'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $service->issuePurchaseOrder($mr, $request->user(), $validated['po_number'], $validated['supplier_name'] ?? null);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.procurement.show', [$project, $mr])
            ->with('status', "Purchase Order {$validated['po_number']} issued.");
    }

    public function recordDelivery(Request $request, Project $project, MaterialRequisition $mr, MaterialProcurementService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($mr->project_id === $project->id, 404);

        $validated = $request->validate([
            'received_qty' => ['required', 'numeric', 'min:0'],
            'delivery_order_no' => ['required', 'string', 'max:100'],
            'mill_cert_ref' => ['nullable', 'string', 'max:120'],
            'inspection_notes' => ['nullable', 'string', 'max:2000'],
            'qc_passed' => ['required', 'boolean'],
        ]);

        try {
            $result = $service->recordSiteDelivery($mr, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $resolved = $result['resolved_constraints_count'];
        $msg = $validated['qc_passed']
            ? 'Site delivery recorded and verified.'
                .($resolved > 0 ? " Auto-resolved {$resolved} linked MATERIAL constraint(s)!" : '')
            : 'Material delivery rejected at site QC inspection.';

        return redirect()->route('projects.procurement.show', [$project, $mr])
            ->with('status', $msg);
    }
}
