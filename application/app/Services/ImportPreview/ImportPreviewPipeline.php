<?php

namespace App\Services\ImportPreview;

use App\Models\Project;
use DateTimeImmutable;
use RuntimeException;

final class ImportPreviewPipeline
{
    public function __construct(private readonly WorkbookReaderInterface $reader, private readonly ?DuplicateResolver $duplicates = null, private readonly ?CanonicalReferenceResolver $references = null) {}

    public function preview(string $path, Project $project): array
    {
        try {
            $sheets = $this->reader->read($path);
        } catch (RuntimeException $e) {
            return $this->fatal($e->getMessage());
        }
        $issues = [];
        $references = $this->references ?? new CanonicalReferenceResolver($project);
        foreach (array_diff(array_keys(ImportPreviewContracts::SHEET_FIELDS), array_keys($sheets)) as $name) {
            $issues[] = ImportPreviewContracts::issue('MISSING_REQUIRED_SHEET', 'FATAL', null, null, null, "Required sheet {$name} is missing.");
        }
        foreach (array_diff(array_keys($sheets), array_keys(ImportPreviewContracts::SHEET_FIELDS)) as $name) {
            $issues[] = ImportPreviewContracts::issue('UNKNOWN_SHEET', 'WARNING', null, null, null, "Sheet {$name} is not imported.");
        }
        $mapped = [];
        $allRows = [];
        $workbookWbs = [];
        if (isset($sheets['WBS'])) {
            $wbsHeaders = array_map(static fn ($h) => strtolower(trim((string) $h)), $sheets['WBS']['headers']);
            $wbsIndex = array_search('wbs_code', $wbsHeaders, true);
            if ($wbsIndex !== false) {
                foreach ($sheets['WBS']['rows'] as $values) {
                    if (($values[$wbsIndex] ?? '') !== '') {
                        $workbookWbs[(string) $values[$wbsIndex]] = true;
                    }
                }
            }
        }
        foreach ($sheets as $sheetName => $sheet) {
            if (! isset(ImportPreviewContracts::SHEET_FIELDS[$sheetName])) {
                continue;
            }
            $headers = array_map(static fn ($h) => strtolower(trim((string) $h)), $sheet['headers']);
            $positions = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }
                if (isset($positions[$header])) {
                    $issues[] = ImportPreviewContracts::issue('AMBIGUOUS_HEADER', 'FATAL', 1, $sheet['headers'][$i], null, 'Duplicate header is ambiguous.');
                } else {
                    $positions[$header] = $i;
                }
            }
            foreach (ImportPreviewContracts::SHEET_FIELDS[$sheetName] as $field) {
                if (! array_key_exists($field, $positions)) {
                    $issues[] = ImportPreviewContracts::issue('MISSING_REQUIRED_COLUMN', 'FATAL', 1, null, $field, "Required column {$field} is missing from {$sheetName}.");
                }
            }
            foreach ($positions as $header => $i) {
                if (! in_array($header, ImportPreviewContracts::FIELDS, true)) {
                    $issues[] = ImportPreviewContracts::issue('UNMAPPED_HEADER', 'INFO', 1, $sheet['headers'][$i], null, 'Header is visible but not imported.');
                }
            }
            $mapped[$sheetName] = ['headers' => $sheet['headers'], 'positions' => $positions, 'rows' => []];
            foreach ($sheet['rows'] as $offset => $values) {
                $rowNumber = $offset + 2;
                $candidate = ['_row_number' => $rowNumber];
                foreach ($positions as $field => $i) {
                    if (in_array($field, ImportPreviewContracts::FIELDS, true)) {
                        $candidate[$field] = $values[$i] ?? null;
                    }
                }
                $rowIssues = $this->validateRow($sheetName, $candidate, $project, $workbookWbs, $references);
                $status = $rowIssues === [] ? 'VALID' : 'INVALID';
                $mapped[$sheetName]['rows'][] = ['row_number' => $rowNumber, 'mapped_values' => $this->publicValues($candidate), 'status' => $status, 'errors' => $rowIssues, 'warnings' => [], 'duplicate_matches' => [], 'reference_resolution' => []];
                $allRows[] = ['sheet' => $sheetName] + $candidate;
            }
        }
        $duplicateGroups = ($this->duplicates ?? new DuplicateResolver)->workbook($allRows);
        foreach ($duplicateGroups as $group) {
            foreach ($group as [$duplicateSheet, $number]) {
                foreach ($mapped[$duplicateSheet]['rows'] as &$row) {
                    if ($row['row_number'] === $number) {
                        $row['status'] = 'DUPLICATE';
                        $row['errors'][] = ImportPreviewContracts::issue('DUPLICATE_WITHIN_WORKBOOK', 'ERROR', $number, null, null, 'Exact normalized identity is duplicated.');
                    }
                }
                unset($row);
            }
        }
        unset($sheet, $row);
        if ($project->exists && isset($mapped['WBS'])) {
            $persistedWbs = $project->wbsTasks()->pluck('wbs_code')->map(static fn ($code): string => strtoupper(trim((string) $code)))->all();
            foreach ($mapped['WBS']['rows'] as &$row) {
                $code = strtoupper(trim((string) ($row['mapped_values']['wbs_code'] ?? '')));
                if ($code !== '' && in_array($code, $persistedWbs, true)) {
                    $row['status'] = 'DUPLICATE';
                    $row['errors'][] = ImportPreviewContracts::issue('DUPLICATE_EXISTING_RECORD', 'ERROR', $row['row_number'], 'wbs_code', 'wbs_code', 'WBS code already exists in the canonical project records.');
                }
            }
            unset($row);
        }
        foreach ($mapped as $name => &$sheet) {
            $sheet['rows'] = array_values($sheet['rows']);
        }
        unset($sheet);
        $fatal = count(array_filter($issues, static fn ($i) => $i['severity'] === 'FATAL'));
        $rowList = array_merge(...array_map(static fn ($s) => $s['rows'], array_values($mapped))) ?: [];
        $invalid = count(array_filter($rowList, static fn ($r) => in_array($r['status'], ['INVALID', 'DUPLICATE'], true)));

        return ['schema' => 'RM09_PREVIEW_V1', 'ready' => $fatal === 0 && $invalid === 0, 'sheets' => $mapped, 'issues' => array_values($issues), 'summary' => ['total_rows' => count($rowList), 'valid_rows' => count(array_filter($rowList, fn ($r) => $r['status'] === 'VALID')), 'invalid_rows' => $invalid, 'warning_rows' => 0, 'duplicate_rows' => count(array_filter($rowList, fn ($r) => $r['status'] === 'DUPLICATE')), 'unmapped_columns' => count(array_filter($issues, fn ($i) => $i['code'] === 'UNMAPPED_HEADER')), 'ambiguous_columns' => count(array_filter($issues, fn ($i) => $i['code'] === 'AMBIGUOUS_HEADER')), 'fatal_workbook_errors' => $fatal]];
    }

    private function validateRow(string $sheet, array $r, Project $project, array $workbookWbs = [], ?CanonicalReferenceResolver $references = null): array
    {
        $errors = [];
        $references ??= new CanonicalReferenceResolver($project);
        if (($r['project_code'] ?? '') === '' || ! is_string($r['project_code'] ?? null)) {
            $errors[] = ImportPreviewContracts::issue('REQUIRED_VALUE', 'ERROR', $r['_row_number'], 'project_code', 'project_code', 'Project code is required.');
        } elseif (! $references->projectCode($r['project_code'])) {
            $errors[] = ImportPreviewContracts::issue('PROJECT_REFERENCE_UNKNOWN', 'ERROR', $r['_row_number'], 'project_code', 'project_code', 'Project code is outside the authorized project.');
        }
        if (in_array($sheet, ['WBS', 'TASK'], true)) {
            if (($r['wbs_code'] ?? '') === '') {
                $errors[] = ImportPreviewContracts::issue('REQUIRED_VALUE', 'ERROR', $r['_row_number'], 'wbs_code', 'wbs_code', 'wbs_code is required.');
            } elseif ($sheet === 'TASK' && (! isset($workbookWbs[(string) $r['wbs_code']]) || ! $references->wbsCode((string) $r['wbs_code']))) {
                $errors[] = ImportPreviewContracts::issue('UNKNOWN_REFERENCE', 'ERROR', $r['_row_number'], 'wbs_code', 'wbs_code', 'WBS code is not present in the canonical project reference.');
            }
        }
        if (in_array($sheet, ['TASK'], true)) {
            foreach (['wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'] as $f) {
                if (($r[$f] ?? '') === '') {
                    $errors[] = ImportPreviewContracts::issue('REQUIRED_VALUE', 'ERROR', $r['_row_number'], $f, $f, "{$f} is required.");
                }
            }
            if (($r['baseline_start_date'] ?? '') !== '' && ! $this->date($r['baseline_start_date'])) {
                $errors[] = ImportPreviewContracts::issue('INVALID_DATE', 'ERROR', $r['_row_number'], 'baseline_start_date', 'baseline_start_date', 'Date must use YYYY-MM-DD.');
            }
            if (($r['baseline_finish_date'] ?? '') !== '' && ! $this->date($r['baseline_finish_date'])) {
                $errors[] = ImportPreviewContracts::issue('INVALID_DATE', 'ERROR', $r['_row_number'], 'baseline_finish_date', 'baseline_finish_date', 'Date must use YYYY-MM-DD.');
            }
            if (($r['progress_percent'] ?? '') !== '' && ! $this->percentage($r['progress_percent'])) {
                $errors[] = ImportPreviewContracts::issue('INVALID_PERCENTAGE', 'ERROR', $r['_row_number'], 'progress_percent', 'progress_percent', 'Percentage must be a decimal between 0 and 100.');
            }
            if (($r['status'] ?? '') !== '' && ! in_array((string) $r['status'], ['NOT_STARTED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'], true)) {
                $errors[] = ImportPreviewContracts::issue('INVALID_STATUS', 'ERROR', $r['_row_number'], 'status', 'status', 'Status is not accepted.');
            }
        }
        if ($sheet === 'FINANCE') {
            foreach (['direction', 'amount', 'occurred_at', 'description'] as $f) {
                if (($r[$f] ?? '') === '') {
                    $errors[] = ImportPreviewContracts::issue('REQUIRED_VALUE', 'ERROR', $r['_row_number'], $f, $f, "{$f} is required.");
                }
            }
            if (($r['direction'] ?? '') !== '' && ! in_array((string) $r['direction'], ['INCOME', 'EXPENSE'], true)) {
                $errors[] = ImportPreviewContracts::issue('INVALID_DIRECTION', 'ERROR', $r['_row_number'], 'direction', 'direction', 'Direction must be INCOME or EXPENSE.');
            }
            if (($r['amount'] ?? '') !== '' && ! $this->amount($r['amount'])) {
                $errors[] = ImportPreviewContracts::issue('INVALID_AMOUNT', 'ERROR', $r['_row_number'], 'amount', 'amount', 'Amount must be a decimal with at most two places.');
            }
            if (($r['occurred_at'] ?? '') !== '' && ! $this->date($r['occurred_at'])) {
                $errors[] = ImportPreviewContracts::issue('INVALID_DATE', 'ERROR', $r['_row_number'], 'occurred_at', 'occurred_at', 'Date must use YYYY-MM-DD.');
            }
        }

        return $errors;
    }

    private function date(mixed $v): bool
    {
        if (! is_string($v) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return false;
        } $d = DateTimeImmutable::createFromFormat('!Y-m-d', $v);

        return $d !== false && $d->format('Y-m-d') === $v;
    }

    private function percentage(mixed $v): bool
    {
        return (is_string($v) || is_int($v) || is_float($v)) && preg_match('/^(?:0|[1-9]\d{0,1}|100)(?:\.\d+)?$/', (string) $v) === 1 && (float) $v <= 100;
    }

    private function amount(mixed $v): bool
    {
        return (is_string($v) || is_int($v) || is_float($v)) && preg_match('/^-?(?:0|[1-9]\d*)(?:\.\d{1,2})?$/', (string) $v) === 1;
    }

    private function publicValues(array $r): array
    {
        unset($r['_row_number']);

        return array_intersect_key($r, array_flip(ImportPreviewContracts::FIELDS));
    }

    private function fatal(string $code): array
    {
        return ['schema' => 'RM09_PREVIEW_V1', 'ready' => false, 'sheets' => [], 'issues' => [ImportPreviewContracts::issue($code,'FATAL',null,null,null,'Workbook preview failed closed.')], 'summary' => ['total_rows' => 0, 'valid_rows' => 0, 'invalid_rows' => 0, 'warning_rows' => 0, 'duplicate_rows' => 0, 'unmapped_columns' => 0, 'ambiguous_columns' => 0, 'fatal_workbook_errors' => 1]];
    }
}
