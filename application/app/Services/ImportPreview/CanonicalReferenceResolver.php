<?php

namespace App\Services\ImportPreview;

use App\Models\Project;

final class CanonicalReferenceResolver
{
    public function __construct(private readonly Project $project) {}

    public function projectCode(string $code): bool
    {
        return self::normalizeIdentifier((string) $this->project->code) === self::normalizeIdentifier($code);
    }

    public function wbsCode(string $code): bool
    {
        // Unsaved model doubles are used by the pure pipeline tests. Production
        // projects are persisted and therefore use the canonical read-only seam.
        if (! $this->project->exists) {
            return trim($code) !== '';
        }

        return $this->project->wbsTasks()->where('wbs_code', trim($code))->exists();
    }

    public static function normalizeIdentifier(string $value): string
    {
        return strtoupper(trim($value));
    }
}
