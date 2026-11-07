<?php

namespace App\Services;

use App\Models\EvidenceDocument;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\ProjectConstraint;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class WeeklyExecutiveReportService
{
    public const VERSION = 'CED_WEEKLY_EXECUTIVE_REPORT_V1';

    public function __construct(
        private readonly EarnedValueManagementService $evmService,
        private readonly WeeklyLookaheadService $lookaheadService,
        private readonly CedProjectHealthV1ReadModel $healthReadModel,
    ) {}

    /**
     * Generates a comprehensive weekly executive status report.
     *
     * @return array<string, mixed>
     */
    public function generate(Project $project, ?Carbon $weekStart = null): array
    {
        $start = $weekStart ? $weekStart->copy()->startOfWeek() : Carbon::now()->startOfWeek();
        $end = $start->copy()->endOfWeek();
        $weekNumber = $start->weekOfYear;
        $year = $start->year;

        // 1. Project Health
        $health = $this->healthReadModel->forProject($project);

        // 2. Earned Value Management (EVM) Snapshot
        $evm = $this->evmService->calculate($project, CarbonImmutable::parse($end->toDateString()));

        // 3. Weekly Lookahead Schedule & Make-Ready Analysis
        $lookahead = $this->lookaheadService->forProject($project, 3, $start);

        // 4. Financial Transactions approved/posted in this week
        $financeTransactions = FinanceTransaction::query()
            ->where('project_id', $project->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->orWhereBetween('approved_at', [$start, $end])
                    ->orWhereBetween('posted_at', [$start, $end]);
            })
            ->with(['creator', 'approver'])
            ->orderByDesc('created_at')
            ->get();

        $inflowThisWeek = $financeTransactions->whereIn('direction', ['INFLOW', 'INCOME'])->where('status', 'POSTED')->sum('amount');
        $outflowThisWeek = $financeTransactions->whereIn('direction', ['OUTFLOW', 'EXPENSE'])->where('status', 'POSTED')->sum('amount');

        // 5. Site Constraints Delta (Logged this week vs Resolved this week)
        $constraintsOpened = ProjectConstraint::query()
            ->where('project_id', $project->id)
            ->whereBetween('created_at', [$start, $end])
            ->with(['task', 'creator'])
            ->get();

        $constraintsResolved = ProjectConstraint::query()
            ->where('project_id', $project->id)
            ->whereBetween('resolved_at', [$start, $end])
            ->with(['task', 'resolver'])
            ->get();

        $allOpenConstraints = ProjectConstraint::query()
            ->where('project_id', $project->id)
            ->where('status', 'OPEN')
            ->with(['task'])
            ->get();

        // 6. Photo Evidence Highlights captured this week
        $weeklyPhotos = EvidenceDocument::query()
            ->where('project_id', $project->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('captured_at', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end) {
                        $sub->whereNull('captured_at')
                            ->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->with(['task', 'constraint', 'uploader'])
            ->latest()
            ->get();

        return [
            'version' => self::VERSION,
            'report_number' => sprintf('WSR-%s-%04d-W%02d', $project->code, $year, $weekNumber),
            'week_number' => $weekNumber,
            'year' => $year,
            'date_range_label' => sprintf('%s - %s', $start->format('d M Y'), $end->format('d M Y')),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'previous_week' => $start->copy()->subWeek()->startOfWeek()->toDateString(),
            'next_week' => $start->copy()->addWeek()->startOfWeek()->toDateString(),
            'is_current_week' => $start->isCurrentWeek(),
            'health' => $health,
            'evm' => $evm,
            'lookahead' => $lookahead,
            'finance' => [
                'transactions' => $financeTransactions,
                'inflow_this_week' => (float) $inflowThisWeek,
                'outflow_this_week' => (float) $outflowThisWeek,
                'net_cashflow_this_week' => (float) ($inflowThisWeek - $outflowThisWeek),
            ],
            'constraints' => [
                'opened_this_week' => $constraintsOpened,
                'resolved_this_week' => $constraintsResolved,
                'active_open_count' => $allOpenConstraints->count(),
                'critical_blockers_count' => $allOpenConstraints->where('severity', 'CRITICAL')->count(),
            ],
            'photos' => [
                'items' => $weeklyPhotos,
                'total' => $weeklyPhotos->count(),
                'with_gps' => $weeklyPhotos->whereNotNull('gps_latitude')->count(),
                'defects' => $weeklyPhotos->filter(fn ($p) => ($p->metadata['category'] ?? '') === 'DEFECT')->count(),
            ],
        ];
    }
}
