<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Models\VariationOrder;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariationOrderService
{
    public const VERSION = 'CED_VO_WORKFLOW_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @param  array{title: string, description?: string|null, reason?: string|null, type: string, cost_amount: float|int|string, time_impact_days?: int|string|null}  $data
     */
    public function createRequest(Project $project, User $creator, array $data): VariationOrder
    {
        return DB::transaction(function () use ($project, $creator, $data): VariationOrder {
            if (! $this->access->allows($creator, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $type = strtoupper((string) $data['type']);
            if (! in_array($type, VariationOrder::TYPES, true)) {
                throw new DomainException('Variation type must be ADDITION or OMISSION.');
            }

            // Cost and time are entered as magnitudes; the type determines the sign.
            $sign = $type === 'OMISSION' ? -1 : 1;
            $cost = round(abs((float) $data['cost_amount']) * $sign, 2);
            $days = abs((int) ($data['time_impact_days'] ?? 0)) * $sign;

            $last = VariationOrder::query()
                ->where('project_id', $project->id)
                ->orderByDesc('vo_number')
                ->first();
            $number = ($last?->vo_number ?? 0) + 1;
            $reference = sprintf('VO-%s-%03d', $project->code, $number);

            $vo = VariationOrder::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'vo_number' => $number,
                'reference_no' => $reference,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'reason' => $data['reason'] ?? 'CLIENT_REQUEST',
                'type' => $type,
                'status' => 'SUBMITTED',
                'cost_impact' => $cost,
                'time_impact_days' => $days,
                'created_by' => $creator->id,
            ]);

            $this->audit->record($project->id, $creator, 'vo.submitted', $vo, [
                'reference_no' => $reference,
                'cost_impact' => $cost,
                'time_impact_days' => $days,
            ]);

            return $vo;
        });
    }

    public function verifyBySiteEngineer(VariationOrder $vo, User $actor, ?string $notes = null): VariationOrder
    {
        return DB::transaction(function () use ($vo, $actor, $notes): VariationOrder {
            $this->requireRole($actor, $vo->project, ['SITE_SUPERVISOR', 'PROJECT_MANAGER', 'EXECUTIVE']);
            $this->requireStatus($vo, 'SUBMITTED');

            $vo->forceFill([
                'status' => 'VERIFIED_SITE',
                'site_engineer_id' => $actor->id,
                'site_verified_at' => now('UTC'),
                'site_notes' => $notes ?: 'Site condition and scope of variation confirmed.',
            ])->save();

            $this->audit->record($vo->project_id, $actor, 'vo.site_verified', $vo, ['status' => 'VERIFIED_SITE']);

            return $vo;
        });
    }

    public function verifyByQuantitySurveyor(VariationOrder $vo, User $actor, ?string $notes = null): VariationOrder
    {
        return DB::transaction(function () use ($vo, $actor, $notes): VariationOrder {
            $this->requireRole($actor, $vo->project, ['PROJECT_MANAGER', 'EXECUTIVE']);
            $this->requireStatus($vo, 'VERIFIED_SITE');

            $vo->forceFill([
                'status' => 'VERIFIED_QS',
                'quantity_surveyor_id' => $actor->id,
                'qs_verified_at' => now('UTC'),
                'qs_notes' => $notes ?: 'Unit rates and cost impact validated against the BOQ.',
            ])->save();

            $this->audit->record($vo->project_id, $actor, 'vo.qs_verified', $vo, ['status' => 'VERIFIED_QS']);

            return $vo;
        });
    }

    /**
     * Level 3: final approval. Applies cost impact to the project budget (new BAC)
     * and time impact to the baseline finish date, atomically.
     */
    public function approveByDirector(VariationOrder $vo, User $actor, ?string $notes = null): VariationOrder
    {
        return DB::transaction(function () use ($vo, $actor, $notes): VariationOrder {
            $this->requireRole($actor, $vo->project, ['EXECUTIVE']);
            $this->requireStatus($vo, 'VERIFIED_QS');

            if ((int) $vo->created_by === (int) $actor->id) {
                throw new DomainException('Maker-checker compliance violation: the requester of a variation cannot give final approval.');
            }

            $project = Project::query()->lockForUpdate()->findOrFail($vo->project_id);

            $previousBudget = (float) $project->budget_amount;
            $newBudget = round($previousBudget + (float) $vo->cost_impact, 2);
            if ($newBudget < 0) {
                throw new DomainException('Variation would reduce the project budget below zero.');
            }

            $previousFinish = CarbonImmutable::parse($project->baseline_finish_date)->startOfDay();
            $newFinish = $previousFinish->addDays((int) $vo->time_impact_days);

            $project->forceFill([
                'budget_amount' => $newBudget,
                'baseline_finish_date' => $newFinish->toDateString(),
            ])->save();

            $vo->forceFill([
                'status' => 'APPROVED',
                'project_director_id' => $actor->id,
                'director_approved_at' => now('UTC'),
                'director_notes' => $notes ?: 'Variation approved and applied to the contract baseline.',
                'previous_budget_amount' => $previousBudget,
                'new_budget_amount' => $newBudget,
                'previous_finish_date' => $previousFinish->toDateString(),
                'new_finish_date' => $newFinish->toDateString(),
            ])->save();

            $this->audit->record($vo->project_id, $actor, 'vo.approved', $vo, [
                'status' => 'APPROVED',
                'previous_budget_amount' => $previousBudget,
                'new_budget_amount' => $newBudget,
                'previous_finish_date' => $previousFinish->toDateString(),
                'new_finish_date' => $newFinish->toDateString(),
            ]);

            return $vo;
        });
    }

    public function reject(VariationOrder $vo, User $actor, string $reason): VariationOrder
    {
        return DB::transaction(function () use ($vo, $actor, $reason): VariationOrder {
            $this->requireRole($actor, $vo->project, ['SITE_SUPERVISOR', 'PROJECT_MANAGER', 'EXECUTIVE']);

            if (in_array($vo->status, ['APPROVED', 'REJECTED'], true)) {
                throw new DomainException("Cannot reject a variation in status {$vo->status}.");
            }

            $vo->forceFill(['status' => 'REJECTED', 'rejection_reason' => $reason])->save();

            $this->audit->record($vo->project_id, $actor, 'vo.rejected', $vo, ['reason' => $reason]);

            return $vo;
        });
    }

    private function requireStatus(VariationOrder $vo, string $expected): void
    {
        if ($vo->status !== $expected) {
            throw new DomainException("Variation order must be {$expected}; current status is {$vo->status}.");
        }
    }

    /**
     * @param  list<string>  $roles
     */
    private function requireRole(User $user, Project $project, array $roles): void
    {
        if (! $this->access->allows($user, $project, $roles)) {
            throw new AuthorizationException('User role is insufficient for this variation approval level.');
        }
    }
}
