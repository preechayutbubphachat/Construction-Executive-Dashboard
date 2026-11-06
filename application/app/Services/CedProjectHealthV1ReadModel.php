<?php

namespace App\Services;

use App\Models\Project;
use Carbon\CarbonImmutable;
use DomainException;

class CedProjectHealthV1ReadModel
{
    public function __construct(
        private readonly ProgressCalculator $progress,
        private readonly ProjectHealthCalculator $health,
        private readonly CedFinanceV1ReadModel $finance,
    ) {}

    /** @return array{project_id:string,rule_version:string,calculated_at:string,status:string,dimensions:array<string,mixed>,reasons:array<string,string>,source_scope:array<string,list<array<string,mixed>>>,explanation:string} */
    public function forProject(Project $project): array
    {
        $evaluatedAt = CarbonImmutable::now('UTC');
        $tasks = $project->wbsTasks()
            ->where('is_baseline', true)
            ->whereDoesntHave('children')
            ->orderBy('wbs_code')->get();
        $entries = $project->progressEntries()
            ->whereIn('task_id', $tasks->pluck('id'))
            ->whereDate('progress_date', '<=', $evaluatedAt->toDateString())->get();
        $scheduleSources = $tasks->map(fn ($task): array => [
            'source_type' => 'wbs_task',
            'source_id' => (string) $task->id,
            'project_id' => (string) $project->id,
            'label' => "Task {$task->wbs_code} · {$task->name}",
        ])->concat($entries->map(fn ($entry): array => [
            'source_type' => 'progress_entry',
            'source_id' => (string) $entry->id,
            'project_id' => (string) $project->id,
            'label' => "Progress entry {$entry->id}",
        ]))->values()->all();
        $latestProgress = $entries->sortByDesc('recorded_at')->first();
        $freshnessSources = $latestProgress === null ? [] : [[
            'source_type' => 'progress_entry',
            'source_id' => (string) $latestProgress->id,
            'project_id' => (string) $project->id,
            'label' => "Progress entry {$latestProgress->id}",
        ]];
        $risks = $project->risks()->orderBy('id')->get();
        $riskSources = $risks->filter(fn ($risk) => $risk->status === 'OPEN' && in_array($risk->severity, ['HIGH', 'CRITICAL'], true))
            ->map(fn ($risk): array => [
                'source_type' => 'project_risk',
                'source_id' => (string) $risk->id,
                'project_id' => (string) $project->id,
                'label' => "Risk {$risk->id} · {$risk->title}",
            ])->values()->all();
        $finance = $this->finance->forProject($project);
        $costSources = collect($finance['contributing_source_record_scope']['included_record_ids'])
            ->map(fn (string $id): array => [
                'source_type' => 'finance_transaction',
                'source_id' => $id,
                'project_id' => (string) $project->id,
                'label' => "Finance transaction {$id}",
            ])->values()->all();
        $sourceScope = [
            'schedule' => $scheduleSources,
            'cost' => $costSources,
            'risk' => $riskSources,
            'freshness' => $freshnessSources,
        ];
        $unknown = [
            'project_id' => (string) $project->id,
            'rule_version' => ProjectHealthCalculator::VERSION,
            'calculated_at' => $evaluatedAt->utc()->format('Y-m-d\TH:i:s\Z'),
            'status' => 'UNKNOWN',
            'dimensions' => [],
            'reasons' => [],
            'source_scope' => $sourceScope,
            'explanation' => 'Health is unavailable from accepted inputs.',
        ];

        if ($tasks->isEmpty()) {
            return $unknown;
        }

        try {
            $actual = $this->progress->actual($tasks, $entries, $evaluatedAt);
            $planned = $this->progress->planned($tasks, $evaluatedAt);
            $freshnessAge = $latestProgress === null ? null : max(0.0, $latestProgress->recorded_at->diffInHours($evaluatedAt));
            $budget = (float) $project->budget_amount;
            $costUsage = $budget > 0.0 ? round(((float) $finance['expense'] / $budget) * 100.0, 4) : null;
            $health = $this->health->evaluate(
                scheduleVariancePoints: $actual['data_status'] === 'COMPLETE' && $planned['data_status'] === 'COMPLETE' ? $actual['value'] - $planned['value'] : null,
                costUsagePercent: $costUsage,
                highRiskCount: count($riskSources),
                freshnessAgeHours: $freshnessAge,
                evaluatedAt: $evaluatedAt,
                sourceScope: ['project_id' => $project->id],
            );

            return [
                'project_id' => (string) $project->id,
                'rule_version' => $health['formula_version'],
                'calculated_at' => $health['evaluated_at'],
                'status' => $health['overall_status'],
                'dimensions' => $health['dimensions'],
                'reasons' => $health['reasons'],
                'source_scope' => $sourceScope,
                'explanation' => 'Health evaluated by accepted '.$health['formula_version'].' rules.',
            ];
        } catch (DomainException) {
            return $unknown;
        }
    }
}
