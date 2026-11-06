<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ExecutiveProjectHealth extends Component
{
    #[Locked]
    public string $projectId;

    #[Locked]
    public string $projectName;

    #[Locked]
    public array $actual;

    #[Locked]
    public array $planned;

    #[Locked]
    public array $health;

    public function mount(
        string $projectId,
        string $projectName,
        array $actual,
        array $planned,
        array $health,
    ): void {
        $this->projectId = $projectId;
        $this->projectName = $projectName;
        $this->actual = $actual;
        $this->planned = $planned;
        $this->health = $health;
    }

    public function render(): View
    {
        return view('livewire.executive-project-health');
    }
}
