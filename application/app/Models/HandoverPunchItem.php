<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandoverPunchItem extends Model
{
    use HasImmutableKey;

    public const DISCIPLINES = [
        'ARCHITECTURAL',
        'STRUCTURAL',
        'MEP',
        'CIVIL_EXTERNAL',
        'FIRE_SAFETY',
    ];

    public const SEVERITIES = [
        'MINOR',
        'MAJOR',
        'CRITICAL_HANDOVER_BLOCKER',
    ];

    public const STATUSES = [
        'OPEN',
        'RECTIFIED_PENDING_INSPECTION',
        'VERIFIED_CLOSED',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'punch_code',
        'location_zone',
        'discipline',
        'severity',
        'description',
        'rectification_notes',
        'status',
        'reported_by',
        'rectified_by',
        'verified_by',
        'rectified_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'rectified_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function wbsTask(): BelongsTo
    {
        return $this->belongsTo(WbsTask::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function rectifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rectified_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
