<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HseSafetyLog extends Model
{
    use HasImmutableKey;

    public const RECORD_TYPES = [
        'TOOLBOX_TALK',
        'NEAR_MISS',
        'FIRST_AID',
        'LOST_TIME_INJURY',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'log_date',
        'record_type',
        'title',
        'description',
        'attendees_count',
        'lost_time_hours',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'attendees_count' => 'integer',
            'lost_time_hours' => 'decimal:2',
        ];
    }

    public function isLti(): bool
    {
        return $this->record_type === 'LOST_TIME_INJURY';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
