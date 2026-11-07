<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\WeeklyLookaheadService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WeeklyLookaheadView extends Component
{
    #[Locked]
    public string $projectId;

    public int $weeks = 3;

    public string $filter = 'ALL';

    public ?string $anchorDateString = null;

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
        $this->anchorDateString = now()->startOfWeek()->toDateString();
    }

    public function setWeeks(int $weeks): void
    {
        if (in_array($weeks, [3, 4, 6], true)) {
            $this->weeks = $weeks;
        }
    }

    public function setFilter(string $filter): void
    {
        if (in_array($filter, ['ALL', 'MAKE_READY', 'CONSTRAINED'], true)) {
            $this->filter = $filter;
        }
    }

    public function shiftWindow(int $weeksDelta): void
    {
        $current = $this->anchorDateString ? Carbon::parse($this->anchorDateString) : now();
        $this->anchorDateString = $current->addWeeks($weeksDelta)->startOfWeek()->toDateString();
    }

    public function resetToToday(): void
    {
        $this->anchorDateString = now()->startOfWeek()->toDateString();
    }

    public function project(): Project
    {
        return Project::query()->findOrFail($this->projectId);
    }

    public function render(WeeklyLookaheadService $service): View
    {
        $project = $this->project();
        $anchor = $this->anchorDateString ? Carbon::parse($this->anchorDateString) : now();

        $lookahead = $service->forProject($project, $this->weeks, $anchor);

        $filteredTasks = collect($lookahead['tasks']);
        if ($this->filter === 'MAKE_READY') {
            $filteredTasks = $filteredTasks->where('is_make_ready', true);
        } elseif ($this->filter === 'CONSTRAINED') {
            $filteredTasks = $filteredTasks->where('is_make_ready', false);
        }

        return view('livewire.weekly-lookahead-view', [
            'project' => $project,
            'lookahead' => $lookahead,
            'tasks' => $filteredTasks->values()->all(),
        ]);
    }
}
