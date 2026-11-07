<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\SiteMachineryLog;
use App\Models\SiteManpowerLog;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiteResourceOperationsService
{
    public const VERSION = 'CED_SITE_RESOURCES_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * 6A: Record daily trade manpower attendance and auto-resolve linked LABOR_CREW constraints when target met.
     *
     * @param  array{
     *     wbs_task_id?: string|null,
     *     constraint_id?: string|null,
     *     log_date: string,
     *     trade: string,
     *     contractor_company?: string|null,
     *     planned_headcount: int|string,
     *     actual_headcount: int|string,
     *     working_hours?: float|int|string|null,
     *     shift?: string|null,
     *     notes?: string|null
     * }  $data
     * @return array{log: SiteManpowerLog, resolved_constraints_count: int}
     */
    public function recordManpowerLog(Project $project, User $actor, array $data): array
    {
        return DB::transaction(function () use ($project, $actor, $data): array {
            if (! $this->access->allows($actor, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $trade = strtoupper((string) $data['trade']);
            if (! in_array($trade, SiteManpowerLog::TRADES, true)) {
                throw new DomainException("Unsupported trade {$trade}.");
            }

            $shift = strtoupper((string) ($data['shift'] ?? 'DAY'));
            if (! in_array($shift, SiteManpowerLog::SHIFTS, true)) {
                throw new DomainException("Unsupported shift {$shift}.");
            }

            $planned = max(0, (int) $data['planned_headcount']);
            $actual = max(0, (int) $data['actual_headcount']);
            $hours = round(max(0.0, (float) ($data['working_hours'] ?? 8.00)), 2);
            $manHours = round($actual * $hours, 2);

            $taskId = $this->validateTask($project, $data['wbs_task_id'] ?? null);
            $constraintId = $this->validateConstraint($project, $data['constraint_id'] ?? null);

            $log = SiteManpowerLog::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'constraint_id' => $constraintId,
                'log_date' => $data['log_date'],
                'trade' => $trade,
                'contractor_company' => $data['contractor_company'] ?? null,
                'planned_headcount' => $planned,
                'actual_headcount' => $actual,
                'working_hours' => $hours,
                'total_man_hours' => $manHours,
                'shift' => $shift,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            $resolvedCount = 0;
            if ($log->meetsTarget()) {
                $resolvedCount = $this->resolveConstraints(
                    $project->id,
                    $constraintId,
                    $taskId,
                    ProjectConstraint::TYPE_LABOR_CREW,
                    sprintf('Auto-resolved: %s crew mobilized (%d/%d workers on site).', $trade, $actual, $planned),
                    $actor,
                    $log->id,
                );
            }

            $this->audit->record($project->id, $actor, 'operations.manpower_logged', $log, [
                'trade' => $trade,
                'actual_headcount' => $actual,
                'total_man_hours' => $manHours,
                'resolved_constraints_count' => $resolvedCount,
            ]);

            return ['log' => $log, 'resolved_constraints_count' => $resolvedCount];
        });
    }

    /**
     * 6B: Record heavy machinery utilization and auto-resolve linked EQUIPMENT constraints when operational.
     *
     * @param  array{
     *     wbs_task_id?: string|null,
     *     constraint_id?: string|null,
     *     log_date: string,
     *     equipment_code: string,
     *     equipment_name: string,
     *     category: string,
     *     status: string,
     *     operating_hours?: float|int|string|null,
     *     downtime_hours?: float|int|string|null,
     *     operator_name?: string|null,
     *     notes?: string|null
     * }  $data
     * @return array{log: SiteMachineryLog, resolved_constraints_count: int}
     */
    public function recordMachineryLog(Project $project, User $actor, array $data): array
    {
        return DB::transaction(function () use ($project, $actor, $data): array {
            if (! $this->access->allows($actor, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $category = strtoupper((string) $data['category']);
            if (! in_array($category, SiteMachineryLog::CATEGORIES, true)) {
                throw new DomainException("Unsupported machinery category {$category}.");
            }

            $status = strtoupper((string) $data['status']);
            if (! in_array($status, SiteMachineryLog::STATUSES, true)) {
                throw new DomainException("Unsupported machinery status {$status}.");
            }

            $operatingHours = round(max(0.0, (float) ($data['operating_hours'] ?? 0.00)), 2);
            $downtimeHours = round(max(0.0, (float) ($data['downtime_hours'] ?? 0.00)), 2);

            $taskId = $this->validateTask($project, $data['wbs_task_id'] ?? null);
            $constraintId = $this->validateConstraint($project, $data['constraint_id'] ?? null);

            $log = SiteMachineryLog::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'constraint_id' => $constraintId,
                'log_date' => $data['log_date'],
                'equipment_code' => strtoupper(trim((string) $data['equipment_code'])),
                'equipment_name' => $data['equipment_name'],
                'category' => $category,
                'status' => $status,
                'operating_hours' => $operatingHours,
                'downtime_hours' => $downtimeHours,
                'operator_name' => $data['operator_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            $resolvedCount = 0;
            if ($status === 'OPERATIONAL' && $operatingHours > 0.0) {
                $resolvedCount = $this->resolveConstraints(
                    $project->id,
                    $constraintId,
                    $taskId,
                    ProjectConstraint::TYPE_EQUIPMENT,
                    sprintf('Auto-resolved: equipment %s (%s) operational on site (%.1f hrs).', $log->equipment_code, $log->equipment_name, $operatingHours),
                    $actor,
                    $log->id,
                );
            }

            $this->audit->record($project->id, $actor, 'operations.machinery_logged', $log, [
                'equipment_code' => $log->equipment_code,
                'status' => $status,
                'operating_hours' => $operatingHours,
                'resolved_constraints_count' => $resolvedCount,
            ]);

            return ['log' => $log, 'resolved_constraints_count' => $resolvedCount];
        });
    }

    private function validateTask(Project $project, ?string $taskId): ?string
    {
        if (empty($taskId)) {
            return null;
        }
        $task = WbsTask::query()->where('project_id', $project->id)->find($taskId);
        if ($task === null) {
            throw new DomainException('Selected WBS task does not belong to this project.');
        }

        return (string) $task->id;
    }

    private function validateConstraint(Project $project, ?string $constraintId): ?string
    {
        if (empty($constraintId)) {
            return null;
        }
        $constraint = ProjectConstraint::query()->where('project_id', $project->id)->find($constraintId);
        if ($constraint === null) {
            throw new DomainException('Selected constraint does not belong to this project.');
        }

        return (string) $constraint->id;
    }

    private function resolveConstraints(
        string $projectId,
        ?string $constraintId,
        ?string $taskId,
        string $constraintType,
        string $resolutionNotes,
        User $actor,
        string $sourceLogId,
    ): int {
        if ($constraintId === null && $taskId === null) {
            return 0;
        }

        $constraints = ProjectConstraint::query()
            ->where('project_id', $projectId)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->where(function ($q) use ($constraintId, $taskId, $constraintType): void {
                if ($constraintId !== null) {
                    $q->orWhere('id', $constraintId);
                }
                if ($taskId !== null) {
                    $q->orWhere(function ($sub) use ($taskId, $constraintType): void {
                        $sub->where('task_id', $taskId)->where('type', $constraintType);
                    });
                }
            })
            ->get();

        foreach ($constraints as $constraint) {
            $constraint->forceFill([
                'status' => ProjectConstraint::STATUS_RESOLVED,
                'resolved_at' => now('UTC'),
                'resolution_notes' => $resolutionNotes,
            ])->save();

            $this->audit->record($projectId, $actor, 'constraint.auto_resolved_by_site_resources', $constraint, [
                'constraint_type' => $constraintType,
                'source_log_id' => $sourceLogId,
            ]);
        }

        return $constraints->count();
    }
}
