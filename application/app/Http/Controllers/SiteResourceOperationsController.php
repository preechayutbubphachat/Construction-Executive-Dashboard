<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\SiteMachineryLog;
use App\Models\SiteManpowerLog;
use App\Models\WbsTask;
use App\Services\SiteResourceOperationsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SiteResourceOperationsController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $manpowerLogs = $project->manpowerLogs()
            ->with(['task', 'constraint', 'creator'])
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->get();

        $machineryLogs = $project->machineryLogs()
            ->with(['task', 'constraint', 'creator'])
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->get();

        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_leaf', true)
            ->orderBy('wbs_code')
            ->get();

        $openLaborConstraints = $project->constraints()
            ->where('type', ProjectConstraint::TYPE_LABOR_CREW)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->get();

        $openEquipmentConstraints = $project->constraints()
            ->where('type', ProjectConstraint::TYPE_EQUIPMENT)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->get();

        $totalPlannedWorkers = (int) $manpowerLogs->sum('planned_headcount');
        $totalActualWorkers = (int) $manpowerLogs->sum('actual_headcount');
        $totalOperatingHours = (float) $machineryLogs->sum('operating_hours');
        $totalDowntimeHours = (float) $machineryLogs->sum('downtime_hours');

        $stats = [
            'total_actual_workers' => $totalActualWorkers,
            'total_man_hours' => (float) $manpowerLogs->sum('total_man_hours'),
            'manpower_fulfillment_pct' => $totalPlannedWorkers > 0
                ? round(($totalActualWorkers / $totalPlannedWorkers) * 100, 1)
                : 100.0,
            'operational_machines' => $machineryLogs->where('status', 'OPERATIONAL')->count(),
            'breakdown_machines' => $machineryLogs->whereIn('status', ['BREAKDOWN', 'MAINTENANCE'])->count(),
            'machine_availability_pct' => ($totalOperatingHours + $totalDowntimeHours) > 0
                ? round(($totalOperatingHours / ($totalOperatingHours + $totalDowntimeHours)) * 100, 1)
                : 100.0,
            'open_resource_constraints' => $openLaborConstraints->count() + $openEquipmentConstraints->count(),
        ];

        return view('projects.resources.index', compact(
            'project',
            'manpowerLogs',
            'machineryLogs',
            'tasks',
            'openLaborConstraints',
            'openEquipmentConstraints',
            'stats'
        ));
    }

    public function storeManpower(
        Request $request,
        Project $project,
        SiteResourceOperationsService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'constraint_id' => ['nullable', 'string', 'max:36'],
            'log_date' => ['required', 'date'],
            'trade' => ['required', 'in:'.implode(',', SiteManpowerLog::TRADES)],
            'contractor_company' => ['nullable', 'string', 'max:255'],
            'planned_headcount' => ['required', 'integer', 'min:0', 'max:10000'],
            'actual_headcount' => ['required', 'integer', 'min:0', 'max:10000'],
            'working_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'shift' => ['nullable', 'in:'.implode(',', SiteManpowerLog::SHIFTS)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $result = $service->recordManpowerLog($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        $resolved = $result['resolved_constraints_count'];
        $msg = 'Daily manpower attendance logged.'
            .($resolved > 0 ? " Auto-resolved {$resolved} LABOR_CREW constraint(s)!" : '');

        return redirect()->route('projects.resources.index', $project)->with('status', $msg);
    }

    public function storeMachinery(
        Request $request,
        Project $project,
        SiteResourceOperationsService $service,
    ): RedirectResponse {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'wbs_task_id' => ['nullable', 'string', 'max:36'],
            'constraint_id' => ['nullable', 'string', 'max:36'],
            'log_date' => ['required', 'date'],
            'equipment_code' => ['required', 'string', 'max:64'],
            'equipment_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:'.implode(',', SiteMachineryLog::CATEGORIES)],
            'status' => ['required', 'in:'.implode(',', SiteMachineryLog::STATUSES)],
            'operating_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'downtime_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'operator_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $result = $service->recordMachineryLog($project, $request->user(), $validated);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }

        $resolved = $result['resolved_constraints_count'];
        $msg = 'Heavy machinery utilization logged.'
            .($resolved > 0 ? " Auto-resolved {$resolved} EQUIPMENT constraint(s)!" : '');

        return redirect()->route('projects.resources.index', $project)->with('status', $msg);
    }
}
