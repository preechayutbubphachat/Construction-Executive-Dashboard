<?php

namespace App\Http\Controllers;

use App\Models\EvidenceDocument;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidencePreviewController extends Controller
{
    public function __invoke(Project $project, EvidenceDocument $evidence): StreamedResponse
    {
        Gate::authorize('view', $project);
        abort_unless($evidence->project_id === $project->id, 404);
        Gate::authorize('view', $evidence);
        abort_unless($evidence->storage_disk === 'evidence', 404);
        abort_unless(Storage::disk('evidence')->exists($evidence->storage_path), 404);

        $disk = Storage::disk('evidence');
        $mime = $evidence->mime_type ?: 'image/jpeg';

        return response()->stream(function () use ($disk, $evidence): void {
            $stream = $disk->readStream($evidence->storage_path);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($evidence->original_name).'"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
