<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequisition extends Model
{
    use HasImmutableKey;

    public const CATEGORIES = [
        'STEEL_REBAR',
        'CONCRETE',
        'PRECAST',
        'STRUCTURAL_STEEL',
        'MEP_EQUIPMENT',
        'ARCHITECTURAL',
        'GENERAL',
    ];

    public const STATUSES = [
        'REQUESTED',
        'PO_ISSUED',
        'PARTIALLY_DELIVERED',
        'DELIVERED',
        'REJECTED_DELIVERY',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'constraint_id',
        'mr_number',
        'reference_no',
        'material_name',
        'category',
        'unit',
        'required_qty',
        'delivered_qty',
        'unit_cost',
        'total_estimated_cost',
        'supplier_name',
        'po_number',
        'required_on_site_date',
        'status',
        'delivery_order_no',
        'mill_cert_ref',
        'inspection_notes',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mr_number' => 'integer',
            'required_qty' => 'decimal:2',
            'delivered_qty' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_estimated_cost' => 'decimal:2',
            'required_on_site_date' => 'date',
            'approved_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function shortageQty(): float
    {
        return max(0.0, round((float) $this->required_qty - (float) $this->delivered_qty, 2));
    }

    public function fulfillmentPercent(): float
    {
        $req = (float) $this->required_qty;
        if ($req <= 0.0) {
            return 100.0;
        }

        return min(100.0, round(((float) $this->delivered_qty / $req) * 100, 1));
    }

    /**
     * Returns true if this material is still short and needed on site within the next 7 days (or overdue).
     */
    public function isLookaheadShortageRisk(?CarbonImmutable $asOf = null): bool
    {
        if ($this->status === 'DELIVERED' || $this->shortageQty() <= 0.0) {
            return false;
        }

        $today = ($asOf ?? CarbonImmutable::now())->startOfDay();
        $horizon = $today->addDays(7);
        $neededDate = CarbonImmutable::parse($this->required_on_site_date)->startOfDay();

        return $neededDate->lessThanOrEqualTo($horizon);
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

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
