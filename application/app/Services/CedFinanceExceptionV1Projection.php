<?php

namespace App\Services;

use App\Domain\Finance\FinanceStatus;
use App\Models\Project;

class CedFinanceExceptionV1Projection
{
    /**
     * @return array{dimension:'COST',project_id:string,health_rule_version:string,health_calculated_at:string,finance_rule_version:string,finance_calculated_at:string,status:'AMBER'|'RED',value:int|float,unit:string,reason:string,source_scope:list<array{source_type:'finance_transaction',source_id:string,project_id:string,label:string}>}|null
     */
    public function forProject(Project $project, mixed $health, mixed $finance): ?array
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

        $cost = $health['dimensions']['COST'] ?? null;
        $costReason = $health['reasons']['COST'] ?? null;
        $costSources = $health['source_scope']['cost'] ?? null;

        if (! is_array($cost)
            || ! isset($cost['status'], $cost['value'], $cost['unit'], $cost['reason'])
            || ! is_string($cost['status'])
            || ! in_array($cost['status'], ['AMBER', 'RED'], true)
            || (! is_int($cost['value']) && ! is_float($cost['value']))
            || ! is_finite((float) $cost['value'])
            || ! is_string($cost['unit'])
            || trim($cost['unit']) === ''
            || ! is_string($cost['reason'])
            || trim($cost['reason']) === ''
            || ! is_string($costReason)
            || $costReason !== $cost['reason']
            || ! is_array($costSources)
            || ! array_is_list($costSources)) {
            return null;
        }

        if (! is_array($finance)
            || ! isset(
                $finance['rule_version'],
                $finance['calculated_at'],
                $finance['project_id'],
                $finance['contributing_source_record_scope'],
                $finance['income'],
                $finance['expense'],
                $finance['net'],
                $finance['explanation'],
            )
            || $finance['rule_version'] !== CedFinanceV1ReadModel::VERSION
            || ! is_string($finance['calculated_at'])
            || trim($finance['calculated_at']) === ''
            || ! is_string($finance['project_id'])
            || $finance['project_id'] !== $projectId
            || ! is_array($finance['contributing_source_record_scope'])
            || ! $this->isCanonicalMoney($finance['income'])
            || ! $this->isCanonicalMoney($finance['expense'])
            || ! $this->isCanonicalMoney($finance['net'])
            || ! is_string($finance['explanation'])
            || trim($finance['explanation']) === '') {
            return null;
        }

        $financeScope = $finance['contributing_source_record_scope'];
        $includedRecordIds = $financeScope['included_record_ids'] ?? null;

        if (! isset($financeScope['project_id'], $financeScope['status'])
            || ! is_string($financeScope['project_id'])
            || $financeScope['project_id'] !== $projectId
            || $financeScope['status'] !== FinanceStatus::Posted->value
            || ! is_array($includedRecordIds)
            || ! array_is_list($includedRecordIds)
            || ! $this->hasUniqueNonEmptyStringIds($includedRecordIds)) {
            return null;
        }

        $costSourceIds = [];

        foreach ($costSources as $source) {
            if (! is_array($source)
                || ! isset($source['source_type'], $source['source_id'], $source['project_id'], $source['label'])
                || $source['source_type'] !== 'finance_transaction'
                || ! is_string($source['source_id'])
                || trim($source['source_id']) === ''
                || ! is_string($source['project_id'])
                || $source['project_id'] !== $projectId
                || ! is_string($source['label'])
                || trim($source['label']) === ''
                || in_array($source['source_id'], $costSourceIds, true)) {
                return null;
            }

            $costSourceIds[] = $source['source_id'];
        }

        if ($costSourceIds !== $includedRecordIds) {
            return null;
        }

        return [
            'dimension' => 'COST',
            'project_id' => $health['project_id'],
            'health_rule_version' => $health['rule_version'],
            'health_calculated_at' => $health['calculated_at'],
            'finance_rule_version' => $finance['rule_version'],
            'finance_calculated_at' => $finance['calculated_at'],
            'status' => $cost['status'],
            'value' => $cost['value'],
            'unit' => $cost['unit'],
            'reason' => $cost['reason'],
            'source_scope' => $costSources,
        ];
    }

    private function isCanonicalMoney(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^-?(?:0|[1-9]\d*)\.\d{2}$/D', $value) === 1;
    }

    /** @param list<mixed> $ids */
    private function hasUniqueNonEmptyStringIds(array $ids): bool
    {
        $seen = [];

        foreach ($ids as $id) {
            if (! is_string($id)
                || trim($id) === ''
                || in_array($id, $seen, true)) {
                return false;
            }

            $seen[] = $id;
        }

        return true;
    }
}
