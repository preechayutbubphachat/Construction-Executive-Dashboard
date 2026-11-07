<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\PortfolioRiskHeatmapService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PortfolioRiskHeatmapView extends Component
{
    public string $filter = 'ALL';

    public string $sortField = 'risk_score';

    public string $sortDirection = 'desc';

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['ALL', 'CRITICAL', 'HIGH', 'MEDIUM', 'LOW'], true)) {
            $this->filter = $filter;
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function render(PortfolioRiskHeatmapService $service): View
    {
        $user = Auth::user();

        $projects = Project::query()
            ->whereHas('members', fn ($query) => $query->whereKey($user->getKey()))
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $heatmapData = $service->forProjects($projects);

        $projectList = collect($heatmapData['projects']);

        if ($this->filter !== 'ALL') {
            $projectList = $projectList->where('risk_level', $this->filter);
        }

        $sortField = $this->sortField;
        $isDesc = $this->sortDirection === 'desc';

        $sortedProjects = $projectList->sortBy(function ($item) use ($sortField) {
            return $item[$sortField] ?? 0;
        }, SORT_REGULAR, $isDesc)->values()->all();

        return view('livewire.portfolio-risk-heatmap-view', [
            'summary' => $heatmapData['summary'],
            'evaluatedAt' => $heatmapData['evaluated_at'],
            'projects' => $sortedProjects,
            'allProjects' => $heatmapData['projects'],
        ]);
    }
}
