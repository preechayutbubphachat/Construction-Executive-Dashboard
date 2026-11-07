<?php

namespace App\Services;

use App\Models\HseSafetyLog;
use App\Models\PermitToWork;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HseSafetyOperationsService
{
    public const VERSION = 'CED_HSE_SAFETY_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @param  array{
     *     wbs_task_id?: string|null,
     *     constraint_id?: string|null,
     *     permit_type: string,
     *     work_title: string,
     *     location_zone?: string|null,
     *     valid_from: string,
     *     valid_until: string,
     *     precautions_checklist?: string|null
     * }  $data
     */
    public function requestPermit(Project $project, User $requester, array $data): PermitToWork
    {
        return DB::transaction(function () use ($project, $requester, $data): PermitToWork {
            if (! $this->access->allows($requester, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $type = strtoupper((string) $data['permit_type']);
            if (! in_array($type, PermitToWork::TYPES, true)) {
                throw new DomainException("Unsupported permit type {$type}.");
            }

            $taskId = ! empty($data['wbs_task_id']) ? (string) $data['wbs_task_id'] : null;
            if ($taskId !== null) {
                $task = WbsTask::query()->where('project_id', $project->id)->find($taskId);
                if ($task === null) {
                    throw new DomainException('Selected WBS task does not belong to this project.');
                }
            }

            $constraintId = ! empty($data['constraint_id']) ? (string) $data['constraint_id'] : null;
            if ($constraintId !== null) {
                $constraint = ProjectConstraint::query()->where('project_id', $project->id)->find($constraintId);
                if ($constraint === null) {
                    throw new DomainException('Selected constraint does not belong to this project.');
                }
            }

            $last = PermitToWork::query()
                ->where('project_id', $project->id)
                ->orderByDesc('ptw_number')
                ->first();
            $number = ($last?->ptw_number ?? 0) + 1;
            $reference = sprintf('PTW-%s-%03d', $project->code, $number);

            $permit = PermitToWork::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'constraint_id' => $constraintId,
                'ptw_number' => $number,
                'reference_no' => $reference,
                'permit_type' => $type,
                'work_title' => $data['work_title'],
                'location_zone' => $data['location_zone'] ?? null,
                'valid_from' => $data['valid_from'],
                'valid_until' => $data['valid_until'],
                'precautions_checklist' => $data['precautions_checklist'] ?? null,
                'status' => 'REQUESTED',
                'created_by' => $requester->id,
            ]);

            $this->audit->record($project->id, $requester, 'hse.ptw_requested', $permit, [
                'reference_no' => $reference,
                'permit_type' => $type,
            ]);

            return $permit;
        });
    }

    /**
     * Safety Officer / Project Manager / Executive approves Permit-to-Work (enforces Maker-Checker)
     * and auto-resolves linked PERMIT constraints.
     *
     * @return array{permit: PermitToWork, resolved_constraints_count: int}
     */
    public function approvePermit(PermitToWork $permit, User $officer, ?string $notes = null): array
    {
        return DB::transaction(function () use ($permit, $officer, $notes): array {
            $this->requireSafetyAuthority($officer, $permit->project);

            if ($permit->status !== 'REQUESTED') {
                throw new DomainException("Permit-to-Work must be REQUESTED to approve; current status is {$permit->status}.");
            }

            if ((int) $permit->created_by === (int) $officer->id) {
                throw new DomainException('Maker-checker compliance violation: the requester of a high-risk Permit-to-Work cannot self-approve it.');
            }

            $permit->forceFill([
                'status' => 'APPROVED',
                'approved_by' => $officer->id,
                'approved_at' => now('UTC'),
                'safety_notes' => $notes ?: 'Safety precautions inspected and approved for execution.',
            ])->save();

            $resolvedCount = $this->resolveLinkedPermitConstraints($permit, $officer);

            $this->audit->record($permit->project_id, $officer, 'hse.ptw_approved', $permit, [
                'reference_no' => $permit->reference_no,
                'approved_by' => $officer->name,
                'resolved_constraints_count' => $resolvedCount,
            ]);

            return [
                'permit' => $permit,
                'resolved_constraints_count' => $resolvedCount,
            ];
        });
    }

    public function revokePermit(PermitToWork $permit, User $officer, string $reason): PermitToWork
    {
        return DB::transaction(function () use ($permit, $officer, $reason): PermitToWork {
            $this->requireSafetyAuthority($officer, $permit->project);

            $permit->forceFill([
                'status' => 'REVOKED',
                'safety_notes' => 'Revoked: '.$reason,
            ])->save();

            $this->audit->record($permit->project_id, $officer, 'hse.ptw_revoked', $permit, [
                'reason' => $reason,
            ]);

            return $permit;
        });
    }

    /**
     * @param  array{
     *     log_date: string,
     *     record_type: string,
     *     title: string,
     *     description?: string|null,
     *     attendees_count?: int|string|null,
     *     lost_time_hours?: float|int|string|null
     * }  $data
     */
    public function recordSafetyEvent(Project $project, User $actor, array $data): HseSafetyLog
    {
        return DB::transaction(function () use ($project, $actor, $data): HseSafetyLog {
            if (! $this->access->allows($actor, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $type = strtoupper((string) $data['record_type']);
            if (! in_array($type, HseSafetyLog::RECORD_TYPES, true)) {
                throw new DomainException("Unsupported HSE record type {$type}.");
            }

            $log = HseSafetyLog::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'log_date' => $data['log_date'],
                'record_type' => $type,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'attendees_count' => max(0, (int) ($data['attendees_count'] ?? 0)),
                'lost_time_hours' => round(max(0.0, (float) ($data['lost_time_hours'] ?? 0.00)), 2),
                'created_by' => $actor->id,
            ]);

            $this->audit->record($project->id, $actor, 'hse.event_logged', $log, [
                'record_type' => $type,
                'title' => $log->title,
            ]);

            return $log;
        });
    }

    /**
     * Calculates project-wide HSE KPIs including Safe Man-Hours since the last Lost Time Injury (LTI).
     *
     * @return array{
     *     total_man_hours: float,
     *     safe_man_hours: float,
     *     lti_count: int,
     *     near_miss_count: int,
     *     toolbox_talks_count: int,
     *     active_permits_count: int,
     *     open_permit_constraints: int
     * }
     */
    public function calculateSafetyMetrics(Project $project): array
    {
        $totalManHours = (float) $project->manpowerLogs()->sum('total_man_hours');

        $latestLti = $project->hseSafetyLogs()
            ->where('record_type', 'LOST_TIME_INJURY')
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->first();

        if ($latestLti === null) {
            $safeManHours = $totalManHours;
        } else {
            $safeManHours = (float) $project->manpowerLogs()
                ->where('log_date', '>', $latestLti->log_date->toDateString())
                ->sum('total_man_hours');
        }

        return [
            'total_man_hours' => round($totalManHours, 1),
            'safe_man_hours' => round($safeManHours, 1),
            'lti_count' => $project->hseSafetyLogs()->where('record_type', 'LOST_TIME_INJURY')->count(),
            'near_miss_count' => $project->hseSafetyLogs()->where('record_type', 'NEAR_MISS')->count(),
            'toolbox_talks_count' => $project->hseSafetyLogs()->where('record_type', 'TOOLBOX_TALK')->count(),
            'active_permits_count' => $project->permitToWorks()->where('status', 'APPROVED')->count(),
            'open_permit_constraints' => $project->constraints()
                ->where('type', ProjectConstraint::TYPE_PERMIT)
                ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
                ->count(),
        ];
    }

    private function resolveLinkedPermitConstraints(PermitToWork $permit, User $actor): int
    {
        if ($permit->constraint_id === null && $permit->wbs_task_id === null) {
            return 0;
        }

        $constraints = ProjectConstraint::query()
            ->where('project_id', $permit->project_id)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->where(function ($q) use ($permit): void {
                if ($permit->constraint_id !== null) {
                    $q->orWhere('id', $permit->constraint_id);
                }
                if ($permit->wbs_task_id !== null) {
                    $q->orWhere(function ($sub) use ($permit): void {
                        $sub->where('task_id', $permit->wbs_task_id)
                            ->where('type', ProjectConstraint::TYPE_PERMIT);
                    });
                }
            })
            ->get();

        foreach ($constraints as $constraint) {
            $constraint->forceFill([
                'status' => ProjectConstraint::STATUS_RESOLVED,
                'resolved_at' => now('UTC'),
                'resolution_notes' => sprintf(
                    'Auto-resolved upon safety approval of Permit-to-Work %s (%s: %s).',
                    $permit->reference_no,
                    $permit->permit_type,
                    $permit->work_title
                ),
            ])->save();

            $this->audit->record($permit->project_id, $actor, 'constraint.auto_resolved_by_permit', $constraint, [
                'permit_id' => $permit->id,
                'reference_no' => $permit->reference_no,
            ]);
        }

        return $constraints->count();
    }

    private function requireSafetyAuthority(User $user, Project $project): void
    {
        if (! $this->access->allows($user, $project, ['SAFETY_OFFICER', 'PROJECT_MANAGER', 'EXECUTIVE'])) {
            throw new AuthorizationException('Only Safety Officer, Project Manager, or Executive can authorize or revoke a Permit-to-Work.');
        }
    }
}
