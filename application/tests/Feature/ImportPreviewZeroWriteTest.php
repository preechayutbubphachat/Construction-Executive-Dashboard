<?php

namespace Tests\Feature;

use App\Http\Controllers\ImportPreviewController;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class ImportPreviewZeroWriteTest extends TestCase
{
    public function test_real_controller_upload_preview_cleans_private_temporary_file(): void
    {
        Gate::shouldReceive('authorize')->once()->andReturnTrue();
        $request = Request::create('/projects/CED-001/import-preview', 'POST');
        $request->setUserResolver(fn () => new User(['id' => 99]));
        $request->files->set('workbook', new UploadedFile(base_path('tests/Fixtures/import-preview/valid.xlsx'), 'valid.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true));
        $project = new Project(['id' => 'CED-001', 'code' => 'CED-001']);
        $response = (new ImportPreviewController)->store($request, $project);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('data-preview-ready="true"', $response->getContent());
        self::assertDirectoryDoesNotExist(storage_path('app/rm09-previews/99'));
    }

    public function test_preview_source_has_no_domain_mutation_calls_or_audit_write(): void
    {
        $source = file_get_contents(base_path('app/Services/ImportPreview/ImportPreviewPipeline.php'));
        self::assertIsString($source);
        self::assertDoesNotMatchRegularExpression('/->(?:save|create|update|delete|push|attach|detach|sync)\s*\(/', $source);
        self::assertStringNotContainsString('AuditRecorder', $source);
    }
}
