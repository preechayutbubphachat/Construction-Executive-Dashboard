<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\VariationOrder;
use App\Services\VariationOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class VariationOrderController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $vos = $project->variationOrders()->with('creator')->orderByDesc('vo_number')->get();
        $approved = $vos->where('status', 'APPROVED');

        $stats = [
            'total' => $vos->count(),
            'pending' => $vos->whereIn('status', ['SUBMITTED', 'VERIFIED_SITE', 'VERIFIED_QS'])->count(),
            'approved_cost' => (float) $approved->sum('cost_impact'),
            'approved_days' => (int) $approved->sum('time_impact_days'),
            'pending_cost' => (float) $vos->whereIn('status', ['SUBMITTED', 'VERIFIED_SITE', 'VERIFIED_QS'])->sum('cost_impact'),
        ];

        return view('projects.vo.index', compact('project', 'vos', 'stats'));
    }

    public function create(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $next = ($project->variationOrders()->max('vo_number') ?? 0) + 1;

        return view('projects.vo.create', [
            'project' => $project,
            'nextRef' => sprintf('VO-%s-%03d', $project->code, $next),
        ]);
    }

    public function store(Request $request, Project $project, VariationOrderService $service): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'reason' => ['required', 'in:'.implode(',', VariationOrder::REASONS)],
            'type' => ['required', 'in:'.implode(',', VariationOrder::TYPES)],
            'cost_amount' => ['required', 'numeric', 'min:0'],
            'time_impact_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
        ]);

        try {
            $vo = $service->createRequest($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.vo.show', [$project, $vo])
            ->with('status', "Variation request {$vo->reference_no} submitted.");
    }

    public function show(Request $request, Project $project, VariationOrder $vo): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);
        abort_unless($vo->project_id === $project->id, 404);

        $vo->load(['creator', 'siteEngineer', 'quantitySurveyor', 'projectDirector']);

        return view('projects.vo.show', compact('project', 'vo'));
    }

    public function verifySite(Request $request, Project $project, VariationOrder $vo, VariationOrderService $service): RedirectResponse
    {
        return $this->act($request, $project, $vo, fn () => $service->verifyBySiteEngineer($vo, $request->user(), $request->input('notes')), 'Level 1 site verification recorded.');
    }

    public function verifyQs(Request $request, Project $project, VariationOrder $vo, VariationOrderService $service): RedirectResponse
    {
        return $this->act($request, $project, $vo, fn () => $service->verifyByQuantitySurveyor($vo, $request->user(), $request->input('notes')), 'Level 2 QS cost certification recorded.');
    }

    public function approveDirector(Request $request, Project $project, VariationOrder $vo, VariationOrderService $service): RedirectResponse
    {
        return $this->act($request, $project, $vo, fn () => $service->approveByDirector($vo, $request->user(), $request->input('notes')), 'Level 3 approved. Project budget and baseline finish date updated.');
    }

    public function reject(Request $request, Project $project, VariationOrder $vo, VariationOrderService $service): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        return $this->act($request, $project, $vo, fn () => $service->reject($vo, $request->user(), (string) $request->input('reason')), 'Variation request rejected.');
    }

    private function act(Request $request, Project $project, VariationOrder $vo, \Closure $action, string $message): RedirectResponse
    {
        abort_unless($request->user() !== null, 403);
        abort_unless($vo->project_id === $project->id, 404);

        try {
            $action();
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('projects.vo.show', [$project, $vo])->with('status', $message);
    }
}
