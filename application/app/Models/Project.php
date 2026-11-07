<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableKey;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Project extends Model
{
    use HasImmutableKey;

    public const STATUSES = ['DRAFT', 'ACTIVE', 'ON_HOLD', 'COMPLETED', 'ARCHIVED'];

    private const LIFECYCLE_TRANSITIONS = [
        'DRAFT' => ['ACTIVE', 'ARCHIVED'],
        'ACTIVE' => ['ON_HOLD', 'COMPLETED', 'ARCHIVED'],
        'ON_HOLD' => ['ACTIVE', 'COMPLETED', 'ARCHIVED'],
        'COMPLETED' => ['ARCHIVED'],
        'ARCHIVED' => [],
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'code',
        'name',
        'baseline_start_date',
        'baseline_finish_date',
        'budget_amount',
        'status',
        'archived_at',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $project): void {
            $start = CarbonImmutable::parse($project->baseline_start_date)->startOfDay();
            $finish = CarbonImmutable::parse($project->baseline_finish_date)->startOfDay();

            if ($finish->lessThan($start)) {
                throw new DomainException('Project baseline finish date must be on or after its start date.');
            }

            if ((float) $project->budget_amount < 0) {
                throw new DomainException('Project budget amount cannot be negative.');
            }

            if (! in_array($project->status, self::STATUSES, true)) {
                throw new DomainException("Unsupported project status {$project->status}.");
            }

            $isArchived = $project->status === 'ARCHIVED';
            if ($isArchived !== ($project->archived_at !== null)) {
                throw new DomainException('Archived projects require both ARCHIVED status and an archived timestamp.');
            }
        });

        static::updating(function (self $project): void {
            if ($project->isDirty(['id', 'code'])) {
                throw new LogicException('Project id and code are immutable.');
            }

            if ($project->getOriginal('status') === 'ARCHIVED') {
                throw new DomainException('Archived projects are read-only.');
            }

            if ($project->isDirty('status')) {
                $from = (string) $project->getOriginal('status');
                $to = (string) $project->status;

                if (! in_array($to, self::LIFECYCLE_TRANSITIONS[$from] ?? [], true)) {
                    throw new DomainException("Project lifecycle transition {$from} -> {$to} is not allowed.");
                }

                if ($to === 'COMPLETED' && $project->wbsTasks()
                    ->whereNotIn('status', ['COMPLETED', 'CANCELLED'])
                    ->exists()) {
                    throw new DomainException('A project cannot complete while WBS/tasks remain active.');
                }
            }

            if ($project->isDirty(['baseline_start_date', 'baseline_finish_date'])) {
                $start = CarbonImmutable::parse($project->baseline_start_date)->toDateString();
                $finish = CarbonImmutable::parse($project->baseline_finish_date)->toDateString();
                $hasOutOfRangeTasks = $project->wbsTasks()
                    ->where(function ($query) use ($start, $finish): void {
                        $query->whereDate('baseline_start_date', '<', $start)
                            ->orWhereDate('baseline_finish_date', '>', $finish);
                    })
                    ->exists();

                if ($hasOutOfRangeTasks) {
                    throw new DomainException('Project baseline dates must contain every WBS/task baseline.');
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'baseline_start_date' => 'date',
            'baseline_finish_date' => 'date',
            'budget_amount' => 'decimal:2',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function wbsTasks(): HasMany
    {
        return $this->hasMany(WbsTask::class);
    }

    public function progressEntries(): HasMany
    {
        return $this->hasMany(ProgressEntry::class);
    }

    public function financeTransactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function evidenceDocuments(): HasMany
    {
        return $this->hasMany(EvidenceDocument::class);
    }

    public function risks(): HasMany
    {
        return $this->hasMany(ProjectRisk::class);
    }

    public function constraints(): HasMany
    {
        return $this->hasMany(ProjectConstraint::class);
    }

    public function ipcs(): HasMany
    {
        return $this->hasMany(InterimPaymentCertificate::class);
    }

    public function variationOrders(): HasMany
    {
        return $this->hasMany(VariationOrder::class);
    }

    public function ncrs(): HasMany
    {
        return $this->hasMany(NonConformanceReport::class);
    }

    public function materialRequisitions(): HasMany
    {
        return $this->hasMany(MaterialRequisition::class);
    }

    public function manpowerLogs(): HasMany
    {
        return $this->hasMany(SiteManpowerLog::class);
    }

    public function machineryLogs(): HasMany
    {
        return $this->hasMany(SiteMachineryLog::class);
    }

    public function permitToWorks(): HasMany
    {
        return $this->hasMany(PermitToWork::class);
    }

    public function hseSafetyLogs(): HasMany
    {
        return $this->hasMany(HseSafetyLog::class);
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class);
    }
}
