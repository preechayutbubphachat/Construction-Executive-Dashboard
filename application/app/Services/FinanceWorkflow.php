<?php

namespace App\Services;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class FinanceWorkflow
{
    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    public function submit(FinanceTransaction $transaction, User $actor): FinanceTransaction
    {
        return DB::transaction(function () use ($transaction, $actor): FinanceTransaction {
            $locked = $this->lock($transaction);
            $this->requireMember($actor, $locked);

            if ((int) $locked->created_by !== (int) $actor->getKey()) {
                throw new AuthorizationException('Only the transaction maker may submit it.');
            }

            $this->requireStatus($locked, FinanceStatus::Draft);
            $locked->forceFill([
                'status' => FinanceStatus::Submitted,
                'submitted_at' => now('UTC'),
            ])->save();
            $this->audit->record($locked->project_id, $actor, 'finance.submitted', $locked, [
                'from' => FinanceStatus::Draft->value,
                'to' => FinanceStatus::Submitted->value,
            ]);

            return $locked;
        });
    }

    public function approve(FinanceTransaction $transaction, User $actor): FinanceTransaction
    {
        return DB::transaction(function () use ($transaction, $actor): FinanceTransaction {
            $locked = $this->lock($transaction);
            $this->requireRole($actor, $locked, ['FINANCE_APPROVER', 'EXECUTIVE']);
            $this->requireStatus($locked, FinanceStatus::Submitted);

            if ((int) $locked->created_by === (int) $actor->getKey()) {
                throw new DomainException('Maker-checker violation: created_by must differ from approved_by.');
            }

            $locked->forceFill([
                'status' => FinanceStatus::Approved,
                'approved_by' => $actor->getKey(),
                'approved_at' => now('UTC'),
            ])->save();
            $this->audit->record($locked->project_id, $actor, 'finance.approved', $locked, [
                'from' => FinanceStatus::Submitted->value,
                'to' => FinanceStatus::Approved->value,
                'maker_checker' => 'created_by != approved_by',
            ]);

            return $locked;
        });
    }

    public function post(FinanceTransaction $transaction, User $actor): FinanceTransaction
    {
        return DB::transaction(function () use ($transaction, $actor): FinanceTransaction {
            $locked = $this->lock($transaction);
            $this->requireRole($actor, $locked, ['FINANCE_APPROVER', 'EXECUTIVE']);
            $this->requireStatus($locked, FinanceStatus::Approved);

            if ($locked->approved_by === null || (int) $locked->created_by === (int) $locked->approved_by) {
                throw new DomainException('A maker-checker approval is required before posting.');
            }

            $locked->forceFill([
                'status' => FinanceStatus::Posted,
                'posted_at' => now('UTC'),
            ])->save();
            $this->audit->record($locked->project_id, $actor, 'finance.posted', $locked, [
                'from' => FinanceStatus::Approved->value,
                'to' => FinanceStatus::Posted->value,
            ]);

            return $locked;
        });
    }

    private function lock(FinanceTransaction $transaction): FinanceTransaction
    {
        return FinanceTransaction::query()->lockForUpdate()->findOrFail($transaction->getKey());
    }

    private function requireMember(User $actor, FinanceTransaction $transaction): void
    {
        if (! $this->access->allows($actor, $transaction->project)) {
            throw new AuthorizationException('Project membership is required.');
        }
    }

    private function requireRole(User $actor, FinanceTransaction $transaction, array $roles): void
    {
        if (! $this->access->allows($actor, $transaction->project, $roles)) {
            throw new AuthorizationException('The project role is not authorized for this transition.');
        }
    }

    private function requireStatus(FinanceTransaction $transaction, FinanceStatus $expected): void
    {
        if ($transaction->status !== $expected) {
            throw new DomainException("Expected {$expected->value}; found {$transaction->status->value}.");
        }
    }
}
