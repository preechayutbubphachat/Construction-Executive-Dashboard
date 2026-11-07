<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\EarlyWarningAlertEngine;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProjectAlertsCenter extends Component
{
    #[Locked]
    public string $projectId;

    public string $filter = 'ALL';

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['ALL', 'CRITICAL', 'SCHEDULE', 'CONSTRAINT', 'FINANCE'], true)) {
            $this->filter = $filter;
        }
    }

    public function project(): Project
    {
        return Project::query()->findOrFail($this->projectId);
    }

    public function render(EarlyWarningAlertEngine $engine): View
    {
        $project = $this->project();
        $alertData = $engine->forProject($project);

        $filteredAlerts = collect($alertData['alerts']);

        if ($this->filter === 'CRITICAL') {
            $filteredAlerts = $filteredAlerts->where('severity', 'CRITICAL');
        } elseif ($this->filter === 'SCHEDULE') {
            $filteredAlerts = $filteredAlerts->filter(fn ($a) => in_array($a['category'], ['SCHEDULE_OVERDUE', 'LOOKAHEAD_BLOCKED'], true));
        } elseif ($this->filter === 'CONSTRAINT') {
            $filteredAlerts = $filteredAlerts->where('category', 'CRITICAL_CONSTRAINT_STALE');
        } elseif ($this->filter === 'FINANCE') {
            $filteredAlerts = $filteredAlerts->where('category', 'BUDGET_BURNOUT');
        }

        return view('livewire.project-alerts-center', [
            'project' => $project,
            'summary' => $alertData['summary'],
            'evaluatedAt' => $alertData['evaluated_at'],
            'alerts' => $filteredAlerts->values()->all(),
        ]);
    }
}
