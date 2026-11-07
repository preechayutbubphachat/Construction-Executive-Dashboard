<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariationOrder extends Model
{
    use HasImmutableKey;

    public const REASONS = ['CLIENT_REQUEST', 'DESIGN_CHANGE', 'SITE_CONDITION', 'REGULATORY', 'OTHER'];

    public const TYPES = ['ADDITION', 'OMISSION'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'vo_number',
        'reference_no',
        'title',
        'description',
        'reason',
        'type',
        'status',
        'cost_impact',
        'time_impact_days',
        'previous_budget_amount',
        'new_budget_amount',
        'previous_finish_date',
        'new_finish_date',
        'site_engineer_id',
        'site_verified_at',
        'site_notes',
        'quantity_surveyor_id',
        'qs_verified_at',
        'qs_notes',
        'project_director_id',
        'director_approved_at',
        'director_notes',
        'rejection_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'vo_number' => 'integer',
            'cost_impact' => 'decimal:2',
            'time_impact_days' => 'integer',
            'previous_budget_amount' => 'decimal:2',
            'new_budget_amount' => 'decimal:2',
            'previous_finish_date' => 'date',
            'new_finish_date' => 'date',
            'site_verified_at' => 'datetime',
            'qs_verified_at' => 'datetime',
            'director_approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function siteEngineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'site_engineer_id');
    }

    public function quantitySurveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quantity_surveyor_id');
    }

    public function projectDirector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_director_id');
    }
}
