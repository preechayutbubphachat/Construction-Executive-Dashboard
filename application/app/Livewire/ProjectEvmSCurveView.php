<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\EarnedValueManagementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProjectEvmSCurveView extends Component
{
    #[Locked]
    public string $projectId;

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function render(EarnedValueManagementService $service): View
    {
        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        $evmData = $service->calculate($project);

        return view('livewire.project-evm-s-curve-view', [
            'project' => $project,
            'evmData' => $evmData,
            'metrics' => $evmData['metrics'],
            'variances' => $evmData['variances'],
            'indices' => $evmData['indices'],
            'forecasts' => $evmData['forecasts'],
            'series' => $evmData['s_curve']['series'],
        ]);
    }
}
