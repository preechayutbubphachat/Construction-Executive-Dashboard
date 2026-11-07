<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpcItem extends Model
{
    use HasImmutableKey;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'ipc_id',
        'wbs_task_id',
        'description',
        'contract_amount',
        'previous_percent',
        'current_percent',
        'this_period_percent',
        'claimed_amount',
        'certified_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'contract_amount' => 'decimal:2',
            'previous_percent' => 'decimal:2',
            'current_percent' => 'decimal:2',
            'this_period_percent' => 'decimal:2',
            'claimed_amount' => 'decimal:2',
            'certified_amount' => 'decimal:2',
        ];
    }

    public function ipc(): BelongsTo
    {
        return $this->belongsTo(InterimPaymentCertificate::class, 'ipc_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WbsTask::class, 'wbs_task_id');
    }
}
