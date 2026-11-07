<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\WbsTask;
use App\Services\ImportPreview\ImportPreviewPipeline;
use App\Services\ImportPreview\WorkbookReaderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ImportPreviewValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_boundary_has_distinct_get_and_post_routes(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())->filter(fn ($r) => str_contains($r->getName() ?? '', 'projects.import-preview'))->values();
        self::assertSame(['GET', 'POST'], array_map(fn ($r) => $r->methods()[0], $routes->all()));
        self::assertSame('projects.import-preview.create', $routes[0]->getName());
        self::assertSame('projects.import-preview.store', $routes[1]->getName());
    }

    public function test_pipeline_compares_references_to_persisted_wbs_records_and_never_calls_network(): void
    {
        Http::fake();
        $project = Project::create(['id' => 'project-db', 'code' => 'CED-DB', 'name' => 'DB', 'baseline_start_date' => '2026-01-01', 'baseline_finish_date' => '2026-12-31', 'budget_amount' => 1000, 'status' => 'ACTIVE']);
        $other = Project::create(['id' => 'project-other-db', 'code' => 'CED-OTHER-DB', 'name' => 'Other', 'baseline_start_date' => '2026-01-01', 'baseline_finish_date' => '2026-12-31', 'budget_amount' => 1000, 'status' => 'ACTIVE']);
        WbsTask::create(['id' => 'task-db', 'project_id' => $project->id, 'wbs_code' => 'W-1', 'name' => 'Persisted', 'baseline_start_date' => '2026-01-01', 'baseline_finish_date' => '2026-01-02', 'weight' => 1, 'status' => 'NOT_STARTED', 'is_baseline' => true, 'is_leaf' => true]);
        WbsTask::create(['id' => 'task-other-db', 'project_id' => $other->id, 'wbs_code' => 'W-OTHER', 'name' => 'Other', 'baseline_start_date' => '2026-01-01', 'baseline_finish_date' => '2026-01-02', 'weight' => 1, 'status' => 'NOT_STARTED', 'is_baseline' => true, 'is_leaf' => true]);
        $reader = new class implements WorkbookReaderInterface
        {
            public function read(string $path): array
            {
                return ['PROJECT' => ['headers' => ['project_code'], 'rows' => [['CED-DB']]], 'WBS' => ['headers' => ['project_code', 'wbs_code'], 'rows' => [['CED-DB', 'W-1'], ['CED-DB', 'W-MISSING']]], 'TASK' => ['headers' => ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'], 'rows' => [['CED-DB', 'W-1', 'Task', '2026-01-01', '2026-01-02', 0, 'NOT_STARTED'], ['CED-DB', 'W-OTHER', 'Leak', '2026-01-01', '2026-01-02', 0, 'NOT_STARTED']]], 'FINANCE' => ['headers' => ['project_code', 'direction', 'amount', 'occurred_at', 'description'], 'rows' => []]];
            }
        };
        $before = WbsTask::query()->count();
        $report = (new ImportPreviewPipeline($reader))->preview('ignored', $project);
        self::assertSame('DUPLICATE', $report['sheets']['WBS']['rows'][0]['status']);
        self::assertContains('DUPLICATE_EXISTING_RECORD', array_column($report['sheets']['WBS']['rows'][0]['errors'], 'code'));
        self::assertSame('VALID', $report['sheets']['TASK']['rows'][0]['status']);
        self::assertContains('UNKNOWN_REFERENCE', array_column($report['sheets']['TASK']['rows'][1]['errors'], 'code'));
        self::assertSame($before, WbsTask::query()->count());
        Http::assertNothingSent();
    }
}
