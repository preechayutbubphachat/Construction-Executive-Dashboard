<?php

namespace App\Services\ImportPreview;

final class ImportPreviewContracts
{
    public const FIELDS = ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status', 'direction', 'amount', 'occurred_at', 'description'];

    public const SHEET_FIELDS = [
        'PROJECT' => ['project_code'],
        'WBS' => ['project_code', 'wbs_code'],
        'TASK' => ['project_code', 'wbs_code', 'task_name', 'baseline_start_date', 'baseline_finish_date', 'progress_percent', 'status'],
        'FINANCE' => ['project_code', 'direction', 'amount', 'occurred_at', 'description'],
    ];

    public static function issue(string $code, string $severity, ?int $row, ?string $column, ?string $field, string $message): array
    {
        return compact('code', 'severity', 'row', 'column', 'field', 'message') + ['value_visibility' => 'safe'];
    }
}
