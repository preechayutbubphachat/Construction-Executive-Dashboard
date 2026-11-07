<?php

namespace App\Livewire;

use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\WbsTask;
use App\Services\ConstraintManagement;
use App\Services\SitePhotoWatermarkService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class SiteSupervisorQuickCaptureView extends Component
{
    use WithFileUploads;

    #[Locked]
    public string $projectId;

    public string $activeTab = 'TASKS'; // 'TASKS', 'LOG_BLOCKER', 'CONSTRAINTS', 'PHOTOS', 'SUMMARY'

    public string $constraintType = 'MATERIAL';

    public string $constraintTitle = '';

    public string $constraintDescription = '';

    public string $constraintSeverity = 'MEDIUM';

    public ?string $constraintTaskId = null;

    public ?string $resolvingConstraintId = null;

    public string $resolutionNote = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    // Field Photo & Inspection state
    public $photoFile;

    public ?string $photoTaskId = null;

    public ?string $photoConstraintId = null;

    public string $photoCaption = '';

    public string $photoCategory = 'PROGRESS'; // 'PROGRESS', 'DEFECT', 'QUALITY_CHECK', 'SAFETY'

    public ?float $photoLat = null;

    public ?float $photoLng = null;

    public string $galleryFilter = 'ALL';

    public ?string $previewEvidenceId = null;

    public function mount(Project $project): void
    {
        Gate::authorize('view', $project);
        $this->projectId = (string) $project->getKey();
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['TASKS', 'LOG_BLOCKER', 'CONSTRAINTS', 'PHOTOS', 'SUMMARY'], true)) {
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

    public function setCoordinates(?float $lat, ?float $lng): void
    {
        $this->photoLat = $lat;
        $this->photoLng = $lng;
    }

    public function uploadSitePhoto(SitePhotoWatermarkService $service): void
    {
        $this->validate([
            'photoFile' => ['required', 'image', 'max:12288'],
            'photoCategory' => ['required', 'string', 'in:PROGRESS,DEFECT,QUALITY_CHECK,SAFETY'],
            'photoCaption' => ['nullable', 'string', 'max:255'],
            'photoTaskId' => ['nullable', 'string'],
            'photoConstraintId' => ['nullable', 'string'],
            'photoLat' => ['nullable', 'numeric'],
            'photoLng' => ['nullable', 'numeric'],
        ]);

        $project = Project::query()->findOrFail($this->projectId);
        Gate::authorize('view', $project);

        try {
            $service->storeSitePhoto($project, Auth::user(), $this->photoFile, [
                'task_id' => $this->photoTaskId,
                'constraint_id' => $this->photoConstraintId,
                'caption' => $this->photoCaption,
                'category' => $this->photoCategory,
                'gps_latitude' => $this->photoLat,
                'gps_longitude' => $this->photoLng,
                'captured_at' => now(),
            ]);

            $this->photoFile = null;
            $this->photoCaption = '';
            $this->photoTaskId = null;
            $this->photoConstraintId = null;
            $this->photoCategory = 'PROGRESS';

            $this->successMessage = 'Field photo uploaded and watermarked with telemetry.';
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
            $this->successMessage = null;
        }
    }

    public function openPhotoModal(string $evidenceId): void
    {
        $this->previewEvidenceId = $evidenceId;
    }

    public function closePhotoModal(): void
    {
        $this->previewEvidenceId = null;
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

        // Photos & inspection evidence
        $photosQuery = EvidenceDocument::query()
            ->where('project_id', $project->id)
            ->with(['task', 'constraint', 'uploader'])
            ->latest();

        $allPhotos = (clone $photosQuery)->get();

        if ($this->galleryFilter === 'GPS_ONLY') {
            $photosQuery->whereNotNull('gps_latitude');
        } elseif (in_array($this->galleryFilter, ['PROGRESS', 'DEFECT', 'QUALITY_CHECK', 'SAFETY'], true)) {
            $photosQuery->where('metadata->category', $this->galleryFilter);
        } elseif ($this->galleryFilter !== 'ALL') {
            $photosQuery->where('wbs_task_id', $this->galleryFilter);
        }

        $filteredPhotos = $photosQuery->get();

        $selectedPhoto = $this->previewEvidenceId
            ? $allPhotos->firstWhere('id', $this->previewEvidenceId)
            : null;

        $photoStats = [
            'total' => $allPhotos->count(),
            'withGps' => $allPhotos->whereNotNull('gps_latitude')->count(),
            'progress' => $allPhotos->filter(fn ($p) => ($p->metadata['category'] ?? '') === 'PROGRESS')->count(),
            'defects' => $allPhotos->filter(fn ($p) => ($p->metadata['category'] ?? '') === 'DEFECT')->count(),
        ];

        return view('livewire.site-supervisor-quick-capture-view', [
            'project' => $project,
            'tasks' => $tasks,
            'tasksWithSoundness' => $tasksWithSoundness,
            'constraints' => $constraints,
            'soundCount' => $soundCount,
            'blockedCount' => $blockedCount,
            'photos' => $filteredPhotos,
            'selectedPhoto' => $selectedPhoto,
            'photoStats' => $photoStats,
        ]);
    }
}
