<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteMachineryLog extends Model
{
    use HasImmutableKey;

    public const CATEGORIES = [
        'TOWER_CRANE',
        'MOBILE_CRANE',
        'EXCAVATOR',
        'PILING_RIG',
        'CONCRETE_PUMP',
        'GENERATOR_COMPRESSOR',
        'TRUCK_HAULER',
    ];

    public const STATUSES = [
        'OPERATIONAL',
        'MAINTENANCE',
        'BREAKDOWN',
        'STANDBY',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'constraint_id',
        'log_date',
        'equipment_code',
        'equipment_name',
        'category',
        'status',
        'operating_hours',
        'downtime_hours',
        'operator_name',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'operating_hours' => 'decimal:2',
            'downtime_hours' => 'decimal:2',
        ];
    }

    public function availabilityPercent(): float
    {
        $op = (float) $this->operating_hours;
        $down = (float) $this->downtime_hours;
        $total = $op + $down;

        if ($total <= 0.0) {
            return $this->status === 'OPERATIONAL' ? 100.0 : 0.0;
        }

        return round(($op / $total) * 100, 1);
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
}
