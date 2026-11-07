<?php

namespace App\Services\ImportPreview;

final class DuplicateResolver
{
    public function workbook(array $rows): array
    {
        $seen = [];
        foreach ($rows as $row) {
            $sheet = (string) ($row['sheet'] ?? '');
            $fields = match ($sheet) {
                'PROJECT' => ['project_code'],
                'WBS' => ['project_code', 'wbs_code'],
                'TASK' => ['project_code', 'wbs_code', 'task_name'],
                'FINANCE' => ['project_code', 'direction', 'amount', 'occurred_at', 'description'],
                default => []
            };
            $identity = [];
            foreach ($fields as $field) {
                $identity[$field] = $this->normalize($field, $row[$field] ?? null);
            }
            $key = json_encode([$sheet, $identity], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $seen[$key][] = [$sheet, $row['_row_number']];
        }

        return array_values(array_filter($seen, static fn ($numbers) => count($numbers) > 1));
    }

    private function normalize(string $field, mixed $value): string
    {
        $value = trim((string) $value);
        if (in_array($field, ['project_code', 'wbs_code', 'direction'], true)) {
            return strtoupper($value);
        }
        if ($field === 'amount' && preg_match('/^-?(?:\d+)(?:\.\d+)?$/', $value)) {
            $value = rtrim(rtrim($value, '0'), '.');

            return $value === '-0' ? '0' : $value;
        }

        return $value;
    }
}
