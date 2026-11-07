<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectConstraint;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EarlyWarningAlertEngine
{
    public const VERSION = 'CED_EARLY_WARNING_ALERTS_V1';

    public function __construct(
        protected CedFinanceV1ReadModel $financeReadModel,
        protected WeeklyLookaheadService $lookaheadService,
    ) {}

    /**
     * @return array{
     *     version: string,
     *     evaluated_at: string,
     *     project: array{id: string, code: string, name: string},
     *     alerts: list<array{
     *         id: string,
     *         category: string,
     *         severity: string,
     *         title: string,
     *         message: string,
     *         entity_type: string,
     *         entity_id: string,
     *         action_url: string
     *     }>,
     *     summary: array{
     *         total_alerts: int,
     *         critical_count: int,
     *         warning_count: int,
     *         highest_severity: string
     *     }
     * }
     */
    public function forProject(Project $project, ?Carbon $now = null): array
    {
        $evaluationTime = $now ? $now->copy() : now();
        $evalDate = $evaluationTime->toDateString();
        $alerts = [];

        // 1. Evaluate SCHEDULE_OVERDUE
        $wbsTasks = $project->wbsTasks()
            ->where('is_leaf', true)
            ->where('status', '!=', 'COMPLETED')
            ->whereDate('baseline_finish_date', '<', $evalDate)
            ->orderBy('baseline_finish_date')
            ->get();

        $latestProgressByTask = $project->progressEntries()
            ->whereIn('task_id', $wbsTasks->pluck('id'))
            ->orderBy('progress_date')
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($entry): string => (string) $entry->task_id)
            ->map(fn (Collection $history) => $history->last());

        foreach ($wbsTasks as $task) {
            $progress = $latestProgressByTask->get((string) $task->id);
            $pct = $progress !== null ? (float) $progress->progress_percent : 0.0;
            if ($pct >= 100.0) {
                continue;
            }

            $daysOverdue = Carbon::parse($task->baseline_finish_date)->diffInDays($evaluationTime);
            $alerts[] = [
                'id' => 'alert_sched_overdue_'.$task->id,
                'category' => 'SCHEDULE_OVERDUE',
                'severity' => 'CRITICAL',
                'title' => "Task Overdue: [{$task->wbs_code}] {$task->name}",
                'message' => "Finish date was {$task->baseline_finish_date->toDateString()} ({$daysOverdue} days overdue). Current progress is ".number_format($pct, 1).'%.',
                'entity_type' => 'TASK',
                'entity_id' => $task->id,
                'action_url' => route('projects.schedule.show', $project),
            ];
        }

        // 2. Evaluate LOOKAHEAD_BLOCKED (upcoming 14 days)
        $lookahead = $this->lookaheadService->forProject($project, 2, $evaluationTime);
        foreach ($lookahead['tasks'] as $lTask) {
            if (! $lTask['is_make_ready'] && ! empty($lTask['open_constraints'])) {
                $constraintCount = count($lTask['open_constraints']);
                $hasCritical = collect($lTask['open_constraints'])->contains('severity', 'CRITICAL');
                $alerts[] = [
                    'id' => 'alert_lookahead_blocked_'.$lTask['id'],
                    'category' => 'LOOKAHEAD_BLOCKED',
                    'severity' => $hasCritical ? 'CRITICAL' : 'WARNING',
                    'title' => "Upcoming Task Blocked: [{$lTask['wbs_code']}] {$lTask['name']}",
                    'message' => "Scheduled in lookahead horizon with {$constraintCount} unresolved site impediment(s).",
                    'entity_type' => 'TASK',
                    'entity_id' => $lTask['id'],
                    'action_url' => route('projects.lookahead', $project),
                ];
            }
        }

        // 3. Evaluate CRITICAL_CONSTRAINT_STALE
        $openConstraints = $project->constraints()
            ->open()
            ->whereIn('severity', [ProjectConstraint::SEVERITY_CRITICAL, ProjectConstraint::SEVERITY_HIGH])
            ->get();

        foreach ($openConstraints as $c) {
            $isOverdueDate = $c->target_resolution_date !== null && $c->target_resolution_date->toDateString() < $evalDate;
            $daysOpen = $c->created_at ? $c->created_at->diffInDays($evaluationTime) : 0;
            $isAged = $daysOpen >= 7;

            if ($isOverdueDate || $isAged) {
                $severity = $c->severity === ProjectConstraint::SEVERITY_CRITICAL ? 'CRITICAL' : 'WARNING';
                $reason = $isOverdueDate
                    ? "Target resolution date was {$c->target_resolution_date->toDateString()}"
                    : "Open for {$daysOpen} days without resolution";

                $alerts[] = [
                    'id' => 'alert_stale_constraint_'.$c->id,
                    'category' => 'CRITICAL_CONSTRAINT_STALE',
                    'severity' => $severity,
                    'title' => "{$c->severity} Impediment Stale: {$c->title}",
                    'message' => "{$reason}. Type: {$c->type}.",
                    'entity_type' => 'CONSTRAINT',
                    'entity_id' => $c->id,
                    'action_url' => route('projects.constraints', $project),
                ];
            }
        }

        // 4. Evaluate BUDGET_BURNOUT
        $budget = (float) ($project->budget_amount ?? 0);
        if ($budget > 0) {
            $finance = $this->financeReadModel->forProject($project);
            $expense = (float) ($finance['expense'] ?? 0);
            $utilization = ($expense / $budget) * 100;

            if ($utilization >= 90.0) {
                $isOverBudget = $utilization >= 100.0;
                $alerts[] = [
                    'id' => 'alert_budget_burnout_'.$project->id,
                    'category' => 'BUDGET_BURNOUT',
                    'severity' => $isOverBudget ? 'CRITICAL' : 'WARNING',
                    'title' => $isOverBudget ? 'Budget Exceeded (100%+)' : 'Budget Burnout Warning (90%+)',
                    'message' => 'Total posted expense of '.number_format($expense, 2).' has utilized '.number_format($utilization, 1).'% of planned budget ('.number_format($budget, 2).').',
                    'entity_type' => 'PROJECT',
                    'entity_id' => $project->id,
                    'action_url' => route('projects.finance', $project),
                ];
            }
        }

        $criticalCount = collect($alerts)->where('severity', 'CRITICAL')->count();
        $warningCount = collect($alerts)->where('severity', 'WARNING')->count();
        $highestSeverity = $criticalCount > 0 ? 'CRITICAL' : ($warningCount > 0 ? 'WARNING' : 'NONE');

        return [
            'version' => self::VERSION,
            'evaluated_at' => $evaluationTime->toIso8601String(),
            'project' => [
                'id' => $project->id,
                'code' => $project->code,
                'name' => $project->name,
            ],
            'alerts' => $alerts,
            'summary' => [
                'total_alerts' => count($alerts),
                'critical_count' => $criticalCount,
                'warning_count' => $warningCount,
                'highest_severity' => $highestSeverity,
            ],
        ];
    }
}
