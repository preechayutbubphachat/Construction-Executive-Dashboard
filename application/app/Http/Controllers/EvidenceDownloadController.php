<?php

namespace App\Http\Controllers;

use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Services\AuditRecorder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceDownloadController extends Controller
{
    public function __invoke(
        Project $project,
        EvidenceDocument $evidence,
        AuditRecorder $audit,
    ): StreamedResponse {
        Gate::authorize('view', $project);
        abort_unless($evidence->project_id === $project->id, 404);
        Gate::authorize('view', $evidence);
        abort_unless($evidence->access_class === 'PRIVATE' && $evidence->storage_disk === 'evidence', 404);
        abort_unless(Storage::disk('evidence')->exists($evidence->storage_path), 404);

        $audit->record($project->id, request()->user(), 'evidence.downloaded', $evidence, [
            'access_class' => $evidence->access_class,
            'review_state' => $evidence->review_state,
        ]);

        return Storage::disk('evidence')->download($evidence->storage_path, $evidence->original_name);
    }
}
