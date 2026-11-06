<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProjectManagement
{
    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    public function updateMetadata(Project $project, User $actor, array $attributes): Project
    {
        return DB::transaction(function () use ($project, $actor, $attributes): Project {
            $locked = $this->lockAndAuthorize($project, $actor);
            $locked->fill(Arr::only($attributes, [
                'name',
                'baseline_start_date',
                'baseline_finish_date',
                'budget_amount',
            ]));
            $changes = $this->changes($locked);

            if ($changes !== []) {
                $locked->save();
                $this->audit->record($locked->id, $actor, 'project.metadata_updated', $locked, $changes);
            }

            return $locked->refresh();
        });
    }

    public function transition(Project $project, User $actor, string $status): Project
    {
        return DB::transaction(function () use ($project, $actor, $status): Project {
            $locked = $this->lockAndAuthorize($project, $actor);
            $from = $locked->status;
            $locked->forceFill([
                'status' => $status,
                'archived_at' => $status === 'ARCHIVED' ? now('UTC') : null,
            ])->save();
            $this->audit->record($locked->id, $actor, $status === 'ARCHIVED' ? 'project.archived' : 'project.lifecycle_changed', $locked, [
                'from' => $from,
                'to' => $status,
            ]);

            return $locked->refresh();
        });
    }

    private function lockAndAuthorize(Project $project, User $actor): Project
    {
        $locked = Project::query()->lockForUpdate()->findOrFail($project->getKey());

        if (! $this->access->allows($actor, $locked, ['EXECUTIVE'])) {
            throw new AuthorizationException('The project role is not authorized to manage this project.');
        }

        return $locked;
    }

    private function changes(Project $project): array
    {
        return collect($project->getDirty())
            ->mapWithKeys(fn (mixed $value, string $attribute): array => [
                $attribute => [
                    'from' => $project->getOriginal($attribute),
                    'to' => $value,
                ],
            ])
            ->all();
    }
}
