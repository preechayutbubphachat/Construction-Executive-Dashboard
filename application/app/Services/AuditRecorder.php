<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditRecorder
{
    public function record(
        string $projectId,
        User $actor,
        string $action,
        Model $resource,
        array $summary = [],
    ): AuditEvent {
        $resourceProjectId = $resource instanceof Project
            ? (string) $resource->getKey()
            : (string) $resource->getAttribute('project_id');

        if ($resourceProjectId !== $projectId) {
            throw new DomainException('Audit resources must belong to the recorded project.');
        }

        if (! $actor->projects()->whereKey($projectId)->exists()) {
            throw new DomainException('Audit actors must be members of the recorded project.');
        }

        return AuditEvent::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $projectId,
            'actor_id' => $actor->getKey(),
            'action' => $action,
            'auditable_type' => $resource->getMorphClass(),
            'auditable_id' => (string) $resource->getKey(),
            'summary' => $summary,
            'occurred_at' => now('UTC'),
        ]);
    }
}
