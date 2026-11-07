<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Services\ImportPreview\ImportPreviewPipeline;
use App\Services\ImportPreview\PhpSpreadsheetWorkbookReader;
use App\Services\ImportPreview\WorkbookReaderInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class ImportPreviewPipelineTest extends TestCase
{
    public function test_real_php_spreadsheet_workbook_is_read_and_mapped_without_writes(): void
    {
        $project = new Project(['code' => 'CED-001']);
        $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview(__DIR__.'/../Fixtures/import-preview/valid.xlsx', $project);
        self::assertTrue($report['ready']);
        self::assertSame(['PROJECT', 'WBS', 'TASK', 'FINANCE'], array_keys($report['sheets']));
        self::assertSame('Task A', $report['sheets']['TASK']['rows'][0]['mapped_values']['task_name']);
        self::assertSame('EXPENSE', $report['sheets']['FINANCE']['rows'][0]['mapped_values']['direction']);
        self::assertSame(0, $report['summary']['invalid_rows']);
    }

    public function test_corrupt_workbook_fails_closed_with_no_partial_preview(): void
    {
        $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview(__DIR__.'/../Fixtures/import-preview/corrupt.xlsx', new Project(['code' => 'CED-001']));
        self::assertFalse($report['ready']);
        self::assertSame([], $report['sheets']);
        self::assertContains($report['issues'][0]['code'], ['CORRUPT_WORKBOOK', 'INVALID_XLSX_SIGNATURE']);
    }

    public function test_invalid_types_are_structured_errors_and_unknown_headers_are_not_imported(): void
    {
        $reader = new class implements WorkbookReaderInterface
        {
            public function read(string $path): array
            {
                return ['PROJECT' => ['headers' => ['project_code', 'mystery'], 'rows' => [['CED-001', 'x']]], 'WBS' => ['headers' => ['project_code', 'wbs_code'], 'rows' => [['CED-001', 'W-1']]], 'TASK' => ['headers' => ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'], 'rows' => [['CED-001', 'W-1', 'T', 'bad', '2026-01-01', 101, 'BAD']]], 'FINANCE' => ['headers' => ['project_code', 'direction', 'amount', 'occurred_at', 'description'], 'rows' => []]];
            }
        };
        $report = (new ImportPreviewPipeline($reader))->preview('ignored', new Project(['code' => 'CED-001']));
        self::assertFalse($report['ready']);
        self::assertSame(1, $report['summary']['unmapped_columns']);
        self::assertContains('INVALID_DATE', array_column($report['sheets']['TASK']['rows'][0]['errors'], 'code'));
        self::assertContains('INVALID_PERCENTAGE', array_column($report['sheets']['TASK']['rows'][0]['errors'], 'code'));
    }

    public function test_all_canonical_sheets_validate_duplicate_identity_and_reject_silent_coercion(): void
    {
        $reader = new class implements WorkbookReaderInterface
        {
            public function read(string $path): array
            {
                return [
                    'PROJECT' => ['headers' => ['project_code'], 'rows' => [['CED-001'], [' ced-001 ']]],
                    'WBS' => ['headers' => ['project_code', 'wbs_code'], 'rows' => [['CED-001', 'W-1']]],
                    'TASK' => ['headers' => ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'], 'rows' => [['CED-001', 'W-1', 'Task', '2026-01-01', '2026-01-02', '1e2', 'BAD']]],
                    'FINANCE' => ['headers' => ['project_code', 'direction', 'amount', 'occurred_at', 'description'], 'rows' => [['CED-001', 'SIDEWAYS', '12.345', '2026-01-01', 'Cost']]],
                ];
            }
        };
        $report = (new ImportPreviewPipeline($reader))->preview('ignored', new Project(['code' => 'CED-001']));

        self::assertSame(['PROJECT', 'WBS', 'TASK', 'FINANCE'], array_keys($report['sheets']));
        self::assertSame('DUPLICATE', $report['sheets']['PROJECT']['rows'][0]['status']);
        self::assertSame('DUPLICATE', $report['sheets']['PROJECT']['rows'][1]['status']);
        $codes = array_column($report['sheets']['TASK']['rows'][0]['errors'], 'code');
        $codes = array_merge($codes, array_column($report['sheets']['FINANCE']['rows'][0]['errors'], 'code'));
        self::assertContains('INVALID_PERCENTAGE', $codes);
        self::assertContains('INVALID_STATUS', $codes);
        self::assertContains('INVALID_DIRECTION', $codes);
        self::assertContains('INVALID_AMOUNT', $codes);
        self::assertFalse($report['ready']);
    }

    public function test_duplicate_project_rows_do_not_mark_unrelated_wbs_row_with_same_row_number(): void
    {
        $reader = new class implements WorkbookReaderInterface
        {
            public function read(string $path): array
            {
                return [
                    'PROJECT' => ['headers' => ['project_code'], 'rows' => [['CED-001'], [' ced-001 ']]],
                    'WBS' => ['headers' => ['project_code', 'wbs_code'], 'rows' => [['CED-001', 'W-1']]],
                    'TASK' => ['headers' => ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'], 'rows' => []],
                    'FINANCE' => ['headers' => ['project_code', 'direction', 'amount', 'occurred_at', 'description'], 'rows' => []],
                ];
            }
        };

        $report = (new ImportPreviewPipeline($reader))->preview('ignored', new Project(['code' => 'CED-001']));

        self::assertSame('DUPLICATE', $report['sheets']['PROJECT']['rows'][0]['status']);
        self::assertSame('DUPLICATE', $report['sheets']['PROJECT']['rows'][1]['status']);
        self::assertSame('VALID', $report['sheets']['WBS']['rows'][0]['status']);
        self::assertSame([], $report['sheets']['WBS']['rows'][0]['errors']);
    }

    public function test_reader_fails_closed_for_size_and_timeout_without_partial_sheets(): void
    {
        $oversized = tempnam(sys_get_temp_dir(), 'rm09-');
        file_put_contents($oversized, str_repeat('x', 10 * 1024 * 1024 + 1));
        rename($oversized, $oversized.'.xlsx');
        $sizeReport = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview($oversized.'.xlsx', new Project(['code' => 'CED-001']));
        self::assertSame('UPLOAD_SIZE_LIMIT', $sizeReport['issues'][0]['code']);
        self::assertSame([], $sizeReport['sheets']);
        @unlink($oversized.'.xlsx');

        $path = tempnam(sys_get_temp_dir(), 'rm09-').'.xlsx';
        $book = new Spreadsheet;
        $book->getActiveSheet()->setCellValue('A1', 'project_code');
        (new Xlsx($book))->save($path);
        $clockCalls = 0;
        $reader = new PhpSpreadsheetWorkbookReader(30.0, static function () use (&$clockCalls): float {
            return ++$clockCalls === 1 ? 0.0 : 31.0;
        });
        $timeoutReport = (new ImportPreviewPipeline($reader))->preview($path, new Project(['code' => 'CED-001']));
        self::assertSame('PARSER_TIMEOUT', $timeoutReport['issues'][0]['code']);
        self::assertSame([], $timeoutReport['sheets']);
        @unlink($path);
    }

    public function test_real_reader_rejects_more_than_ten_sheets_without_partial_preview(): void
    {
        $path = $this->workbookPath(11, 1, 1);
        $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview($path, new Project(['code' => 'CED-001']));
        self::assertFalse($report['ready']);
        self::assertSame('SHEET_LIMIT', $report['issues'][0]['code']);
        self::assertSame([], $report['sheets']);
        @unlink($path);
    }

    public function test_real_reader_rejects_more_than_five_thousand_rows_without_truncating(): void
    {
        $path = $this->workbookPath(1, 5001, 1);
        $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview($path, new Project(['code' => 'CED-001']));
        self::assertFalse($report['ready']);
        self::assertSame('ROW_LIMIT', $report['issues'][0]['code']);
        self::assertSame([], $report['sheets']);
        @unlink($path);
    }

    public function test_real_reader_rejects_more_than_sixty_four_columns_without_partial_preview(): void
    {
        $path = $this->workbookPath(1, 1, 65);
        $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview($path, new Project(['code' => 'CED-001']));
        self::assertFalse($report['ready']);
        self::assertSame('COLUMN_LIMIT', $report['issues'][0]['code']);
        self::assertSame([], $report['sheets']);
        @unlink($path);
    }

    public function test_real_reader_rejects_macro_and_external_link_archives(): void
    {
        foreach (['xl/vbaProject.bin' => 'MACRO_REJECTED', 'xl/externalLinks/externalLink1.xml' => 'EXTERNAL_LINK_REJECTED'] as $entry => $code) {
            $path = $this->workbookPath(1, 1, 1);
            $zip = new ZipArchive;
            self::assertTrue($zip->open($path));
            $zip->addFromString($entry, 'synthetic boundary marker');
            $zip->close();
            $report = (new ImportPreviewPipeline(new PhpSpreadsheetWorkbookReader))->preview($path, new Project(['code' => 'CED-001']));
            self::assertFalse($report['ready']);
            self::assertSame($code, $report['issues'][0]['code']);
            self::assertSame([], $report['sheets']);
            @unlink($path);
        }
    }

    public function test_real_reader_returns_formula_cached_value_in_read_data_only_mode(): void
    {
        $path = $this->workbookPath(1, 1, 1, true);
        $sheets = (new PhpSpreadsheetWorkbookReader)->read($path);
        self::assertSame(42, $sheets['SHEET1']['rows'][0][0]);
        @unlink($path);
    }

    public function test_real_reader_extracts_headers_and_pipeline_enforces_required_sheet_and_column_contract(): void
    {
        $path = $this->workbookPath(1, 1, 2, false, ['project_code', 'unexpected_header']);
        $sheets = (new PhpSpreadsheetWorkbookReader)->read($path);
        self::assertSame(['project_code', 'unexpected_header'], $sheets['SHEET1']['headers']);
        $reader = new class implements WorkbookReaderInterface
        {
            public function read(string $path): array
            {
                return ['PROJECT' => ['headers' => ['project_code'], 'rows' => []], 'WBS' => ['headers' => ['project_code'], 'rows' => []], 'TASK' => ['headers' => ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'], 'rows' => []], 'FINANCE' => ['headers' => ['project_code', 'direction', 'amount', 'occurred_at'], 'rows' => []]];
            }
        };
        $report = (new ImportPreviewPipeline($reader))->preview('ignored', new Project(['code' => 'CED-001']));
        self::assertSame(2, count(array_filter($report['issues'], fn (array $i): bool => $i['code'] === 'MISSING_REQUIRED_COLUMN')));
        self::assertContains('MISSING_REQUIRED_SHEET', array_column((new ImportPreviewPipeline(new class implements WorkbookReaderInterface
        {
            public function read(string $path): array
            {
                return ['PROJECT' => ['headers' => ['project_code'], 'rows' => []]];
            }
        }))->preview('ignored', new Project(['code' => 'CED-001']))['issues'], 'code'));
        @unlink($path);
    }

    private function workbookPath(int $sheetCount, int $rows, int $columns, bool $formula = false, ?array $headers = null): string
    {
        $path = tempnam(sys_get_temp_dir(), 'rm09-').'.xlsx';
        $book = new Spreadsheet;
        $book->getActiveSheet()->setTitle('SHEET1');
        for ($s = 1; $s < $sheetCount; $s++) {
            $book->createSheet()->setTitle('SHEET'.($s + 1));
        }
        foreach ($book->getWorksheetIterator() as $sheet) {
            for ($c = 1; $c <= $columns; $c++) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($c).'1', $headers[$c - 1] ?? 'column_'.$c);
            }
            for ($r = 2; $r <= $rows + 1; $r++) {
                $sheet->setCellValue('A'.$r, $formula && $r === 2 ? '=40+2' : 'value');
            }
            if ($formula) {
                $sheet->getCell('A2')->setCalculatedValue(42);
            }
        }
        (new Xlsx($book))->save($path);

        return $path;
    }
}
