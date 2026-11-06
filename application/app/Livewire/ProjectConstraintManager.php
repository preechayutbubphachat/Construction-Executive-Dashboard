<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Services\ConstraintManagement;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ProjectConstraintManager extends Component
{
    #[Locked]
    public string $projectId;

    public string $type = ProjectConstraint::TYPE_MATERIAL;

    public string $title = '';

    public string $description = '';

    public string $severity = ProjectConstraint::SEVERITY_MEDIUM;

    public ?string $task_id = null;

    public ?string $target_resolution_date = null;

    public ?int $assigned_to = null;

    public string $filter = 'ALL';

    public ?string $resolvingId = null;

    public string $resolutionNotes = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function createConstraint(ConstraintManagement $service): void
    {
        $this->resetErrorBag();
        $this->reset(['successMessage', 'errorMessage']);

        if ($this->task_id === '') {
            $this->task_id = null;
        }

        if ($this->target_resolution_date === '') {
            $this->target_resolution_date = null;
        }

        $validated = $this->validate([
            'type' => ['required', 'string', Rule::in(ProjectConstraint::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(ProjectConstraint::SEVERITIES)],
            'task_id' => ['nullable', 'string'],
            'target_resolution_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $project = $this->project();
        $user = Auth::user();

        try {
            $service->create($project, $user, $validated);
            $this->reset(['title', 'description', 'task_id', 'target_resolution_date', 'assigned_to']);
            $this->type = ProjectConstraint::TYPE_MATERIAL;
            $this->severity = ProjectConstraint::SEVERITY_MEDIUM;
            $this->successMessage = 'Constraint logged successfully.';
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function openResolveModal(string $constraintId): void
    {
        $this->resolvingId = $constraintId;
        $this->resolutionNotes = '';
        $this->reset(['successMessage', 'errorMessage']);
    }

    public function closeResolveModal(): void
    {
        $this->resolvingId = null;
        $this->resolutionNotes = '';
    }

    public function resolveConstraint(ConstraintManagement $service): void
    {
        $this->resetErrorBag();
        $this->validate([
            'resolvingId' => ['required', 'string'],
            'resolutionNotes' => ['required', 'string', 'max:1000'],
        ]);

        $constraint = ProjectConstraint::query()
            ->where('project_id', $this->projectId)
            ->where('id', $this->resolvingId)
            ->firstOrFail();

        $user = Auth::user();

        try {
            $service->resolve($constraint, $user, $this->resolutionNotes);
            $this->resolvingId = null;
            $this->resolutionNotes = '';
            $this->successMessage = 'Constraint marked as resolved.';
        } catch (DomainException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function project(): Project
    {
        return Project::query()->findOrFail($this->projectId);
    }

    public function render(): View
    {
        $project = $this->project();
        $tasks = $project->wbsTasks()->orderBy('wbs_code')->get();
        $members = $project->members()->get();

        $query = $project->constraints()->with(['task', 'creator', 'assignee'])->latest();

        if ($this->filter === 'OPEN') {
            $query->open();
        } elseif ($this->filter === 'CRITICAL') {
            $query->open()->critical();
        } elseif ($this->filter === 'RESOLVED') {
            $query->where('status', ProjectConstraint::STATUS_RESOLVED);
        }

        $constraints = $query->get();

        $stats = [
            'total' => $project->constraints()->count(),
            'open' => $project->constraints()->open()->count(),
            'critical' => $project->constraints()->open()->critical()->count(),
            'resolved' => $project->constraints()->where('status', ProjectConstraint::STATUS_RESOLVED)->count(),
        ];

        return view('livewire.project-constraint-manager', [
            'project' => $project,
            'tasks' => $tasks,
            'members' => $members,
            'constraints' => $constraints,
            'stats' => $stats,
        ]);
    }
}
