<?php

namespace App\Services;

use App\Models\NonConformanceReport;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NonConformanceReportService
{
    public const VERSION = 'CED_NCR_WORKFLOW_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @param  array{
     *     wbs_task_id?: string|null,
     *     title: string,
     *     description?: string|null,
     *     category?: string|null,
     *     severity?: string|null,
     *     hold_payment?: bool|int|string|null,
     *     location_zone?: string|null
     * }  $data
     */
    public function issueNcr(Project $project, User $issuer, array $data): NonConformanceReport
    {
        return DB::transaction(function () use ($project, $issuer, $data): NonConformanceReport {
            if (! $this->access->allows($issuer, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $category = strtoupper((string) ($data['category'] ?? 'WORKMANSHIP'));
            if (! in_array($category, NonConformanceReport::CATEGORIES, true)) {
                throw new DomainException("Unsupported NCR category {$category}.");
            }

            $severity = strtoupper((string) ($data['severity'] ?? 'MAJOR'));
            if (! in_array($severity, NonConformanceReport::SEVERITIES, true)) {
                throw new DomainException("Unsupported NCR severity {$severity}.");
            }

            $taskId = ! empty($data['wbs_task_id']) ? (string) $data['wbs_task_id'] : null;
            if ($taskId !== null) {
                $task = WbsTask::query()
                    ->where('project_id', $project->id)
                    ->find($taskId);
                if ($task === null) {
                    throw new DomainException('Selected WBS task does not belong to this project.');
                }
            }

            $holdPayment = array_key_exists('hold_payment', $data) && $data['hold_payment'] !== null
                ? filter_var($data['hold_payment'], FILTER_VALIDATE_BOOLEAN)
                : in_array($severity, ['MAJOR', 'CRITICAL'], true);

            $last = NonConformanceReport::query()
                ->where('project_id', $project->id)
                ->orderByDesc('ncr_number')
                ->first();
            $number = ($last?->ncr_number ?? 0) + 1;
            $reference = sprintf('NCR-%s-%03d', $project->code, $number);

            $ncr = NonConformanceReport::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'ncr_number' => $number,
                'reference_no' => $reference,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $category,
                'severity' => $severity,
                'status' => 'OPEN',
                'hold_payment' => $holdPayment,
                'location_zone' => $data['location_zone'] ?? null,
                'created_by' => $issuer->id,
            ]);

            $this->audit->record($project->id, $issuer, 'ncr.issued', $ncr, [
                'reference_no' => $reference,
                'severity' => $severity,
                'hold_payment' => $holdPayment,
                'wbs_task_id' => $taskId,
            ]);

            return $ncr;
        });
    }

    /**
     * Contractor / Site submits CAPA (Root Cause + Corrective Action + Preventive Action).
     *
     * @param  array{root_cause?: string|null, corrective_action: string, preventive_action?: string|null}  $data
     */
    public function submitRectification(NonConformanceReport $ncr, User $actor, array $data): NonConformanceReport
    {
        return DB::transaction(function () use ($ncr, $actor, $data): NonConformanceReport {
            if (! $this->access->allows($actor, $ncr->project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            if (! in_array($ncr->status, ['OPEN', 'REJECTED_RECTIFICATION'], true)) {
                throw new DomainException("NCR cannot accept rectification in status {$ncr->status}.");
            }

            $ncr->forceFill([
                'status' => 'RECTIFICATION_SUBMITTED',
                'root_cause' => $data['root_cause'] ?? null,
                'corrective_action' => $data['corrective_action'],
                'preventive_action' => $data['preventive_action'] ?? null,
                'rectification_submitted_by' => $actor->id,
                'rectification_submitted_at' => now('UTC'),
            ])->save();

            $this->audit->record($ncr->project_id, $actor, 'ncr.rectification_submitted', $ncr, [
                'status' => 'RECTIFICATION_SUBMITTED',
            ]);

            return $ncr;
        });
    }

    /**
     * QC Inspector / Project Manager / Executive verifies the rectification and closes the NCR,
     * releasing any IPC payment hold on the linked WBS task.
     */
    public function verifyAndClose(NonConformanceReport $ncr, User $inspector, ?string $notes = null): NonConformanceReport
    {
        return DB::transaction(function () use ($ncr, $inspector, $notes): NonConformanceReport {
            $this->requireInspectorRole($inspector, $ncr->project);

            if ($ncr->status !== 'RECTIFICATION_SUBMITTED') {
                throw new DomainException("NCR must be in RECTIFICATION_SUBMITTED status before closing; current status is {$ncr->status}.");
            }

            if ($ncr->rectification_submitted_by !== null && (int) $ncr->rectification_submitted_by === (int) $inspector->id) {
                throw new DomainException('Maker-checker compliance violation: the user who submitted the rectification cannot verify and close it.');
            }

            $ncr->forceFill([
                'status' => 'CLOSED',
                'verified_by' => $inspector->id,
                'verified_at' => now('UTC'),
                'verification_notes' => $notes ?: 'Rectified work inspected on site and verified compliant with project specifications.',
            ])->save();

            $this->audit->record($ncr->project_id, $inspector, 'ncr.closed', $ncr, [
                'status' => 'CLOSED',
                'verified_by' => $inspector->name,
            ]);

            return $ncr;
        });
    }

    public function rejectRectification(NonConformanceReport $ncr, User $inspector, string $reason): NonConformanceReport
    {
        return DB::transaction(function () use ($ncr, $inspector, $reason): NonConformanceReport {
            $this->requireInspectorRole($inspector, $ncr->project);

            if ($ncr->status !== 'RECTIFICATION_SUBMITTED') {
                throw new DomainException("Only RECTIFICATION_SUBMITTED NCRs can have their rectification rejected; current status is {$ncr->status}.");
            }

            $ncr->forceFill([
                'status' => 'REJECTED_RECTIFICATION',
                'verified_by' => $inspector->id,
                'verified_at' => now('UTC'),
                'verification_notes' => 'Rectification rejected: '.$reason,
            ])->save();

            $this->audit->record($ncr->project_id, $inspector, 'ncr.rectification_rejected', $ncr, [
                'status' => 'REJECTED_RECTIFICATION',
                'reason' => $reason,
            ]);

            return $ncr;
        });
    }

    private function requireInspectorRole(User $user, Project $project): void
    {
        if (! $this->access->allows($user, $project, ['PROJECT_MANAGER', 'EXECUTIVE', 'SAFETY_OFFICER'])) {
            throw new AuthorizationException('User role is insufficient to verify or close an NCR.');
        }
    }
}
