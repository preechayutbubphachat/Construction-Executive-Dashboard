<?php

namespace App\Services;

use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\WbsTask;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class CedExecutiveSourceLinkResolver
{
    public function resolve(Project $project, array $sourceRef): ?array
    {
        if ((string) ($sourceRef['project_id'] ?? '') !== (string) $project->id
            || ! Project::query()->whereKey($project->id)->exists()
            || ! auth()->check()
            || Gate::forUser(auth()->user())->denies('view', $project)) {
            return null;
        }

        $type = $sourceRef['source_type'] ?? null;
        $id = $sourceRef['source_id'] ?? null;
        if ($type === 'wbs_task') {
            if (! Route::has('projects.wbs.tasks.show')) {
                return null;
            }
            $task = WbsTask::query()->whereKey($id)->where('project_id', $project->id)->first();
            if ($task === null) {
                return null;
            }

            return [
                'source_type' => 'wbs_task',
                'source_id' => (string) $task->id,
                'label' => (string) ($sourceRef['label'] ?? "WBS task {$task->id}"),
                'route_name' => 'projects.wbs.tasks.show',
                'route_params' => ['project' => $project, 'task' => $task],
                'url' => route('projects.wbs.tasks.show', ['project' => $project, 'task' => $task]),
            ];
        }
        if ($type !== 'finance_transaction' || ! Route::has('projects.finance.transactions.show')) {
            return null;
        }
        $transaction = FinanceTransaction::query()->whereKey($id)->where('project_id', $project->id)->where('status', 'POSTED')->first();
        if ($transaction === null) {
            return null;
        }

        return [
            'source_type' => 'finance_transaction',
            'source_id' => (string) $transaction->id,
            'label' => (string) ($sourceRef['label'] ?? "Finance transaction {$transaction->id}"),
            'route_name' => 'projects.finance.transactions.show',
            'route_params' => ['project' => $project, 'transaction' => $transaction],
            'url' => route('projects.finance.transactions.show', ['project' => $project, 'transaction' => $transaction]),
        ];
    }
}
