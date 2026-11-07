<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectConstraint extends Model
{
    use HasImmutableKey;

    public const TYPE_MATERIAL = 'MATERIAL';

    public const TYPE_DESIGN_RFI = 'DESIGN_RFI';

    public const TYPE_INSPECTION = 'INSPECTION';

    public const TYPE_LABOR_CREW = 'LABOR_CREW';

    public const TYPE_SITE_ACCESS = 'SITE_ACCESS';

    public const TYPE_WEATHER = 'WEATHER';

    public const TYPE_EQUIPMENT = 'EQUIPMENT';

    public const TYPE_PERMIT = 'PERMIT';

    public const TYPE_OTHER = 'OTHER';

    public const TYPES = [
        self::TYPE_MATERIAL,
        self::TYPE_DESIGN_RFI,
        self::TYPE_INSPECTION,
        self::TYPE_LABOR_CREW,
        self::TYPE_SITE_ACCESS,
        self::TYPE_WEATHER,
        self::TYPE_EQUIPMENT,
        self::TYPE_PERMIT,
        self::TYPE_OTHER,
    ];

    public const SEVERITY_CRITICAL = 'CRITICAL';

    public const SEVERITY_HIGH = 'HIGH';

    public const SEVERITY_MEDIUM = 'MEDIUM';

    public const SEVERITY_LOW = 'LOW';

    public const SEVERITIES = [
        self::SEVERITY_CRITICAL,
        self::SEVERITY_HIGH,
        self::SEVERITY_MEDIUM,
        self::SEVERITY_LOW,
    ];

    public const STATUS_OPEN = 'OPEN';

    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    public const STATUS_RESOLVED = 'RESOLVED';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_RESOLVED,
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'task_id',
        'type',
        'title',
        'description',
        'severity',
        'status',
        'target_resolution_date',
        'resolved_at',
        'resolution_notes',
        'assigned_to',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_resolution_date' => 'date',
            'resolved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WbsTask::class, 'task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['OPEN', 'IN_PROGRESS']);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('severity', 'CRITICAL');
    }
}
