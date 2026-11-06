<?php

namespace App\Providers;

use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Policies\EvidenceDocumentPolicy;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(EvidenceDocument::class, EvidenceDocumentPolicy::class);
    }
}
