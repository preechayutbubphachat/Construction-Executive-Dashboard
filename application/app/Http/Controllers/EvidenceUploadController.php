<?php

namespace App\Http\Controllers;

use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Services\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class EvidenceUploadController extends Controller
{
    public function __invoke(
        Request $request,
        Project $project,
        AuditRecorder $audit,
    ): RedirectResponse {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'evidence' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,jpg,jpeg,png,webp',
                'mimetypes:application/pdf,image/jpeg,image/png,image/webp',
            ],
        ]);
        $file = $validated['evidence'];
        $disk = Storage::disk('evidence');
        $bytes = $file->get();
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        $extension = match ($mimeType) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                'evidence' => 'The evidence content type is not supported.',
            ]),
        };
        $sha256 = hash('sha256', $bytes);
        $documentId = (string) Str::ulid();
        $storagePath = $project->getKey().'/'.$documentId.'.'.$extension;
        $written = false;

        try {
            DB::transaction(function () use (
                $audit,
                $bytes,
                $documentId,
                $file,
                $mimeType,
                $project,
                $request,
                $sha256,
                $storagePath,
                $disk,
                &$written,
            ): void {
                if (! $disk->put($storagePath, $bytes)) {
                    throw new RuntimeException('Evidence storage failed.');
                }
                $written = true;

                $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));
                $originalName = substr($originalName !== '' ? $originalName : 'evidence', 0, 255);
                $evidence = EvidenceDocument::query()->create([
                    'id' => $documentId,
                    'project_id' => $project->getKey(),
                    'storage_disk' => 'evidence',
                    'storage_path' => $storagePath,
                    'original_name' => $originalName,
                    'mime_type' => $mimeType,
                    'byte_size' => strlen($bytes),
                    'sha256' => $sha256,
                    'access_class' => 'PRIVATE',
                    'review_state' => 'UPLOADED',
                    'uploaded_by' => $request->user()->getKey(),
                ]);
                $audit->record($project->getKey(), $request->user(), 'evidence.created', $evidence, [
                    'access_class' => 'PRIVATE',
                    'review_state' => 'UPLOADED',
                    'mime_type' => $mimeType,
                    'byte_size' => strlen($bytes),
                    'sha256' => $sha256,
                ]);
            });
        } catch (Throwable $exception) {
            if ($written) {
                try {
                    $disk->delete($storagePath);
                } catch (Throwable) {
                    // Preserve the original storage, database, or audit failure.
                }
            }

            throw $exception;
        }

        return redirect()
            ->route('projects.executive', $project)
            ->with('status', 'Evidence uploaded securely.');
    }
}
