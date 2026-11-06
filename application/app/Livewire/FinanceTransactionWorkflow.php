<?php

namespace App\Livewire;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Services\AuditRecorder;
use App\Services\CedFinanceV1ReadModel;
use App\Services\FinanceWorkflow;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class FinanceTransactionWorkflow extends Component
{
    #[Locked]
    public string $projectId;

    public string $direction = '';

    public string $description = '';

    public string $amount = '';

    public ?string $selectedTransactionId = null;

    public ?string $successMessage = null;

    /** @var array{rule_version: string, calculated_at: string, contributing_source_record_scope: array{project_id: string, status: string, included_record_ids: list<string>}, income: string, expense: string, net: string, explanation: string} */
    public array $postedTotals = [
        'rule_version' => 'CED_FINANCE_V1',
        'calculated_at' => '',
        'contributing_source_record_scope' => [
            'project_id' => '',
            'status' => FinanceStatus::Posted->value,
            'included_record_ids' => [],
        ],
        'income' => '0.00',
        'expense' => '0.00',
        'net' => '0.00',
        'explanation' => '',
    ];

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function createDraft(): void
    {
        $this->resetErrorBag();
        $this->successMessage = null;
        $this->validate([
            'direction' => ['required', 'string', 'in:INCOME,EXPENSE'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $project = $this->authorizedProject();
        $transaction = DB::transaction(function () use ($project): FinanceTransaction {
            $transaction = FinanceTransaction::query()->create([
                'id' => (string) Str::uuid(),
                'project_id' => $project->getKey(),
                'direction' => $this->direction,
                'description' => $this->description,
                'amount' => $this->amount,
                'status' => FinanceStatus::Draft,
                'created_by' => auth()->id(),
            ]);

            app(AuditRecorder::class)->record($project->getKey(), auth()->user(), 'finance.created', $transaction, [
                'to' => FinanceStatus::Draft->value,
                'direction' => $transaction->direction,
                'amount' => (string) $transaction->amount,
            ]);

            return $transaction;
        });

        $this->selectedTransactionId = $transaction->getKey();
        $this->successMessage = 'Draft transaction created.';
        $this->reset(['direction', 'description', 'amount']);
    }

    public function submit(string $transactionId): void
    {
        $this->runTransition($transactionId, 'submitted', fn (FinanceWorkflow $workflow, FinanceTransaction $transaction) => $workflow->submit($transaction, auth()->user())
        );
    }

    public function approve(string $transactionId): void
    {
        $this->runTransition($transactionId, 'approved', fn (FinanceWorkflow $workflow, FinanceTransaction $transaction) => $workflow->approve($transaction, auth()->user())
        );
    }

    public function post(string $transactionId): void
    {
        $this->runTransition($transactionId, 'posted', fn (FinanceWorkflow $workflow, FinanceTransaction $transaction) => $workflow->post($transaction, auth()->user())
        );
    }

    public function render(): View
    {
        $project = $this->authorizedProject();
        $transactions = $project->financeTransactions()->with(['creator', 'approver'])->orderBy('created_at')->orderBy('id')->get();
        $this->postedTotals = app(CedFinanceV1ReadModel::class)->forProject($project);

        return view('livewire.finance-transaction-workflow', [
            'project' => $project,
            'transactions' => $transactions,
            'postedTotals' => $this->postedTotals,
        ]);
    }

    private function runTransition(string $transactionId, string $label, callable $transition): void
    {
        $this->resetErrorBag();
        $this->successMessage = null;
        $transaction = $this->transactionForProject($transactionId);

        try {
            $transition(app(FinanceWorkflow::class), $transaction);
            $this->successMessage = "Transaction {$label}.";
        } catch (AuthorizationException $exception) {
            throw $exception;
        } catch (DomainException $exception) {
            $this->addError('workflow', $exception->getMessage());
            $this->refreshSelected($transactionId);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('workflow', 'The finance transition could not be completed.');
            $this->refreshSelected($transactionId);
        }
    }

    private function authorizedProject(): Project
    {
        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        return $project;
    }

    private function transactionForProject(string $transactionId): FinanceTransaction
    {
        $project = $this->authorizedProject();
        $transaction = $project->financeTransactions()->whereKey($transactionId)->first();
        if ($transaction === null) {
            throw ValidationException::withMessages(['workflow' => 'The transaction is not in the selected project.']);
        }

        return $transaction;
    }

    private function refreshSelected(string $transactionId): void
    {
        $this->selectedTransactionId = $this->transactionForProject($transactionId)->getKey();
    }
}
