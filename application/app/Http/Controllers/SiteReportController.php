<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\DailySiteReportService;
use App\Services\WeeklyExecutiveReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SiteReportController extends Controller
{
    public function daily(
        Request $request,
        Project $project,
        DailySiteReportService $service,
    ): View {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $dateParam = $request->query('date');
        try {
            $date = $dateParam ? Carbon::parse($dateParam) : Carbon::today();
        } catch (\Throwable) {
            $date = Carbon::today();
        }

        $report = $service->generate($project, $date);

        return view('projects.reports.daily', [
            'project' => $project,
            'report' => $report,
        ]);
    }

    public function weekly(
        Request $request,
        Project $project,
        WeeklyExecutiveReportService $service,
    ): View {
        abort_unless($request->user() !== null, 403);
        Gate::authorize('view', $project);

        $weekParam = $request->query('week');
        try {
            $weekStart = $weekParam ? Carbon::parse($weekParam)->startOfWeek() : Carbon::now()->startOfWeek();
        } catch (\Throwable) {
            $weekStart = Carbon::now()->startOfWeek();
        }

        $report = $service->generate($project, $weekStart);

        return view('projects.reports.weekly', [
            'project' => $project,
            'report' => $report,
        ]);
    }
}
