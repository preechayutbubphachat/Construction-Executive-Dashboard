<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConstraintManagement
{
    public function __construct(
        private readonly AuditRecorder $audit,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function create(Project $project, User $creator, array $attributes): ProjectConstraint
    {
        return DB::transaction(function () use ($project, $creator, $attributes): ProjectConstraint {
            $taskId = $attributes['task_id'] ?? null;
            if ($taskId !== null) {
                $task = WbsTask::query()->find($taskId);
                if ($task === null || (string) $task->project_id !== (string) $project->id) {
                    throw new DomainException('Referenced WBS task must exist in the same project.');
                }
            }

            $constraint = ProjectConstraint::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'task_id' => $taskId,
                'type' => $attributes['type'] ?? 'OTHER',
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'severity' => $attributes['severity'] ?? 'MEDIUM',
                'status' => $attributes['status'] ?? 'OPEN',
                'target_resolution_date' => $attributes['target_resolution_date'] ?? null,
                'assigned_to' => $attributes['assigned_to'] ?? null,
                'created_by' => $creator->id,
            ]);

            $this->audit->record($project->id, $creator, 'constraint.created', $constraint, [
                'type' => $constraint->type,
                'severity' => $constraint->severity,
                'status' => $constraint->status,
                'task_id' => $constraint->task_id,
            ]);

            return $constraint->refresh();
        });
    }

    public function resolve(ProjectConstraint $constraint, User $resolver, string $notes): ProjectConstraint
    {
        return DB::transaction(function () use ($constraint, $resolver, $notes): ProjectConstraint {
            $constraint->update([
                'status' => 'RESOLVED',
                'resolved_at' => now('UTC'),
                'resolution_notes' => $notes,
            ]);

            $this->audit->record((string) $constraint->project_id, $resolver, 'constraint.resolved', $constraint, [
                'resolution_notes' => $notes,
                'resolved_at' => $constraint->resolved_at?->toIso8601String(),
            ]);

            return $constraint->refresh();
        });
    }

    public function updateStatus(ProjectConstraint $constraint, User $actor, string $newStatus): ProjectConstraint
    {
        return DB::transaction(function () use ($constraint, $actor, $newStatus): ProjectConstraint {
            $oldStatus = $constraint->status;
            $constraint->update(['status' => $newStatus]);

            $this->audit->record((string) $constraint->project_id, $actor, 'constraint.status_updated', $constraint, [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return $constraint->refresh();
        });
    }
}
