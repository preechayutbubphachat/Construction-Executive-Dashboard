<?php

namespace App\Services;

use App\Models\EngineeringRfi;
use App\Models\EngineeringSubmittal;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EngineeringDocumentControlService
{
    public const VERSION = 'CED_ENG_DOC_CONTROL_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * 7A: Issue a new Request for Information (RFI).
     *
     * @param  array{
     *     wbs_task_id?: string|null,
     *     constraint_id?: string|null,
     *     discipline: string,
     *     subject: string,
     *     drawing_ref?: string|null,
     *     question: string,
     *     proposed_solution?: string|null,
     *     due_date: string
     * }  $data
     */
    public function createRfi(Project $project, User $requester, array $data): EngineeringRfi
    {
        return DB::transaction(function () use ($project, $requester, $data): EngineeringRfi {
            if (! $this->access->allows($requester, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $discipline = strtoupper((string) $data['discipline']);
            if (! in_array($discipline, EngineeringRfi::DISCIPLINES, true)) {
                throw new DomainException("Unsupported RFI discipline {$discipline}.");
            }

            $taskId = ! empty($data['wbs_task_id']) ? (string) $data['wbs_task_id'] : null;
            if ($taskId !== null && WbsTask::query()->where('project_id', $project->id)->find($taskId) === null) {
                throw new DomainException('Selected WBS task does not belong to this project.');
            }

            $constraintId = ! empty($data['constraint_id']) ? (string) $data['constraint_id'] : null;
            if ($constraintId !== null && ProjectConstraint::query()->where('project_id', $project->id)->find($constraintId) === null) {
                throw new DomainException('Selected constraint does not belong to this project.');
            }

            $last = EngineeringRfi::query()
                ->where('project_id', $project->id)
                ->orderByDesc('rfi_number')
                ->first();
            $number = ($last?->rfi_number ?? 0) + 1;
            $reference = sprintf('RFI-%s-%03d', $project->code, $number);

            $rfi = EngineeringRfi::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'constraint_id' => $constraintId,
                'rfi_number' => $number,
                'reference_no' => $reference,
                'discipline' => $discipline,
                'subject' => $data['subject'],
                'drawing_ref' => $data['drawing_ref'] ?? null,
                'question' => $data['question'],
                'proposed_solution' => $data['proposed_solution'] ?? null,
                'due_date' => $data['due_date'],
                'status' => 'OPEN',
                'created_by' => $requester->id,
            ]);

            $this->audit->record($project->id, $requester, 'engineering.rfi_created', $rfi, [
                'reference_no' => $reference,
                'discipline' => $discipline,
                'due_date' => $data['due_date'],
            ]);

            return $rfi;
        });
    }

    /**
     * Consultant / Project Manager / Executive provides official technical response and closes the RFI,
     * automatically resolving any linked DESIGN_RFI constraints.
     *
     * @return array{rfi: EngineeringRfi, resolved_constraints_count: int}
     */
    public function answerAndCloseRfi(EngineeringRfi $rfi, User $responder, string $officialResponse): array
    {
        return DB::transaction(function () use ($rfi, $responder, $officialResponse): array {
            $this->requireEngineeringAuthority($responder, $rfi->project);

            if ($rfi->status !== 'OPEN') {
                throw new DomainException("RFI is already {$rfi->status}.");
            }

            if ((int) $rfi->created_by === (int) $responder->id) {
                throw new DomainException('Maker-checker compliance violation: the requester of an RFI cannot provide the official sign-off response.');
            }

            $rfi->forceFill([
                'status' => 'CLOSED',
                'official_response' => $officialResponse,
                'answered_by' => $responder->id,
                'answered_at' => now('UTC'),
            ])->save();

            $resolvedCount = $this->resolveLinkedDesignRfiConstraints($rfi, $responder);

            $this->audit->record($rfi->project_id, $responder, 'engineering.rfi_closed', $rfi, [
                'reference_no' => $rfi->reference_no,
                'answered_by' => $responder->name,
                'resolved_constraints_count' => $resolvedCount,
            ]);

            return [
                'rfi' => $rfi,
                'resolved_constraints_count' => $resolvedCount,
            ];
        });
    }

    /**
     * 7B: Create a Shop Drawing / Material Sample Submittal.
     *
     * @param  array{
     *     wbs_task_id?: string|null,
     *     submittal_type: string,
     *     discipline: string,
     *     title: string,
     *     spec_section?: string|null,
     *     revision?: string|null
     * }  $data
     */
    public function createSubmittal(Project $project, User $submitter, array $data): EngineeringSubmittal
    {
        return DB::transaction(function () use ($project, $submitter, $data): EngineeringSubmittal {
            if (! $this->access->allows($submitter, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $type = strtoupper((string) $data['submittal_type']);
            if (! in_array($type, EngineeringSubmittal::TYPES, true)) {
                throw new DomainException("Unsupported submittal type {$type}.");
            }

            $discipline = strtoupper((string) $data['discipline']);
            if (! in_array($discipline, EngineeringSubmittal::DISCIPLINES, true)) {
                throw new DomainException("Unsupported submittal discipline {$discipline}.");
            }

            $taskId = ! empty($data['wbs_task_id']) ? (string) $data['wbs_task_id'] : null;
            if ($taskId !== null && WbsTask::query()->where('project_id', $project->id)->find($taskId) === null) {
                throw new DomainException('Selected WBS task does not belong to this project.');
            }

            $last = EngineeringSubmittal::query()
                ->where('project_id', $project->id)
                ->orderByDesc('submittal_number')
                ->first();
            $number = ($last?->submittal_number ?? 0) + 1;
            $reference = sprintf('SUB-%s-%03d', $project->code, $number);

            $submittal = EngineeringSubmittal::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'submittal_number' => $number,
                'reference_no' => $reference,
                'submittal_type' => $type,
                'discipline' => $discipline,
                'title' => $data['title'],
                'spec_section' => $data['spec_section'] ?? null,
                'revision' => $data['revision'] ?? 'Rev.0',
                'status' => 'PENDING_REVIEW',
                'created_by' => $submitter->id,
            ]);

            $this->audit->record($project->id, $submitter, 'engineering.submittal_created', $submittal, [
                'reference_no' => $reference,
                'submittal_type' => $type,
                'revision' => $submittal->revision,
            ]);

            return $submittal;
        });
    }

    public function reviewSubmittal(
        EngineeringSubmittal $submittal,
        User $reviewer,
        string $decision,
        ?string $comments = null,
    ): EngineeringSubmittal {
        return DB::transaction(function () use ($submittal, $reviewer, $decision, $comments): EngineeringSubmittal {
            $this->requireEngineeringAuthority($reviewer, $submittal->project);

            if ($submittal->status !== 'PENDING_REVIEW') {
                throw new DomainException("Submittal must be in PENDING_REVIEW status; current status is {$submittal->status}.");
            }

            if ((int) $submittal->created_by === (int) $reviewer->id) {
                throw new DomainException('Maker-checker compliance violation: the submitter cannot review and approve their own submittal.');
            }

            $normalized = strtoupper($decision);
            if (! in_array($normalized, EngineeringSubmittal::REVIEW_DECISIONS, true)) {
                throw new DomainException("Invalid review decision {$normalized}.");
            }

            $submittal->forceFill([
                'status' => $normalized,
                'review_comments' => $comments ?: "Reviewed with status {$normalized}.",
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now('UTC'),
            ])->save();

            $this->audit->record($submittal->project_id, $reviewer, 'engineering.submittal_reviewed', $submittal, [
                'reference_no' => $submittal->reference_no,
                'revision' => $submittal->revision,
                'decision' => $normalized,
            ]);

            return $submittal;
        });
    }

    public function resubmitRevision(
        EngineeringSubmittal $submittal,
        User $actor,
        string $newRevision,
    ): EngineeringSubmittal {
        return DB::transaction(function () use ($submittal, $actor, $newRevision): EngineeringSubmittal {
            if (! $this->access->allows($actor, $submittal->project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            if (! in_array($submittal->status, ['REVISE_AND_RESUBMIT', 'REJECTED'], true)) {
                throw new DomainException('Only submittals marked REVISE_AND_RESUBMIT or REJECTED can be resubmitted.');
            }

            $submittal->forceFill([
                'revision' => trim($newRevision),
                'status' => 'PENDING_REVIEW',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ])->save();

            $this->audit->record($submittal->project_id, $actor, 'engineering.submittal_resubmitted', $submittal, [
                'reference_no' => $submittal->reference_no,
                'revision' => $submittal->revision,
            ]);

            return $submittal;
        });
    }

    private function resolveLinkedDesignRfiConstraints(EngineeringRfi $rfi, User $actor): int
    {
        if ($rfi->constraint_id === null && $rfi->wbs_task_id === null) {
            return 0;
        }

        $constraints = ProjectConstraint::query()
            ->where('project_id', $rfi->project_id)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->where(function ($q) use ($rfi): void {
                if ($rfi->constraint_id !== null) {
                    $q->orWhere('id', $rfi->constraint_id);
                }
                if ($rfi->wbs_task_id !== null) {
                    $q->orWhere(function ($sub) use ($rfi): void {
                        $sub->where('task_id', $rfi->wbs_task_id)
                            ->where('type', ProjectConstraint::TYPE_DESIGN_RFI);
                    });
                }
            })
            ->get();

        foreach ($constraints as $constraint) {
            $constraint->forceFill([
                'status' => ProjectConstraint::STATUS_RESOLVED,
                'resolved_at' => now('UTC'),
                'resolution_notes' => sprintf(
                    'Auto-resolved by official response to %s (%s).',
                    $rfi->reference_no,
                    $rfi->subject
                ),
            ])->save();

            $this->audit->record($rfi->project_id, $actor, 'constraint.auto_resolved_by_rfi', $constraint, [
                'rfi_id' => $rfi->id,
                'reference_no' => $rfi->reference_no,
            ]);
        }

        return $constraints->count();
    }

    private function requireEngineeringAuthority(User $user, Project $project): void
    {
        if (! $this->access->allows($user, $project, ['PROJECT_MANAGER', 'EXECUTIVE'])) {
            throw new AuthorizationException('Only Project Manager / Consultant Engineer or Executive can sign off on RFIs or Submittals.');
        }
    }
}
