<?php

namespace App\Services;

use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgressRecorder
{
    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    public function record(Project $project, WbsTask $task, User $actor, array $attributes): ProgressEntry
    {
        return DB::transaction(function () use ($project, $task, $actor, $attributes): ProgressEntry {
            $lockedProject = Project::query()->lockForUpdate()->findOrFail($project->getKey());
            if (! $this->access->allows($actor, $lockedProject, ['EXECUTIVE'])) {
                throw new AuthorizationException('The project role is not authorized to record progress.');
            }

            $lockedTask = WbsTask::query()
                ->where('project_id', $lockedProject->id)
                ->lockForUpdate()
                ->findOrFail($task->getKey());

            if ($lockedTask->children()->exists()) {
                throw new DomainException('Progress can only be recorded against leaf tasks.');
            }

            if ($lockedTask->status === 'NOT_STARTED' && (float) $attributes['progress_percent'] > 0) {
                throw new DomainException('A task must be IN_PROGRESS before positive progress is recorded.');
            }

            $entry = ProgressEntry::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $lockedProject->id,
                'task_id' => $lockedTask->id,
                'progress_date' => $attributes['progress_date'],
                'progress_percent' => $attributes['progress_percent'],
                'recorded_by' => $actor->getKey(),
                'recorded_at' => now('UTC'),
                'note' => $attributes['note'] ?? null,
            ]);
            $this->audit->record($lockedProject->id, $actor, 'progress.recorded', $entry, [
                'task_id' => $lockedTask->id,
                'progress_date' => $entry->progress_date->toDateString(),
                'progress_percent' => $entry->progress_percent,
            ]);

            return $entry;
        });
    }
}
