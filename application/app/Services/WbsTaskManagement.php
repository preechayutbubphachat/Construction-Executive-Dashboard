<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WbsTaskManagement
{
    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    public function create(Project $project, User $actor, array $attributes): WbsTask
    {
        return DB::transaction(function () use ($project, $actor, $attributes): WbsTask {
            $lockedProject = $this->lockAndAuthorizeProject($project, $actor);
            $task = WbsTask::query()->create([
                ...Arr::only($attributes, [
                    'parent_id',
                    'wbs_code',
                    'name',
                    'baseline_start_date',
                    'baseline_finish_date',
                    'weight',
                    'status',
                    'is_baseline',
                ]),
                'id' => (string) Str::ulid(),
                'project_id' => $lockedProject->id,
                'status' => $attributes['status'] ?? 'NOT_STARTED',
                'is_baseline' => $attributes['is_baseline'] ?? true,
                'is_leaf' => true,
            ]);

            $this->synchronizeParent($lockedProject->id, $task->parent_id);
            $this->audit->record($lockedProject->id, $actor, 'wbs_task.created', $task, [
                'parent_id' => $task->parent_id,
                'wbs_code' => $task->wbs_code,
                'status' => $task->status,
            ]);

            return $task->refresh();
        });
    }

    public function update(Project $project, WbsTask $task, User $actor, array $attributes): WbsTask
    {
        return DB::transaction(function () use ($project, $task, $actor, $attributes): WbsTask {
            $lockedProject = $this->lockAndAuthorizeProject($project, $actor);
            $lockedTask = WbsTask::query()
                ->where('project_id', $lockedProject->id)
                ->lockForUpdate()
                ->findOrFail($task->getKey());
            $oldParentId = $lockedTask->parent_id;
            $lockedTask->fill(Arr::only($attributes, [
                'parent_id',
                'name',
                'baseline_start_date',
                'baseline_finish_date',
                'weight',
                'status',
                'is_baseline',
            ]));
            $changes = $this->changes($lockedTask);

            if ($changes !== []) {
                $lockedTask->save();
                $this->synchronizeParent($lockedProject->id, $oldParentId);
                $this->synchronizeParent($lockedProject->id, $lockedTask->parent_id);
                $this->audit->record($lockedProject->id, $actor, 'wbs_task.updated', $lockedTask, $changes);
            }

            return $lockedTask->refresh();
        });
    }

    private function lockAndAuthorizeProject(Project $project, User $actor): Project
    {
        $locked = Project::query()->lockForUpdate()->findOrFail($project->getKey());

        if (! $this->access->allows($actor, $locked, ['EXECUTIVE'])) {
            throw new AuthorizationException('The project role is not authorized to manage its WBS/tasks.');
        }

        return $locked;
    }

    private function synchronizeParent(string $projectId, ?string $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        $parent = WbsTask::query()
            ->where('project_id', $projectId)
            ->lockForUpdate()
            ->findOrFail($parentId);
        $hasChildren = WbsTask::query()->where('parent_id', $parent->id)->exists();
        $parent->forceFill([
            'is_leaf' => ! $hasChildren,
            'weight' => $hasChildren ? null : $parent->weight,
        ]);

        if ($parent->isDirty()) {
            $parent->save();
        }
    }

    private function changes(WbsTask $task): array
    {
        return collect($task->getDirty())
            ->mapWithKeys(fn (mixed $value, string $attribute): array => [
                $attribute => [
                    'from' => $task->getOriginal($attribute),
                    'to' => $value,
                ],
            ])
            ->all();
    }
}
