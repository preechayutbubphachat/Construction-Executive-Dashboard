<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;

class ProjectAccess
{
    public function roleFor(User $user, Project $project): ?string
    {
        $member = $project->members()->whereKey($user->getKey())->first();

        return $member?->pivot?->role;
    }

    public function allows(User $user, Project $project, array $roles = []): bool
    {
        $role = $this->roleFor($user, $project);

        return $role !== null && ($roles === [] || in_array($role, $roles, true));
    }
}
