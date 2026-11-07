<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ImportPreview\ImportPreviewPipeline;
use App\Services\ImportPreview\PhpSpreadsheetWorkbookReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ImportPreviewController extends Controller
{
    public function create(Project $project)
    {
        Gate::authorize('view', $project);

        return view('projects.import-preview', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);
        $file = $request->file('workbook');
        if (! $file || ! $file->isValid() || strtolower($file->getClientOriginalExtension()) !== 'xlsx' || $file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['workbook' => 'Only a valid .xlsx workbook up to 10 MiB is accepted.']);
        }
        $bytes = file_get_contents($file->getRealPath());
        if ($bytes === false || strlen($bytes) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['workbook' => 'Workbook could not be read.']);
        }
        $dir = storage_path('app/rm09-previews/'.(string) $request->user()->getKey());
        if (! is_dir($dir)) {
            mkdir($dir, 0700, true);
        }
        $path = $dir.'/'.bin2hex(random_bytes(16)).'.xlsx';
        try {
            file_put_contents($path, $bytes, LOCK_EX);
            $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview($path, $project);

            return response()->view('projects.import-preview', compact('project', 'report'));
        } catch (Throwable) {
            return response()->view('projects.import-preview', ['project' => $project, 'report' => ['ready' => false, 'issues' => [['code' => 'PREVIEW_FAILED', 'severity' => 'FATAL', 'message' => 'Workbook preview failed closed.']], 'sheets' => [], 'summary' => []]]);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
            if (is_dir($dir) && count(scandir($dir)) === 2) {
                rmdir($dir);
            }
        }
    }
}
