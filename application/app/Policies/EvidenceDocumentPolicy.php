<?php

namespace App\Policies;

use App\Models\EvidenceDocument;
use App\Models\User;
use App\Services\ProjectAccess;

class EvidenceDocumentPolicy
{
    public function __construct(private readonly ProjectAccess $access) {}

    public function view(User $user, EvidenceDocument $evidence): bool
    {
        return $this->access->allows($user, $evidence->project);
    }
}
