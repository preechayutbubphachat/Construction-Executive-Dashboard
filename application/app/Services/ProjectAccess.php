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

    public function isExecutive(User $user, Project $project): bool
    {
        return $this->allows($user, $project, ['EXECUTIVE']);
    }

    public function canManageOperations(User $user, Project $project): bool
    {
        return $this->allows($user, $project, ['EXECUTIVE', 'PROJECT_MANAGER']);
    }

    public function canCaptureSite(User $user, Project $project): bool
    {
        return $this->allows($user, $project, ['EXECUTIVE', 'PROJECT_MANAGER', 'SITE_SUPERVISOR', 'SAFETY_OFFICER']);
    }

    public function canApproveFinance(User $user, Project $project): bool
    {
        return $this->isExecutive($user, $project);
    }

    public function canArchive(User $user, Project $project): bool
    {
        return $this->isExecutive($user, $project);
    }
}
