<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Collection;

class ExecutivePortfolioReadModel
{
    public function __construct(
        private readonly CedProjectHealthV1ReadModel $healthBoundary,
        private readonly CedFinanceV1ReadModel $financeBoundary,
        private readonly CedScheduleProgressExceptionV1Projection $scheduleProgressProjection,
        private readonly CedFinanceExceptionV1Projection $financeExceptionProjection,
    ) {}

    /** @return list<array<string, mixed>> */
    public function forProjects(Collection $projects): array
    {
        return $projects
            ->sortBy(fn (Project $project): string => implode('|', [(string) $project->code, (string) $project->id]))
            ->values()
            ->map(fn (Project $project): array => $this->forProject($project))
            ->all();
    }

    /** @return array<string, mixed> */
    public function forProject(Project $project): array
    {
        $health = $this->healthBoundary->forProject($project);
        $finance = $this->financeBoundary->forProject($project);
        $tasks = $project->wbsTasks()->where('is_baseline', true)->whereDoesntHave('children')->orderBy('wbs_code')->orderBy('id')->get();
        $entries = $project->progressEntries()->whereIn('task_id', $tasks->pluck('id'))->whereDate('progress_date', '<=', now('UTC')->toDateString())->get();

        return [
            'project' => $project,
            'status' => $project->status,
            'health' => $this->presentableHealth($health),
            'decision_required' => $this->decisionRequired($health),
            'schedule_progress_exception' => $this->scheduleProgressProjection->forProject($project, $health),
            'finance_exception' => $this->financeExceptionProjection->forProject($project, $health, $finance),
            'evidence_data_quality_exception' => $this->evidenceDataQualityException($project, $health),
            'finance' => $finance,
            'schedule_source_scope' => [
                'project_id' => (string) $project->id,
                'task_ids' => $tasks->pluck('id')->map(fn ($id): string => (string) $id)->sort()->values()->all(),
                'progress_entry_ids' => $entries->pluck('id')->map(fn ($id): string => (string) $id)->sort()->values()->all(),
            ],
            'evidence_ids' => $project->evidenceDocuments()->orderBy('id')->pluck('id')->map(fn ($id): string => (string) $id)->all(),
            'audit_ids' => $project->auditEvents()->latest('occurred_at')->limit(20)->pluck('id')->map(fn ($id): string => (string) $id)->all(),
        ];
    }

    /** @param array<string, mixed> $health */
    private function presentableHealth(array $health): ?array
    {
        if (! $this->hasRenderableHealthShape($health)) {
            return null;
        }

        return $health['status'] === 'UNKNOWN' && $health['dimensions'] === [] ? null : $health;
    }

    /**
     * @param  array<string, mixed>  $health
     * @return array{required:bool,severity:?string,reasons:array<string, array{status:string,reason:string,source_scope:list<array<string, mixed>>}>}
     */
    private function decisionRequired(array $health): array
    {
        $noSignal = [
            'required' => false,
            'severity' => null,
            'reasons' => [],
        ];

        if (! $this->hasRenderableHealthShape($health) || ! in_array($health['status'], ['AMBER', 'RED'], true)) {
            return $noSignal;
        }

        $reasons = [];

        foreach ($health['dimensions'] as $dimension => $signal) {
            if (! in_array($signal['status'], ['GREEN', 'UNKNOWN', 'AMBER', 'RED'], true)) {
                return $noSignal;
            }

            $sourceKey = $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower($dimension);
            $sourceScope = $health['source_scope'][$sourceKey];

            if (! in_array($signal['status'], ['AMBER', 'RED'], true) || trim($signal['reason']) === '') {
                continue;
            }

            $reasons[$dimension] = [
                'status' => $signal['status'],
                'reason' => $signal['reason'],
                'source_scope' => $sourceScope,
            ];
        }

        if ($reasons === []) {
            return $noSignal;
        }

        return [
            'required' => true,
            'severity' => $health['status'],
            'reasons' => $reasons,
        ];
    }

    /**
     * @return array{dimension:'DATA_FRESHNESS',project_id:string,rule_version:string,calculated_at:string,status:'AMBER'|'RED',value:int|float,unit:string,reason:string,source_scope:list<array{source_type:'progress_entry',source_id:string,project_id:string,label:string}>}|null
     */
    private function evidenceDataQualityException(Project $project, mixed $health): ?array
    {
        $projectId = (string) $project->getKey();

        if (! is_array($health)
            || ! isset(
                $health['project_id'],
                $health['rule_version'],
                $health['calculated_at'],
                $health['status'],
                $health['dimensions'],
                $health['reasons'],
                $health['source_scope'],
            )
            || ! is_string($health['project_id'])
            || $health['project_id'] !== $projectId
            || $health['rule_version'] !== ProjectHealthCalculator::VERSION
            || ! is_string($health['calculated_at'])
            || trim($health['calculated_at']) === ''
            || ! is_string($health['status'])
            || ! in_array($health['status'], ['AMBER', 'RED'], true)
            || ! is_array($health['dimensions'])
            || ! is_array($health['reasons'])
            || ! is_array($health['source_scope'])) {
            return null;
        }

        $freshness = $health['dimensions']['DATA_FRESHNESS'] ?? null;
        $freshnessReason = $health['reasons']['DATA_FRESHNESS'] ?? null;
        $freshnessSources = $health['source_scope']['freshness'] ?? null;

        if (! is_array($freshness)
            || ! isset($freshness['status'], $freshness['value'], $freshness['unit'], $freshness['reason'])
            || ! is_string($freshness['status'])
            || ! in_array($freshness['status'], ['AMBER', 'RED'], true)
            || (! is_int($freshness['value']) && ! is_float($freshness['value']))
            || ! is_finite((float) $freshness['value'])
            || ! is_string($freshness['unit'])
            || trim($freshness['unit']) === ''
            || ! is_string($freshness['reason'])
            || trim($freshness['reason']) === ''
            || ! is_string($freshnessReason)
            || $freshnessReason !== $freshness['reason']
            || ! is_array($freshnessSources)
            || ! array_is_list($freshnessSources)
            || $freshnessSources === []) {
            return null;
        }

        $sourceIds = [];

        foreach ($freshnessSources as $source) {
            if (! is_array($source)
                || ! isset($source['source_type'], $source['source_id'], $source['project_id'], $source['label'])
                || $source['source_type'] !== 'progress_entry'
                || ! is_string($source['source_id'])
                || trim($source['source_id']) === ''
                || ! is_string($source['project_id'])
                || $source['project_id'] !== $projectId
                || ! is_string($source['label'])
                || trim($source['label']) === ''
                || in_array($source['source_id'], $sourceIds, true)) {
                return null;
            }

            $sourceIds[] = $source['source_id'];
        }

        return [
            'dimension' => 'DATA_FRESHNESS',
            'project_id' => $health['project_id'],
            'rule_version' => $health['rule_version'],
            'calculated_at' => $health['calculated_at'],
            'status' => $freshness['status'],
            'value' => $freshness['value'],
            'unit' => $freshness['unit'],
            'reason' => $freshness['reason'],
            'source_scope' => $freshnessSources,
        ];
    }

    /** @param array<string, mixed> $health */
    private function hasRenderableHealthShape(array $health): bool
    {
        if (! isset(
            $health['project_id'],
            $health['rule_version'],
            $health['calculated_at'],
            $health['status'],
            $health['dimensions'],
            $health['reasons'],
            $health['source_scope'],
            $health['explanation'],
        )
            || ! is_string($health['project_id'])
            || ! is_string($health['rule_version'])
            || ! is_string($health['calculated_at'])
            || ! is_string($health['status'])
            || ! is_array($health['dimensions'])
            || ! is_array($health['reasons'])
            || ! is_array($health['source_scope'])
            || ! is_string($health['explanation'])) {
            return false;
        }

        foreach ($health['dimensions'] as $dimension => $signal) {
            $sourceKey = is_string($dimension) && $dimension === 'DATA_FRESHNESS' ? 'freshness' : strtolower((string) $dimension);

            if (! is_string($dimension)
                || ! is_array($signal)
                || ! isset($signal['status'], $signal['reason'])
                || ! is_string($signal['status'])
                || ! is_string($signal['reason'])
                || ! array_key_exists($dimension, $health['reasons'])
                || ! is_string($health['reasons'][$dimension])
                || $health['reasons'][$dimension] !== $signal['reason']
                || ! $this->hasCanonicalSourceScope($health['source_scope'][$sourceKey] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private function hasCanonicalSourceScope(mixed $sourceScope): bool
    {
        if (! is_array($sourceScope) || ! array_is_list($sourceScope)) {
            return false;
        }

        foreach ($sourceScope as $source) {
            if (! is_array($source)
                || ! isset($source['source_type'], $source['source_id'], $source['project_id'], $source['label'])
                || ! is_string($source['source_type'])
                || ! is_string($source['source_id'])
                || ! is_string($source['project_id'])
                || ! is_string($source['label'])) {
                return false;
            }
        }

        return true;
    }
}
