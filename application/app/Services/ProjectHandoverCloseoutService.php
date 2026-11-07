<?php

namespace App\Services;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\HandoverPunchItem;
use App\Models\Project;
use App\Models\ProjectHandoverCertificate;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectHandoverCloseoutService
{
    public const CONTRACT_NAME = 'CED_HANDOVER_CLOSEOUT_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * Build the Phase 8 Handover, Punch List, Retention Release & Final Account Statement read model.
     *
     * @return array<string, mixed>
     */
    public function buildDashboard(Project $project): array
    {
        $punchItems = $project->handoverPunchItems()
            ->with(['wbsTask', 'reporter', 'rectifier', 'verifier'])
            ->orderByDesc('created_at')
            ->get();

        $certificates = $project->handoverCertificates()
            ->with(['preparer', 'certifier', 'releaser', 'financeTransaction'])
            ->orderByDesc('created_at')
            ->get();

        $postedIpcs = $project->ipcs()
            ->where('status', 'POSTED')
            ->get();

        $approvedVos = $project->variationOrders()
            ->where('status', 'APPROVED')
            ->get();

        $revisedContractSum = round((float) $project->budget_amount, 2);
        $approvedVoTotal = round((float) $approvedVos->sum('cost_impact'), 2);
        $originalContractSum = round($revisedContractSum - $approvedVoTotal, 2);

        $totalCertifiedGross = round((float) $postedIpcs->sum('current_work_amount'), 2);
        $totalIpcNetPaid = round((float) $postedIpcs->sum('net_payable_amount'), 2);
        $totalRetentionHeld = round((float) $postedIpcs->sum('retention_deduction'), 2);

        $totalRetentionReleased = round(
            (float) $certificates->where('status', 'RETENTION_RELEASED')->sum('retention_release_amount'),
            2
        );
        $availableRetentionBalance = max(0.0, round($totalRetentionHeld - $totalRetentionReleased, 2));

        $openNcrCount = $project->ncrs()->where('status', '!=', 'CLOSED')->count();
        $openPunchCount = $punchItems->where('status', '!=', 'VERIFIED_CLOSED')->count();
        $criticalPunchCount = $punchItems
            ->where('status', '!=', 'VERIFIED_CLOSED')
            ->where('severity', 'CRITICAL_HANDOVER_BLOCKER')
            ->count();

        $activeWbsCount = $project->wbsTasks()
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
            ->count();

        $isReadyForToc = $openNcrCount === 0 && $openPunchCount === 0;
        $isReadyForDlc = $isReadyForToc && $activeWbsCount === 0;

        return [
            'contract' => self::CONTRACT_NAME,
            'punch_items' => $punchItems,
            'certificates' => $certificates,
            'final_account' => [
                'original_contract_sum' => $originalContractSum,
                'approved_vo_total' => $approvedVoTotal,
                'revised_contract_sum' => $revisedContractSum,
                'total_certified_gross' => $totalCertifiedGross,
                'total_ipc_net_paid' => $totalIpcNetPaid,
                'total_retention_held' => $totalRetentionHeld,
                'total_retention_released' => $totalRetentionReleased,
                'available_retention_balance' => $availableRetentionBalance,
                'unbilled_contract_balance' => max(0.0, round($revisedContractSum - $totalCertifiedGross, 2)),
            ],
            'readiness' => [
                'open_ncr_count' => $openNcrCount,
                'open_punch_count' => $openPunchCount,
                'critical_punch_count' => $criticalPunchCount,
                'verified_punch_count' => $punchItems->where('status', 'VERIFIED_CLOSED')->count(),
                'total_punch_count' => $punchItems->count(),
                'active_wbs_count' => $activeWbsCount,
                'is_ready_for_toc' => $isReadyForToc,
                'is_ready_for_dlc' => $isReadyForDlc,
            ],
        ];
    }

    /**
     * 8A-1: Log a Pre-Handover Snag / Punch List Item.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPunchItem(Project $project, User $reporter, array $data): HandoverPunchItem
    {
        return DB::transaction(function () use ($project, $reporter, $data): HandoverPunchItem {
            $this->requireMember($reporter, $project);

            $discipline = strtoupper(trim((string) ($data['discipline'] ?? 'ARCHITECTURAL')));
            if (! in_array($discipline, HandoverPunchItem::DISCIPLINES, true)) {
                throw new DomainException("Unsupported punch list discipline: {$discipline}");
            }

            $severity = strtoupper(trim((string) ($data['severity'] ?? 'MINOR')));
            if (! in_array($severity, HandoverPunchItem::SEVERITIES, true)) {
                throw new DomainException("Unsupported punch list severity: {$severity}");
            }

            $seq = $project->handoverPunchItems()->count() + 1;
            $punchCode = sprintf('PL-%03d', $seq);

            $punchItem = HandoverPunchItem::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => ! empty($data['wbs_task_id']) ? (string) $data['wbs_task_id'] : null,
                'punch_code' => $punchCode,
                'location_zone' => trim((string) $data['location_zone']),
                'discipline' => $discipline,
                'severity' => $severity,
                'description' => trim((string) $data['description']),
                'status' => 'OPEN',
                'reported_by' => $reporter->id,
            ]);

            $this->audit->record($project->id, $reporter, 'handover.punch.created', $punchItem, [
                'punch_code' => $punchCode,
                'location_zone' => $punchItem->location_zone,
                'severity' => $severity,
            ]);

            return $punchItem;
        });
    }

    /**
     * 8A-2: Contractor / Site Engineer submits rectification for a Punch List item.
     */
    public function rectifyPunchItem(HandoverPunchItem $punchItem, User $actor, string $rectificationNotes): HandoverPunchItem
    {
        return DB::transaction(function () use ($punchItem, $actor, $rectificationNotes): HandoverPunchItem {
            $project = $punchItem->project;
            $this->requireMember($actor, $project);

            if ($punchItem->status === 'VERIFIED_CLOSED') {
                throw new DomainException("Punch item {$punchItem->punch_code} is already VERIFIED_CLOSED.");
            }

            $punchItem->forceFill([
                'status' => 'RECTIFIED_PENDING_INSPECTION',
                'rectification_notes' => trim($rectificationNotes),
                'rectified_by' => $actor->id,
                'rectified_at' => CarbonImmutable::now('UTC'),
            ])->save();

            $this->audit->record($project->id, $actor, 'handover.punch.rectified', $punchItem, [
                'punch_code' => $punchItem->punch_code,
                'rectified_by' => $actor->name,
            ]);

            return $punchItem;
        });
    }

    /**
     * 8A-3: Inspector / PM verifies and closes the Punch List item (Maker-Checker enforced).
     */
    public function verifyPunchItemClosed(HandoverPunchItem $punchItem, User $verifier, ?string $notes = null): HandoverPunchItem
    {
        return DB::transaction(function () use ($punchItem, $verifier, $notes): HandoverPunchItem {
            $project = $punchItem->project;
            $this->requireRole($verifier, $project, ['PROJECT_MANAGER', 'EXECUTIVE']);

            if ($punchItem->status === 'VERIFIED_CLOSED') {
                throw new DomainException("Punch item {$punchItem->punch_code} is already VERIFIED_CLOSED.");
            }

            $makerId = $punchItem->rectified_by ?? $punchItem->reported_by;
            if ((int) $makerId === (int) $verifier->id) {
                throw new DomainException('Maker-checker violation: The inspector verifying punch closure cannot be the same person who rectified or reported it.');
            }

            $combinedNotes = $punchItem->rectification_notes;
            if ($notes !== null && trim($notes) !== '') {
                $combinedNotes = trim(($combinedNotes ? $combinedNotes.' | ' : '').'Verified: '.trim($notes));
            }

            $punchItem->forceFill([
                'status' => 'VERIFIED_CLOSED',
                'rectification_notes' => $combinedNotes ?: 'Inspected and verified closed for handover.',
                'verified_by' => $verifier->id,
                'verified_at' => CarbonImmutable::now('UTC'),
            ])->save();

            $this->audit->record($project->id, $verifier, 'handover.punch.verified_closed', $punchItem, [
                'punch_code' => $punchItem->punch_code,
                'verified_by' => $verifier->name,
            ]);

            return $punchItem;
        });
    }

    /**
     * 8B-1: Prepare and Issue Official Handover Certificate (TOC or DLC).
     * Enforces zero open NCRs and zero unclosed Punch List items before issuance.
     *
     * @param  array<string, mixed>  $data
     */
    public function issueCertificate(Project $project, User $preparer, array $data): ProjectHandoverCertificate
    {
        return DB::transaction(function () use ($project, $preparer, $data): ProjectHandoverCertificate {
            $this->requireRole($preparer, $project, ['SITE_SUPERVISOR', 'PROJECT_MANAGER', 'EXECUTIVE']);

            $certType = strtoupper(trim((string) ($data['certificate_type'] ?? 'TAKING_OVER_CERTIFICATE')));
            if (! in_array($certType, ProjectHandoverCertificate::CERTIFICATE_TYPES, true)) {
                throw new DomainException("Unsupported handover certificate type: {$certType}");
            }

            // Gate 1: No open NCRs allowed at handover
            $openNcrs = $project->ncrs()->where('status', '!=', 'CLOSED')->count();
            if ($openNcrs > 0) {
                throw new DomainException("Handover blocked: Project has {$openNcrs} open Non-Conformance Report(s) (NCR) that must be closed before issuing {$certType}.");
            }

            // Gate 2: No open Punch List items allowed at handover
            $openPunchItems = $project->handoverPunchItems()->where('status', '!=', 'VERIFIED_CLOSED')->count();
            if ($openPunchItems > 0) {
                throw new DomainException("Handover blocked: Project has {$openPunchItems} unclosed Punch List item(s) that must be verified closed before issuing {$certType}.");
            }

            // Gate 3: For DLC (Final Completion), all WBS tasks must be COMPLETED or CANCELLED
            if ($certType === 'DEFECTS_LIABILITY_CERTIFICATE') {
                $activeTasks = $project->wbsTasks()->whereNotIn('status', ['COMPLETED', 'CANCELLED'])->count();
                if ($activeTasks > 0) {
                    throw new DomainException("Defects Liability Certificate blocked: Project still has {$activeTasks} active WBS task(s).");
                }
            }

            $dashboard = $this->buildDashboard($project);
            $finalAccount = $dashboard['final_account'];

            $requestedRelease = round((float) ($data['retention_release_amount'] ?? 0), 2);
            $availableRetention = (float) $finalAccount['available_retention_balance'];

            if ($requestedRelease < 0) {
                throw new DomainException('Retention release amount cannot be negative.');
            }

            if ($requestedRelease > $availableRetention) {
                throw new DomainException(sprintf(
                    'Requested retention release (%.2f) exceeds available held retention balance (%.2f).',
                    $requestedRelease,
                    $availableRetention
                ));
            }

            $dlpMonths = max(0, (int) ($data['dlp_months'] ?? 12));
            $handoverDate = CarbonImmutable::parse((string) ($data['handover_date'] ?? now('UTC')->toDateString()));
            $dlpExpiryDate = $handoverDate->addMonths($dlpMonths);

            $prefix = $certType === 'DEFECTS_LIABILITY_CERTIFICATE' ? 'DLC' : 'TOC';
            $seq = $project->handoverCertificates()->count() + 1;
            $certNumber = sprintf('%s-%03d', $prefix, $seq);

            $certificate = ProjectHandoverCertificate::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'certificate_number' => $certNumber,
                'certificate_type' => $certType,
                'handover_date' => $handoverDate->toDateString(),
                'dlp_months' => $dlpMonths,
                'dlp_expiry_date' => $dlpExpiryDate->toDateString(),
                'final_contract_sum' => $finalAccount['revised_contract_sum'],
                'total_approved_vo_amount' => $finalAccount['approved_vo_total'],
                'total_certified_ipc_amount' => $finalAccount['total_certified_gross'],
                'retention_held_total' => $finalAccount['total_retention_held'],
                'retention_release_amount' => $requestedRelease,
                'status' => 'ISSUED',
                'scope_remarks' => ! empty($data['scope_remarks']) ? trim((string) $data['scope_remarks']) : null,
                'prepared_by' => $preparer->id,
                'certified_by' => $preparer->id,
                'certified_at' => CarbonImmutable::now('UTC'),
            ]);

            $this->audit->record($project->id, $preparer, 'handover.certificate.issued', $certificate, [
                'certificate_number' => $certNumber,
                'certificate_type' => $certType,
                'retention_release_amount' => $requestedRelease,
                'dlp_expiry_date' => $dlpExpiryDate->toDateString(),
            ]);

            return $certificate;
        });
    }

    /**
     * 8B-2: Executive Approves Retention Money Release & Final Account Closeout (Maker-Checker enforced).
     * Posts retention disbursement to Finance Ledger and optionally transitions Project to COMPLETED.
     */
    public function releaseRetentionAndCloseout(
        ProjectHandoverCertificate $certificate,
        User $approver,
        bool $markProjectCompleted = false
    ): ProjectHandoverCertificate {
        return DB::transaction(function () use ($certificate, $approver, $markProjectCompleted): ProjectHandoverCertificate {
            $project = $certificate->project;
            $this->requireRole($approver, $project, ['PROJECT_MANAGER', 'EXECUTIVE']);

            if ($certificate->status === 'RETENTION_RELEASED') {
                throw new DomainException("Retention money for certificate {$certificate->certificate_number} has already been released.");
            }

            if ((int) $certificate->prepared_by === (int) $approver->id) {
                throw new DomainException('Maker-checker violation: The executive authorizing retention release cannot be the same person who prepared the handover certificate.');
            }

            $transaction = null;
            if ((float) $certificate->retention_release_amount > 0) {
                $transaction = FinanceTransaction::query()->create([
                    'id' => (string) Str::ulid(),
                    'project_id' => $project->id,
                    'direction' => 'EXPENSE',
                    'description' => sprintf(
                        'Retention Money Release — %s (%s)',
                        $certificate->certificate_number,
                        $certificate->certificate_type
                    ),
                    'amount' => $certificate->retention_release_amount,
                    'status' => FinanceStatus::Posted,
                    'created_by' => $certificate->prepared_by,
                    'approved_by' => $approver->id,
                    'submitted_at' => $certificate->created_at,
                    'approved_at' => CarbonImmutable::now('UTC'),
                    'posted_at' => CarbonImmutable::now('UTC'),
                ]);
            }

            $certificate->forceFill([
                'status' => 'RETENTION_RELEASED',
                'certified_by' => $approver->id,
                'released_by' => $approver->id,
                'retention_released_at' => CarbonImmutable::now('UTC'),
                'finance_transaction_id' => $transaction?->id,
            ])->save();

            // Transition project to COMPLETED if requested or if DLC is released and all WBS tasks are done
            $allWbsDone = ! $project->wbsTasks()
                ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                ->exists();

            if (($markProjectCompleted || $certificate->certificate_type === 'DEFECTS_LIABILITY_CERTIFICATE')
                && $allWbsDone
                && in_array($project->status, ['ACTIVE', 'ON_HOLD'], true)) {
                $project->status = 'COMPLETED';
                $project->save();
            }

            $this->audit->record($project->id, $approver, 'handover.retention.released', $certificate, [
                'certificate_number' => $certificate->certificate_number,
                'retention_release_amount' => (float) $certificate->retention_release_amount,
                'finance_transaction_id' => $transaction?->id,
                'project_status' => $project->status,
            ]);

            return $certificate;
        });
    }

    private function requireMember(User $user, Project $project): void
    {
        if (! $this->access->allows($user, $project)) {
            throw new AuthorizationException('User is not an authorized project member.');
        }
    }

    /**
     * @param  list<string>  $roles
     */
    private function requireRole(User $user, Project $project, array $roles): void
    {
        if (! $this->access->allows($user, $project, $roles)) {
            throw new AuthorizationException('User role is insufficient for this handover closeout operation.');
        }
    }
}
