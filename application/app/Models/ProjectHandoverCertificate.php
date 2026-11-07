<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectHandoverCertificate extends Model
{
    use HasImmutableKey;

    public const CERTIFICATE_TYPES = [
        'TAKING_OVER_CERTIFICATE',
        'DEFECTS_LIABILITY_CERTIFICATE',
    ];

    public const STATUSES = [
        'ISSUED',
        'RETENTION_RELEASED',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'certificate_number',
        'certificate_type',
        'handover_date',
        'dlp_months',
        'dlp_expiry_date',
        'final_contract_sum',
        'total_approved_vo_amount',
        'total_certified_ipc_amount',
        'retention_held_total',
        'retention_release_amount',
        'status',
        'scope_remarks',
        'prepared_by',
        'certified_by',
        'released_by',
        'certified_at',
        'retention_released_at',
        'finance_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'handover_date' => 'date',
            'dlp_months' => 'integer',
            'dlp_expiry_date' => 'date',
            'final_contract_sum' => 'decimal:2',
            'total_approved_vo_amount' => 'decimal:2',
            'total_certified_ipc_amount' => 'decimal:2',
            'retention_held_total' => 'decimal:2',
            'retention_release_amount' => 'decimal:2',
            'certified_at' => 'immutable_datetime',
            'retention_released_at' => 'immutable_datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function certifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }
}
