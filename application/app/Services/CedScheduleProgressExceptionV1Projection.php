<?php

namespace App\Services;

use App\Models\Project;

class CedScheduleProgressExceptionV1Projection
{
    /**
     * @return array{dimension:'SCHEDULE',project_id:string,rule_version:string,calculated_at:string,status:'AMBER'|'RED',value:int|float,unit:string,reason:string,source_scope:list<array{source_type:string,source_id:string,project_id:string,label:string}>}|null
     */
    public function forProject(Project $project, mixed $health): ?array
    {
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
            || $health['project_id'] !== (string) $project->id
            || ! is_string($health['rule_version'])
            || trim($health['rule_version']) === ''
            || ! is_string($health['calculated_at'])
            || trim($health['calculated_at']) === ''
            || ! is_string($health['status'])
            || ! in_array($health['status'], ['AMBER', 'RED'], true)
            || ! is_array($health['dimensions'])
            || ! is_array($health['reasons'])
            || ! is_array($health['source_scope'])) {
            return null;
        }

        $schedule = $health['dimensions']['SCHEDULE'] ?? null;
        $scheduleReason = $health['reasons']['SCHEDULE'] ?? null;
        $scheduleSources = $health['source_scope']['schedule'] ?? null;

        if (! is_array($schedule)
            || ! isset($schedule['status'], $schedule['value'], $schedule['unit'], $schedule['reason'])
            || ! is_string($schedule['status'])
            || ! in_array($schedule['status'], ['AMBER', 'RED'], true)
            || (! is_int($schedule['value']) && ! is_float($schedule['value']))
            || ! is_finite((float) $schedule['value'])
            || ! is_string($schedule['unit'])
            || trim($schedule['unit']) === ''
            || ! is_string($schedule['reason'])
            || trim($schedule['reason']) === ''
            || ! is_string($scheduleReason)
            || $scheduleReason !== $schedule['reason']
            || ! is_array($scheduleSources)
            || ! array_is_list($scheduleSources)) {
            return null;
        }

        foreach ($scheduleSources as $source) {
            if (! is_array($source)
                || ! isset($source['source_type'], $source['source_id'], $source['project_id'], $source['label'])
                || ! is_string($source['source_type'])
                || trim($source['source_type']) === ''
                || ! is_string($source['source_id'])
                || trim($source['source_id']) === ''
                || ! is_string($source['project_id'])
                || $source['project_id'] !== (string) $project->id
                || ! is_string($source['label'])
                || trim($source['label']) === '') {
                return null;
            }
        }

        return [
            'dimension' => 'SCHEDULE',
            'project_id' => $health['project_id'],
            'rule_version' => $health['rule_version'],
            'calculated_at' => $health['calculated_at'],
            'status' => $schedule['status'],
            'value' => $schedule['value'],
            'unit' => $schedule['unit'],
            'reason' => $schedule['reason'],
            'source_scope' => $scheduleSources,
        ];
    }
}
