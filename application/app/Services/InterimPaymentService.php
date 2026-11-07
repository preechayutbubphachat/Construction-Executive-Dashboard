<?php

namespace App\Services;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\InterimPaymentCertificate;
use App\Models\IpcItem;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InterimPaymentService
{
    public const VERSION = 'CED_IPC_WORKFLOW_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * Create and submit a new Interim Payment Application from contractor progress claim.
     *
     * @param  array{
     *     contractor_name: string,
     *     contractor_invoice_no?: string|null,
     *     period_start: string,
     *     period_end: string,
     *     retention_rate_percent?: float|null,
     *     advance_recovery_deduction?: float|null,
     *     other_deductions?: float|null,
     *     vat_rate_percent?: float|null,
     *     items: list<array{
     *         wbs_task_id?: string|null,
     *         description: string,
     *         contract_amount: float,
     *         previous_percent?: float|null,
     *         current_percent: float,
     *         notes?: string|null
     *     }>
     * }  $data
     */
    public function createApplication(Project $project, User $creator, array $data): InterimPaymentCertificate
    {
        return DB::transaction(function () use ($project, $creator, $data): InterimPaymentCertificate {
            $this->requireMember($creator, $project);

            $lastIpc = InterimPaymentCertificate::query()
                ->where('project_id', $project->id)
                ->orderByDesc('certificate_number')
                ->first();

            $certNumber = ($lastIpc?->certificate_number ?? 0) + 1;
            $refNo = sprintf('IPC-%s-%03d', $project->code, $certNumber);

            // Calculate cumulative previous certified work
            $previousCertified = (float) ($lastIpc?->gross_work_amount ?? 0.00);

            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                // Auto-seed from leaf WBS tasks if items list empty
                $leafTasks = WbsTask::query()
                    ->where('project_id', $project->id)
                    ->where('is_leaf', true)
                    ->orderBy('wbs_code')
                    ->get();

                foreach ($leafTasks as $task) {
                    $taskWeight = (float) ($task->weight ?? 1.0);
                    $taskBudget = round(((float) $project->budget_amount) * ($taskWeight / 100), 2);
                    $itemsData[] = [
                        'wbs_task_id' => $task->id,
                        'description' => "[{$task->wbs_code}] {$task->name}",
                        'contract_amount' => max(1000.00, $taskBudget),
                        'previous_percent' => 0.0,
                        'current_percent' => $task->status === 'COMPLETED' ? 100.0 : ($task->status === 'IN_PROGRESS' ? 50.0 : 0.0),
                        'notes' => null,
                    ];
                }
            }

            $currentWorkAmount = 0.00;
            $processedItems = [];

            foreach ($itemsData as $item) {
                $taskId = $item['wbs_task_id'] ?? null;
                $task = $taskId ? WbsTask::query()->find($taskId) : null;

                $contractAmt = (float) ($item['contract_amount'] ?? ($task?->planned_cost ?? 0.00));
                if ($contractAmt <= 0.00 && $task !== null) {
                    $taskWeight = (float) ($task->weight ?? 1.0);
                    $contractAmt = round(((float) $project->budget_amount) * ($taskWeight / 100), 2);
                }

                $prevPct = min(100.00, max(0.00, (float) ($item['previous_percent'] ?? 0.00)));
                $currPct = min(100.00, max(0.00, (float) ($item['current_percent'] ?? ($item['claimed_progress_percent'] ?? 0.00))));
                $periodPct = max(0.00, round($currPct - $prevPct, 2));

                if (isset($item['claimed_amount']) && (float) $item['claimed_amount'] > 0) {
                    $claimedAmt = round((float) $item['claimed_amount'], 2);
                } else {
                    $claimedAmt = round($contractAmt * ($periodPct / 100), 2);
                }

                $certifiedAmt = (float) ($item['certified_amount'] ?? $claimedAmt);
                $notes = $item['notes'] ?? ($item['remarks'] ?? null);
                $desc = $item['description'] ?? ($task ? "[{$task->wbs_code}] {$task->name}" : 'Contract Item');

                // Skip unentered empty rows from large form unless it's the only item
                if ($claimedAmt <= 0.00 && count($itemsData) > 1 && ! isset($item['contract_amount'])) {
                    continue;
                }

                $currentWorkAmount += $certifiedAmt;

                $processedItems[] = [
                    'id' => (string) Str::ulid(),
                    'wbs_task_id' => $taskId,
                    'description' => $desc,
                    'contract_amount' => $contractAmt,
                    'previous_percent' => $prevPct,
                    'current_percent' => $currPct,
                    'this_period_percent' => $periodPct,
                    'claimed_amount' => $claimedAmt,
                    'certified_amount' => $certifiedAmt,
                    'notes' => $notes,
                ];
            }

            // Fallback if current work amount specified at top level
            if ($currentWorkAmount <= 0.00 && isset($data['current_work_amount']) && (float) $data['current_work_amount'] > 0) {
                $currentWorkAmount = (float) $data['current_work_amount'];
            }

            $currentWorkAmount = round($currentWorkAmount, 2);
            $grossWorkAmount = round($previousCertified + $currentWorkAmount, 2);

            $retentionRate = (float) ($data['retention_rate_percent'] ?? 5.00);
            $retentionDeduction = round($currentWorkAmount * ($retentionRate / 100), 2);

            $advanceRecovery = round((float) ($data['advance_recovery_deduction'] ?? 0.00), 2);
            $otherDeductions = round((float) ($data['other_deductions'] ?? 0.00), 2);

            $netBeforeVat = max(0.00, round($currentWorkAmount - $retentionDeduction - $advanceRecovery - $otherDeductions, 2));

            $vatRate = (float) ($data['vat_rate_percent'] ?? 7.00);
            $vatAmount = round($netBeforeVat * ($vatRate / 100), 2);

            $netPayable = round($netBeforeVat + $vatAmount, 2);

            $ipc = InterimPaymentCertificate::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'certificate_number' => $certNumber,
                'reference_no' => $refNo,
                'contractor_name' => $data['contractor_name'] ?? 'Main Construction Contractor Co., Ltd.',
                'contractor_invoice_no' => $data['contractor_invoice_no'] ?? null,
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'status' => 'SUBMITTED',
                'gross_work_amount' => $grossWorkAmount,
                'previous_certified_amount' => $previousCertified,
                'current_work_amount' => $currentWorkAmount,
                'retention_rate_percent' => $retentionRate,
                'retention_deduction' => $retentionDeduction,
                'advance_recovery_deduction' => $advanceRecovery,
                'other_deductions' => $otherDeductions,
                'net_amount_before_vat' => $netBeforeVat,
                'vat_rate_percent' => $vatRate,
                'vat_amount' => $vatAmount,
                'net_payable_amount' => $netPayable,
                'created_by' => $creator->id,
            ]);

            foreach ($processedItems as $itemRecord) {
                $itemRecord['ipc_id'] = $ipc->id;
                IpcItem::query()->create($itemRecord);
            }

            $this->audit->record($project->id, $creator, 'ipc.submitted', $ipc, [
                'certificate_number' => $certNumber,
                'reference_no' => $refNo,
                'gross_work_amount' => $grossWorkAmount,
                'net_payable_amount' => $netPayable,
            ]);

            return $ipc;
        });
    }

    /**
     * Level 1 Approval: Site Engineer / Supervisor verifies physical work on site against WBS & photos.
     */
    public function verifyBySiteEngineer(InterimPaymentCertificate $ipc, User $actor, ?string $notes = null): InterimPaymentCertificate
    {
        return DB::transaction(function () use ($ipc, $actor, $notes): InterimPaymentCertificate {
            $this->requireRole($actor, $ipc->project, ['SITE_SUPERVISOR', 'PROJECT_MANAGER', 'EXECUTIVE']);

            if ($ipc->status !== 'SUBMITTED') {
                throw new DomainException("IPC cannot be site verified from status {$ipc->status}. Must be SUBMITTED.");
            }

            $ipc->forceFill([
                'status' => 'VERIFIED_SITE',
                'site_engineer_id' => $actor->id,
                'site_verified_at' => now('UTC'),
                'site_notes' => $notes ?: 'Physical site inspection confirmed work completed according to specifications.',
            ])->save();

            $this->audit->record($ipc->project_id, $actor, 'ipc.site_verified', $ipc, [
                'status' => 'VERIFIED_SITE',
                'verified_by' => $actor->name,
            ]);

            return $ipc;
        });
    }

    /**
     * Level 2 Approval: Quantity Surveyor (QS) certifies work quantities, valuations, and deductions.
     */
    public function verifyByQuantitySurveyor(InterimPaymentCertificate $ipc, User $actor, ?string $notes = null): InterimPaymentCertificate
    {
        return DB::transaction(function () use ($ipc, $actor, $notes): InterimPaymentCertificate {
            $this->requireRole($actor, $ipc->project, ['PROJECT_MANAGER', 'EXECUTIVE']);

            if ($ipc->status !== 'VERIFIED_SITE') {
                throw new DomainException("IPC cannot be QS verified from status {$ipc->status}. Must be VERIFIED_SITE.");
            }

            $ipc->forceFill([
                'status' => 'VERIFIED_QS',
                'quantity_surveyor_id' => $actor->id,
                'qs_verified_at' => now('UTC'),
                'qs_notes' => $notes ?: 'Quantities measured and validated conforming to contract Bill of Quantities.',
            ])->save();

            $this->audit->record($ipc->project_id, $actor, 'ipc.qs_verified', $ipc, [
                'status' => 'VERIFIED_QS',
                'verified_by' => $actor->name,
            ]);

            return $ipc;
        });
    }

    /**
     * Level 3 Approval: Project Director approves IPC and automatically posts payment into Finance Ledger.
     */
    public function approveByDirector(InterimPaymentCertificate $ipc, User $actor, ?string $notes = null): InterimPaymentCertificate
    {
        return DB::transaction(function () use ($ipc, $actor, $notes): InterimPaymentCertificate {
            $this->requireRole($actor, $ipc->project, ['EXECUTIVE']);

            if ($ipc->status !== 'VERIFIED_QS') {
                throw new DomainException("IPC cannot be director approved from status {$ipc->status}. Must be VERIFIED_QS.");
            }

            if ((int) $ipc->created_by === (int) $actor->id) {
                throw new DomainException('Maker-checker compliance violation: The creator of the IPC application cannot approve and post their own payment certificate.');
            }

            // Create and post FinanceTransaction
            $transactionId = (string) Str::ulid();
            $transaction = FinanceTransaction::query()->create([
                'id' => $transactionId,
                'project_id' => $ipc->project_id,
                'direction' => 'EXPENSE',
                'description' => sprintf(
                    'Certified IPC #%d (%s) - %s',
                    $ipc->certificate_number,
                    $ipc->reference_no,
                    $ipc->contractor_name
                ),
                'amount' => $ipc->net_payable_amount,
                'status' => FinanceStatus::Posted,
                'created_by' => $ipc->created_by,
                'approved_by' => $actor->id,
                'submitted_at' => $ipc->created_at,
                'approved_at' => now('UTC'),
                'posted_at' => now('UTC'),
            ]);

            $ipc->forceFill([
                'status' => 'POSTED',
                'project_director_id' => $actor->id,
                'director_approved_at' => now('UTC'),
                'director_notes' => $notes ?: 'Final executive payment certification authorized for disbursement.',
                'finance_transaction_id' => $transaction->id,
            ])->save();

            $this->audit->record($ipc->project_id, $actor, 'ipc.approved_and_posted', $ipc, [
                'status' => 'POSTED',
                'approved_by' => $actor->name,
                'finance_transaction_id' => $transaction->id,
                'net_payable_amount' => $ipc->net_payable_amount,
            ]);

            return $ipc;
        });
    }

    /**
     * Reject IPC application back to draft with reason.
     */
    public function reject(InterimPaymentCertificate $ipc, User $actor, string $reason): InterimPaymentCertificate
    {
        return DB::transaction(function () use ($ipc, $actor, $reason): InterimPaymentCertificate {
            $this->requireRole($actor, $ipc->project, ['SITE_SUPERVISOR', 'PROJECT_MANAGER', 'EXECUTIVE']);

            if (in_array($ipc->status, ['POSTED', 'APPROVED'], true)) {
                throw new DomainException('Cannot reject an IPC that has already been approved and posted.');
            }

            $ipc->forceFill([
                'status' => 'REJECTED',
                'director_notes' => 'Rejected: '.$reason,
            ])->save();

            $this->audit->record($ipc->project_id, $actor, 'ipc.rejected', $ipc, [
                'reason' => $reason,
                'rejected_by' => $actor->name,
            ]);

            return $ipc;
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
            throw new AuthorizationException('User role is insufficient for this IPC certification level.');
        }
    }
}
