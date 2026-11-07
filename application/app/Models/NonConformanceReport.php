<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NonConformanceReport extends Model
{
    use HasImmutableKey;

    public const CATEGORIES = [
        'STRUCTURAL',
        'ARCHITECTURAL',
        'MEP',
        'MATERIAL',
        'WORKMANSHIP',
        'SAFETY_QUALITY',
    ];

    public const SEVERITIES = ['MINOR', 'MAJOR', 'CRITICAL'];

    public const STATUSES = [
        'OPEN',
        'RECTIFICATION_SUBMITTED',
        'REJECTED_RECTIFICATION',
        'CLOSED',
    ];

    public const UNCLOSED_STATUSES = [
        'OPEN',
        'RECTIFICATION_SUBMITTED',
        'REJECTED_RECTIFICATION',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'ncr_number',
        'reference_no',
        'title',
        'description',
        'category',
        'severity',
        'status',
        'hold_payment',
        'location_zone',
        'root_cause',
        'corrective_action',
        'preventive_action',
        'rectification_submitted_by',
        'rectification_submitted_at',
        'verified_by',
        'verified_at',
        'verification_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ncr_number' => 'integer',
            'hold_payment' => 'boolean',
            'rectification_submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function blocksPayment(): bool
    {
        return (bool) $this->hold_payment
            && in_array($this->status, self::UNCLOSED_STATUSES, true);
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

    public function rectifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rectification_submitted_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
