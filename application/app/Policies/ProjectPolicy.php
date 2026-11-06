<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectAccess;

class ProjectPolicy
{
    public function __construct(private readonly ProjectAccess $access) {}

    public function view(User $user, Project $project): bool
    {
        return $this->access->allows($user, $project);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->access->allows($user, $project, ['EXECUTIVE']);
    }

    public function archive(User $user, Project $project): bool
    {
        return $this->update($user, $project);
    }
}
