<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineeringSubmittal extends Model
{
    use HasImmutableKey;

    public const TYPES = [
        'SHOP_DRAWING',
        'MATERIAL_SAMPLE',
        'METHOD_STATEMENT',
        'CALCULATION_SHEET',
        'AS_BUILT_DRAWING',
    ];

    public const DISCIPLINES = EngineeringRfi::DISCIPLINES;

    public const STATUSES = [
        'PENDING_REVIEW',
        'APPROVED',
        'APPROVED_AS_NOTED',
        'REVISE_AND_RESUBMIT',
        'REJECTED',
    ];

    public const REVIEW_DECISIONS = [
        'APPROVED',
        'APPROVED_AS_NOTED',
        'REVISE_AND_RESUBMIT',
        'REJECTED',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'submittal_number',
        'reference_no',
        'submittal_type',
        'discipline',
        'title',
        'spec_section',
        'revision',
        'status',
        'review_comments',
        'reviewed_by',
        'reviewed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'submittal_number' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function isApprovedForConstruction(): bool
    {
        return in_array($this->status, ['APPROVED', 'APPROVED_AS_NOTED'], true);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WbsTask::class, 'wbs_task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
