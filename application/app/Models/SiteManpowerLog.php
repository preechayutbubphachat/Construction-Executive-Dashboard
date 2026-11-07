<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteManpowerLog extends Model
{
    use HasImmutableKey;

    public const TRADES = [
        'STEEL_FIXER',
        'CARPENTER',
        'CONCRETE_MASON',
        'WELDER',
        'MEP_TECHNICIAN',
        'SCAFFOLDER',
        'SUPERVISOR_ENGINEER',
        'GENERAL_LABOR',
    ];

    public const SHIFTS = ['DAY', 'NIGHT'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'constraint_id',
        'log_date',
        'trade',
        'contractor_company',
        'planned_headcount',
        'actual_headcount',
        'working_hours',
        'total_man_hours',
        'shift',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'planned_headcount' => 'integer',
            'actual_headcount' => 'integer',
            'working_hours' => 'decimal:2',
            'total_man_hours' => 'decimal:2',
        ];
    }

    public function attendancePercent(): float
    {
        $planned = (int) $this->planned_headcount;
        if ($planned <= 0) {
            return 100.0;
        }

        return round(((int) $this->actual_headcount / $planned) * 100, 1);
    }

    public function meetsTarget(): bool
    {
        return (int) $this->actual_headcount > 0
            && (int) $this->actual_headcount >= (int) $this->planned_headcount;
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
