<?php

namespace App\Models;

use App\Domain\Finance\FinanceStatus;
use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class FinanceTransaction extends Model
{
    use HasImmutableKey;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'direction',
        'description',
        'amount',
        'status',
        'created_by',
        'approved_by',
        'submitted_at',
        'approved_at',
        'posted_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $transaction): void {
            if ($transaction->approved_by !== null
                && (int) $transaction->created_by === (int) $transaction->approved_by) {
                throw new LogicException('Maker-checker violation: created_by must differ from approved_by.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => FinanceStatus::class,
            'submitted_at' => 'immutable_datetime',
            'approved_at' => 'immutable_datetime',
            'posted_at' => 'immutable_datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
