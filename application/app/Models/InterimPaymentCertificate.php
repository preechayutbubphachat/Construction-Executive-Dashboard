<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterimPaymentCertificate extends Model
{
    use HasImmutableKey;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'certificate_number',
        'reference_no',
        'contractor_name',
        'contractor_invoice_no',
        'period_start',
        'period_end',
        'status',
        'gross_work_amount',
        'previous_certified_amount',
        'current_work_amount',
        'retention_rate_percent',
        'retention_deduction',
        'advance_recovery_deduction',
        'other_deductions',
        'net_amount_before_vat',
        'vat_rate_percent',
        'vat_amount',
        'net_payable_amount',
        'site_engineer_id',
        'site_verified_at',
        'site_notes',
        'quantity_surveyor_id',
        'qs_verified_at',
        'qs_notes',
        'project_director_id',
        'director_approved_at',
        'director_notes',
        'finance_transaction_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'certificate_number' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'gross_work_amount' => 'decimal:2',
            'previous_certified_amount' => 'decimal:2',
            'current_work_amount' => 'decimal:2',
            'retention_rate_percent' => 'decimal:2',
            'retention_deduction' => 'decimal:2',
            'advance_recovery_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'net_amount_before_vat' => 'decimal:2',
            'vat_rate_percent' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'net_payable_amount' => 'decimal:2',
            'site_verified_at' => 'datetime',
            'qs_verified_at' => 'datetime',
            'director_approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(IpcItem::class, 'ipc_id');
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

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
