<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ExecutivePortfolioReadModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutivePortfolioController extends Controller
{
    public function __invoke(Request $request, ExecutivePortfolioReadModel $readModel): View
    {
        abort_unless($request->user() !== null, 403);

        $projects = Project::query()
            ->whereHas('members', fn ($query) => $query->whereKey($request->user()->getKey()))
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        return view('projects.portfolio', [
            'cards' => $readModel->forProjects($projects),
        ]);
    }
}
