<?php

namespace App\Livewire;

use App\Models\Project;
use App\Services\SiteSpatial3DProjectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SiteSpatial3DView extends Component
{
    #[Locked]
    public string $projectId;

    public ?string $selectedElementId = null;

    /** @var array<string, mixed>|null */
    public ?array $selectedElement = null;

    public string $filterMode = 'ALL'; // 'ALL', 'BLOCKED_ONLY', 'PROGRESS_HEATMAP'

    public float $explodedGap = 0.0; // 0.0 to 8.0 meters

    public string $cameraPreset = 'ISOMETRIC'; // 'ISOMETRIC', 'TOP_DOWN', 'FRONT'

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function selectElement(?string $elementId, SiteSpatial3DProjectionService $service): void
    {
        if ($elementId === null || trim($elementId) === '') {
            $this->closeInspector();

            return;
        }

        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        $data = $service->project($project);
        $element = collect($data['elements'])->firstWhere('id', $elementId);

        if ($element !== null) {
            $this->selectedElementId = $elementId;
            $this->selectedElement = $element;
        } else {
            $this->closeInspector();
        }
    }

    public function closeInspector(): void
    {
        $this->selectedElementId = null;
        $this->selectedElement = null;
    }

    public function setFilterMode(string $mode): void
    {
        if (in_array($mode, ['ALL', 'BLOCKED_ONLY', 'PROGRESS_HEATMAP'], true)) {
            $this->filterMode = $mode;
            $this->dispatch('spatial3d-filter-changed', mode: $mode);
        }
    }

    public function setExplodedGap(float $gap): void
    {
        $this->explodedGap = max(0.0, min(10.0, $gap));
        $this->dispatch('spatial3d-exploded-changed', gap: $this->explodedGap);
    }

    public function setCameraPreset(string $preset): void
    {
        if (in_array($preset, ['ISOMETRIC', 'TOP_DOWN', 'FRONT'], true)) {
            $this->cameraPreset = $preset;
            $this->dispatch('spatial3d-camera-changed', preset: $preset);
        }
    }

    public function render(SiteSpatial3DProjectionService $service): View
    {
        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        $spatialData = $service->project($project);

        return view('livewire.site-spatial-3d-view', [
            'project' => $project,
            'spatialData' => $spatialData,
            'summary' => $spatialData['summary'],
        ]);
    }
}
