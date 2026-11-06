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
        'storage_disk',
        'storage_path',
        'original_name',
        'mime_type',
        'byte_size',
        'sha256',
        'access_class',
        'review_state',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'byte_size' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
