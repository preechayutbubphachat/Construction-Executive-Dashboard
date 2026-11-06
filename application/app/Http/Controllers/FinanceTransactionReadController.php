<?php

namespace App\Http\Controllers;

use App\Models\FinanceTransaction;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FinanceTransactionReadController extends Controller
{
    public function show(Project $project, FinanceTransaction $transaction): View
    {
        Gate::authorize('view', $project);
        abort_unless((string) $transaction->project_id === (string) $project->id, 403);

        return view('projects.finance-transaction', compact('project', 'transaction'));
    }
}
