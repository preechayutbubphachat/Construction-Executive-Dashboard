<?php

namespace App\Services;

use App\Domain\Finance\FinanceStatus;
use App\Models\Project;

class CedFinanceV1ReadModel
{
    public const VERSION = 'CED_FINANCE_V1';

    /**
     * @return array{
     *     rule_version: string,
     *     calculated_at: string,
     *     project_id: string,
     *     contributing_source_record_scope: array{
     *         project_id: string,
     *         status: string,
     *         included_record_ids: list<string>
     *     },
     *     income: string,
     *     expense: string,
     *     net: string,
     *     explanation: string
     * }
     */
    public function forProject(Project $project): array
    {
        $income = 0;
        $expense = 0;
        $includedRecordIds = [];

        foreach ($project->financeTransactions()->get() as $transaction) {
            if ((string) $transaction->project_id !== (string) $project->getKey()
                || $transaction->status !== FinanceStatus::Posted) {
                continue;
            }

            $includedRecordIds[] = (string) $transaction->getKey();
            $cents = $this->cents((string) $transaction->amount);
            if ($transaction->direction === 'INCOME') {
                $income += $cents;
            } elseif ($transaction->direction === 'EXPENSE') {
                $expense += $cents;
            }
        }

        sort($includedRecordIds, SORT_STRING);

        $incomeTotal = $this->money($income);
        $expenseTotal = $this->money($expense);
        $netTotal = $this->money($income - $expense);
        $projectId = (string) $project->getKey();

        return [
            'rule_version' => self::VERSION,
            'calculated_at' => now()->toIso8601String(),
            'project_id' => $projectId,
            'contributing_source_record_scope' => [
                'project_id' => $projectId,
                'status' => FinanceStatus::Posted->value,
                'included_record_ids' => $includedRecordIds,
            ],
            'income' => $incomeTotal,
            'expense' => $expenseTotal,
            'net' => $netTotal,
            'explanation' => "Posted records in project {$projectId} produce income {$incomeTotal}, expense {$expenseTotal}, and net {$netTotal} (income minus expense).",
        ];
    }

    private function cents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function money(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absolute, 100), $absolute % 100);
    }
}
