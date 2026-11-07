<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitToWork extends Model
{
    use HasImmutableKey;

    public const TYPES = [
        'HOT_WORK',
        'WORKING_AT_HEIGHT',
        'CONFINED_SPACE',
        'HEAVY_LIFTING',
        'EXCAVATION_SHORING',
        'ELECTRICAL_ISOLATION',
    ];

    public const STATUSES = [
        'REQUESTED',
        'APPROVED',
        'REVOKED',
        'EXPIRED',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'constraint_id',
        'ptw_number',
        'reference_no',
        'permit_type',
        'work_title',
        'location_zone',
        'valid_from',
        'valid_until',
        'precautions_checklist',
        'status',
        'approved_by',
        'approved_at',
        'safety_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ptw_number' => 'integer',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WbsTask::class, 'wbs_task_id');
    }

    public function constraint(): BelongsTo
    {
        return $this->belongsTo(ProjectConstraint::class, 'constraint_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
