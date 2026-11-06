<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use App\Models\Concerns\IsAppendOnly;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressEntry extends Model
{
    use HasImmutableKey, IsAppendOnly;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'task_id',
        'progress_date',
        'progress_percent',
        'recorded_by',
        'recorded_at',
        'note',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entry): void {
            $task = WbsTask::query()->find($entry->task_id);
            if ($task === null || (string) $task->project_id !== (string) $entry->project_id) {
                throw new DomainException('Progress history must reference a task in the same project.');
            }

            if ($task->children()->exists() || ! $task->is_baseline) {
                throw new DomainException('Progress history can only be recorded against baseline leaf tasks.');
            }

            if ($task->status === 'CANCELLED') {
                throw new DomainException('Progress cannot be recorded against a cancelled task.');
            }

            if ((float) $entry->progress_percent < 0 || (float) $entry->progress_percent > 100) {
                throw new DomainException('Progress percent must be between zero and 100.');
            }

            $project = Project::query()->find($entry->project_id);
            if ($project === null || $project->status === 'ARCHIVED') {
                throw new DomainException('Progress cannot be recorded for a missing or archived project.');
            }

            $isProjectMember = $project->members()->whereKey($entry->recorded_by)->exists();
            if (! $isProjectMember) {
                throw new DomainException('Progress recorder must be a member of the task project.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'progress_date' => 'date',
            'progress_percent' => 'decimal:2',
            'recorded_at' => 'immutable_datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WbsTask::class, 'task_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
