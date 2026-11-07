<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineeringRfi extends Model
{
    use HasImmutableKey;

    public const DISCIPLINES = [
        'STRUCTURAL',
        'ARCHITECTURAL',
        'MEP',
        'GEOTECHNICAL',
        'CIVIL_UTILITIES',
    ];

    public const STATUSES = ['OPEN', 'CLOSED'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'constraint_id',
        'rfi_number',
        'reference_no',
        'discipline',
        'subject',
        'drawing_ref',
        'question',
        'proposed_solution',
        'due_date',
        'status',
        'official_response',
        'answered_by',
        'answered_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'rfi_number' => 'integer',
            'due_date' => 'date',
            'answered_at' => 'datetime',
        ];
    }

    public function isOverdue(?CarbonImmutable $asOf = null): bool
    {
        if ($this->status !== 'OPEN') {
            return false;
        }

        $today = ($asOf ?? CarbonImmutable::now())->startOfDay();

        return CarbonImmutable::parse($this->due_date)->startOfDay()->lessThan($today);
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

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
