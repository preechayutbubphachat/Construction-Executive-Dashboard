<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\WbsTask;
use App\Services\ConstraintManagement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SiteSupervisorQuickCaptureView extends Component
{
    #[Locked]
    public string $projectId;

    public string $activeTab = 'TASKS'; // 'TASKS', 'LOG_BLOCKER', 'CONSTRAINTS', 'SUMMARY'

    public string $constraintType = 'MATERIAL';

    public string $constraintTitle = '';

    public string $constraintDescription = '';

    public string $constraintSeverity = 'MEDIUM';

    public ?string $constraintTaskId = null;

    public ?string $resolvingConstraintId = null;

    public string $resolutionNote = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['TASKS', 'LOG_BLOCKER', 'CONSTRAINTS', 'SUMMARY'], true)) {
            $this->activeTab = $tab;
            $this->resetFeedback();
        }
    }

    public function quickLogConstraint(ConstraintManagement $service): void
    {
        $this->validate([
            'constraintTitle' => ['required', 'string', 'min:3', 'max:255'],
            'constraintType' => ['required', 'string'],
            'constraintSeverity' => ['required', 'string'],
            'constraintTaskId' => ['nullable', 'string'],
        ]);

        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        try {
            $service->create($project, Auth::user(), [
                'type' => $this->constraintType,
                'title' => $this->constraintTitle,
                'description' => $this->constraintDescription ?: null,
                'severity' => $this->constraintSeverity,
                'task_id' => $this->constraintTaskId ?: null,
            ]);

            $this->constraintTitle = '';
            $this->constraintDescription = '';
            $this->constraintTaskId = null;
            $this->constraintType = 'MATERIAL';
            $this->constraintSeverity = 'MEDIUM';

            $this->successMessage = 'Site constraint logged successfully.';
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function quickResolveConstraint(ConstraintManagement $service): void
    {
        $this->validate([
            'resolvingConstraintId' => ['required', 'string'],
            'resolutionNote' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $constraint = ProjectConstraint::query()
            ->where('project_id', $this->projectId)
            ->findOrFail($this->resolvingConstraintId);

        try {
            $service->resolve($constraint, Auth::user(), $this->resolutionNote);

            $this->resolvingConstraintId = null;
            $this->resolutionNote = '';
            $this->successMessage = 'Constraint cleared and marked resolved.';
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function quickTransitionTask(string $taskId, string $newStatus): void
    {
        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        $task = WbsTask::query()
            ->where('project_id', $project->id)
            ->findOrFail($taskId);

        if (! in_array($newStatus, WbsTask::STATUSES, true)) {
            $this->errorMessage = 'Invalid task status requested.';

            return;
        }

        try {
            DB::transaction(function () use ($task, $newStatus): void {
                $task->update(['status' => $newStatus]);
            });

            $this->successMessage = "Task status updated to {$newStatus}.";
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function resetFeedback(): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;
    }

    public function render(): View
    {
        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        // Fetch leaf tasks
        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_leaf', true)
            ->orderBy('baseline_start_date')
            ->get();

        // Fetch open constraints
        $constraints = ProjectConstraint::query()
            ->where('project_id', $project->id)
            ->where('status', 'OPEN')
            ->orderByDesc('created_at')
            ->get();

        $openConstraintTaskIds = $constraints->pluck('task_id')->filter()->unique()->toArray();

        // Compute soundness per task
        $tasksWithSoundness = $tasks->map(function (WbsTask $task) use ($openConstraintTaskIds) {
            $hasBlocker = in_array((string) $task->id, $openConstraintTaskIds, true);

            return [
                'task' => $task,
                'is_sound' => ! $hasBlocker,
                'status' => $task->status,
            ];
        });

        $soundCount = $tasksWithSoundness->where('is_sound', true)->count();
        $blockedCount = $tasksWithSoundness->where('is_sound', false)->count();

        return view('livewire.site-supervisor-quick-capture-view', [
            'project' => $project,
            'tasks' => $tasks,
            'tasksWithSoundness' => $tasksWithSoundness,
            'constraints' => $constraints,
            'soundCount' => $soundCount,
            'blockedCount' => $blockedCount,
        ]);
    }
}
