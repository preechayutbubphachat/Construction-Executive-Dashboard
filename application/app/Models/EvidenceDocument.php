<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceDocument extends Model
{
    use HasImmutableKey;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'wbs_task_id',
        'constraint_id',
        'storage_disk',
        'storage_path',
        'original_name',
        'caption',
        'gps_latitude',
        'gps_longitude',
        'captured_at',
        'mime_type',
        'byte_size',
        'sha256',
        'access_class',
        'review_state',
        'metadata',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'byte_size' => 'integer',
            'gps_latitude' => 'float',
            'gps_longitude' => 'float',
            'captured_at' => 'datetime',
            'metadata' => 'array',
        ];
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeForTask($query, string $taskId)
    {
        return $query->where('wbs_task_id', $taskId);
    }

    public function scopeForConstraint($query, string $constraintId)
    {
        return $query->where('constraint_id', $constraintId);
    }

    public function scopeSitePhotos($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('wbs_task_id')
                ->orWhereNotNull('constraint_id')
                ->orWhereNotNull('gps_latitude')
                ->orWhereJsonContains('metadata->category', 'PROGRESS')
                ->orWhereJsonContains('metadata->category', 'DEFECT')
                ->orWhereJsonContains('metadata->category', 'QUALITY_CHECK')
                ->orWhereJsonContains('metadata->category', 'SAFETY');
        });
    }
}
