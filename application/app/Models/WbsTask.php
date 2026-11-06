<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class WbsTask extends Model
{
    use HasImmutableKey;

    public const STATUSES = ['NOT_STARTED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];

    private const LIFECYCLE_TRANSITIONS = [
        'NOT_STARTED' => ['IN_PROGRESS', 'CANCELLED'],
        'IN_PROGRESS' => ['COMPLETED', 'CANCELLED'],
        'COMPLETED' => [],
        'CANCELLED' => [],
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'parent_id',
        'wbs_code',
        'name',
        'baseline_start_date',
        'baseline_finish_date',
        'weight',
        'status',
        'is_baseline',
        'is_leaf',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $task): void {
            $task->validateDomainRules();
        });

        static::updating(function (self $task): void {
            if ($task->isDirty(['id', 'project_id', 'wbs_code'])) {
                throw new LogicException('WBS/task id, project assignment, and WBS code are immutable.');
            }

            if ($task->isDirty('status')) {
                $from = (string) $task->getOriginal('status');
                $to = (string) $task->status;

                if (! in_array($to, self::LIFECYCLE_TRANSITIONS[$from] ?? [], true)) {
                    throw new DomainException("WBS/task lifecycle transition {$from} -> {$to} is not allowed.");
                }
            }
        });

        static::deleting(function (): void {
            throw new LogicException('WBS/tasks use lifecycle states and cannot be deleted.');
        });
    }

    protected function casts(): array
    {
        return [
            'baseline_start_date' => 'date',
            'baseline_finish_date' => 'date',
            'weight' => 'decimal:4',
            'is_baseline' => 'boolean',
            'is_leaf' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function progressEntries(): HasMany
    {
        return $this->hasMany(ProgressEntry::class, 'task_id');
    }

    private function validateDomainRules(): void
    {
        $project = Project::query()->find($this->project_id);
        if ($project === null) {
            throw new DomainException('A WBS/task must belong to an existing project.');
        }

        if (in_array($project->status, ['COMPLETED', 'ARCHIVED'], true)) {
            throw new DomainException('Completed or archived projects cannot change their WBS/tasks.');
        }

        if (! in_array($this->status, self::STATUSES, true)) {
            throw new DomainException("Unsupported WBS/task status {$this->status}.");
        }

        $start = CarbonImmutable::parse($this->baseline_start_date)->startOfDay();
        $finish = CarbonImmutable::parse($this->baseline_finish_date)->startOfDay();
        if ($finish->lessThan($start)) {
            throw new DomainException('WBS/task baseline finish date must be on or after its start date.');
        }

        if ($start->lessThan($project->baseline_start_date) || $finish->greaterThan($project->baseline_finish_date)) {
            throw new DomainException('WBS/task baseline dates must be within the project baseline.');
        }

        if ($this->weight !== null && ((float) $this->weight <= 0 || (float) $this->weight > 100)) {
            throw new DomainException('Leaf task weight must be greater than zero and no more than 100.');
        }

        $hasChildren = $this->exists && self::query()->where('parent_id', $this->getKey())->exists();
        if ((bool) $this->is_leaf === $hasChildren) {
            throw new DomainException('WBS/task leaf state must match its adjacency children.');
        }

        if ($hasChildren && $this->weight !== null) {
            throw new DomainException('Non-leaf WBS nodes cannot carry progress weight.');
        }

        $this->validateParent($project);

        if ($this->status === 'COMPLETED' && $hasChildren
            && self::query()->where('parent_id', $this->getKey())->where('status', '!=', 'COMPLETED')->exists()) {
            throw new DomainException('A parent WBS node cannot complete before all child tasks complete.');
        }
    }

    private function validateParent(Project $project): void
    {
        if ($this->parent_id === null) {
            return;
        }

        if ((string) $this->parent_id === (string) $this->getKey()) {
            throw new DomainException('A WBS/task cannot be its own parent.');
        }

        $parent = self::query()->find($this->parent_id);
        if ($parent === null || (string) $parent->project_id !== (string) $project->getKey()) {
            throw new DomainException('A WBS/task parent must belong to the same project.');
        }

        if (in_array($parent->status, ['COMPLETED', 'CANCELLED'], true)) {
            throw new DomainException('Completed or cancelled WBS/tasks cannot receive children.');
        }

        $start = CarbonImmutable::parse($this->baseline_start_date)->startOfDay();
        $finish = CarbonImmutable::parse($this->baseline_finish_date)->startOfDay();
        if ($start->lessThan($parent->baseline_start_date) || $finish->greaterThan($parent->baseline_finish_date)) {
            throw new DomainException('Child baseline dates must be within the parent WBS baseline.');
        }

        $visited = [];
        while ($parent !== null) {
            $parentId = (string) $parent->getKey();
            if ($parentId === (string) $this->getKey()) {
                throw new DomainException('A WBS/task parent assignment cannot create a cycle.');
            }

            if (isset($visited[$parentId])) {
                throw new DomainException('The existing WBS hierarchy contains a cycle.');
            }

            $visited[$parentId] = true;
            $parent = $parent->parent_id === null ? null : self::query()->find($parent->parent_id);
        }
    }

    public function constraints(): HasMany
    {
        return $this->hasMany(ProjectConstraint::class, 'task_id');
    }
}
