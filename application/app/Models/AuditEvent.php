<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use App\Models\Concerns\IsAppendOnly;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    use HasImmutableKey, IsAppendOnly;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'actor_id',
        'action',
        'auditable_type',
        'auditable_id',
        'summary',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
