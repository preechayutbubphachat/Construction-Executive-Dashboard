<?php

namespace App\Services;

use App\Models\MaterialRequisition;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MaterialProcurementService
{
    public const VERSION = 'CED_PROCUREMENT_V1';

    public function __construct(
        private readonly ProjectAccess $access,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * @param  array{
     *     wbs_task_id?: string|null,
     *     constraint_id?: string|null,
     *     material_name: string,
     *     category?: string|null,
     *     unit?: string|null,
     *     required_qty: float|int|string,
     *     unit_cost?: float|int|string|null,
     *     supplier_name?: string|null,
     *     required_on_site_date: string
     * }  $data
     */
    public function createRequisition(Project $project, User $requester, array $data): MaterialRequisition
    {
        return DB::transaction(function () use ($project, $requester, $data): MaterialRequisition {
            if (! $this->access->allows($requester, $project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            $category = strtoupper((string) ($data['category'] ?? 'STEEL_REBAR'));
            if (! in_array($category, MaterialRequisition::CATEGORIES, true)) {
                throw new DomainException("Unsupported material category {$category}.");
            }

            $requiredQty = round((float) $data['required_qty'], 2);
            if ($requiredQty <= 0) {
                throw new DomainException('Required material quantity must be greater than zero.');
            }

            $unitCost = round(max(0.0, (float) ($data['unit_cost'] ?? 0.00)), 2);
            $totalEstimated = round($requiredQty * $unitCost, 2);

            $taskId = ! empty($data['wbs_task_id']) ? (string) $data['wbs_task_id'] : null;
            if ($taskId !== null) {
                $task = WbsTask::query()->where('project_id', $project->id)->find($taskId);
                if ($task === null) {
                    throw new DomainException('Selected WBS task does not belong to this project.');
                }
            }

            $constraintId = ! empty($data['constraint_id']) ? (string) $data['constraint_id'] : null;
            if ($constraintId !== null) {
                $constraint = ProjectConstraint::query()->where('project_id', $project->id)->find($constraintId);
                if ($constraint === null) {
                    throw new DomainException('Selected constraint does not belong to this project.');
                }
            }

            $last = MaterialRequisition::query()
                ->where('project_id', $project->id)
                ->orderByDesc('mr_number')
                ->first();
            $number = ($last?->mr_number ?? 0) + 1;
            $reference = sprintf('MR-%s-%03d', $project->code, $number);

            $mr = MaterialRequisition::query()->create([
                'id' => (string) Str::ulid(),
                'project_id' => $project->id,
                'wbs_task_id' => $taskId,
                'constraint_id' => $constraintId,
                'mr_number' => $number,
                'reference_no' => $reference,
                'material_name' => $data['material_name'],
                'category' => $category,
                'unit' => strtoupper((string) ($data['unit'] ?? 'TON')),
                'required_qty' => $requiredQty,
                'delivered_qty' => 0.00,
                'unit_cost' => $unitCost,
                'total_estimated_cost' => $totalEstimated,
                'supplier_name' => $data['supplier_name'] ?? null,
                'required_on_site_date' => $data['required_on_site_date'],
                'status' => 'REQUESTED',
                'created_by' => $requester->id,
            ]);

            $this->audit->record($project->id, $requester, 'procurement.mr_created', $mr, [
                'reference_no' => $reference,
                'material_name' => $mr->material_name,
                'required_qty' => $requiredQty,
                'total_estimated_cost' => $totalEstimated,
            ]);

            return $mr;
        });
    }

    public function issuePurchaseOrder(
        MaterialRequisition $mr,
        User $actor,
        string $poNumber,
        ?string $supplierName = null,
    ): MaterialRequisition {
        return DB::transaction(function () use ($mr, $actor, $poNumber, $supplierName): MaterialRequisition {
            if (! $this->access->allows($mr->project ? $actor : $actor, $mr->project, ['PROJECT_MANAGER', 'EXECUTIVE'])) {
                throw new AuthorizationException('Only Project Manager or Executive can issue a Purchase Order.');
            }

            if ($mr->status !== 'REQUESTED') {
                throw new DomainException("Purchase Order can only be issued from REQUESTED status; current status is {$mr->status}.");
            }

            $mr->forceFill([
                'status' => 'PO_ISSUED',
                'po_number' => $poNumber,
                'supplier_name' => $supplierName ?: $mr->supplier_name,
                'approved_by' => $actor->id,
                'approved_at' => now('UTC'),
            ])->save();

            $this->audit->record($mr->project_id, $actor, 'procurement.po_issued', $mr, [
                'po_number' => $poNumber,
                'supplier_name' => $mr->supplier_name,
            ]);

            return $mr;
        });
    }

    /**
     * Records site delivery & quality inspection.
     * When full required quantity is delivered and passes QC, automatically resolves linked open MATERIAL constraints.
     *
     * @param  array{
     *     received_qty: float|int|string,
     *     delivery_order_no: string,
     *     mill_cert_ref?: string|null,
     *     inspection_notes?: string|null,
     *     qc_passed?: bool|int|string|null
     * }  $data
     * @return array{requisition: MaterialRequisition, resolved_constraints_count: int}
     */
    public function recordSiteDelivery(MaterialRequisition $mr, User $inspector, array $data): array
    {
        return DB::transaction(function () use ($mr, $inspector, $data): array {
            if (! $this->access->allows($inspector, $mr->project)) {
                throw new AuthorizationException('User is not an authorized project member.');
            }

            if ($mr->status === 'DELIVERED') {
                throw new DomainException('This material requisition has already been fully delivered.');
            }

            $qcPassed = ! array_key_exists('qc_passed', $data)
                || $data['qc_passed'] === null
                || filter_var($data['qc_passed'], FILTER_VALIDATE_BOOLEAN);

            if (! $qcPassed) {
                $mr->forceFill([
                    'status' => 'REJECTED_DELIVERY',
                    'delivery_order_no' => $data['delivery_order_no'],
                    'mill_cert_ref' => $data['mill_cert_ref'] ?? null,
                    'inspection_notes' => $data['inspection_notes'] ?? 'Material delivery rejected at site QC inspection.',
                    'received_by' => $inspector->id,
                    'received_at' => now('UTC'),
                ])->save();

                $this->audit->record($mr->project_id, $inspector, 'procurement.delivery_rejected', $mr, [
                    'delivery_order_no' => $data['delivery_order_no'],
                ]);

                return ['requisition' => $mr, 'resolved_constraints_count' => 0];
            }

            $receivedQty = round((float) $data['received_qty'], 2);
            if ($receivedQty <= 0) {
                throw new DomainException('Received quantity must be greater than zero.');
            }

            $newDeliveredTotal = round((float) $mr->delivered_qty + $receivedQty, 2);
            $newStatus = $newDeliveredTotal >= (float) $mr->required_qty ? 'DELIVERED' : 'PARTIALLY_DELIVERED';

            $mr->forceFill([
                'delivered_qty' => $newDeliveredTotal,
                'status' => $newStatus,
                'delivery_order_no' => $data['delivery_order_no'],
                'mill_cert_ref' => $data['mill_cert_ref'] ?? $mr->mill_cert_ref,
                'inspection_notes' => $data['inspection_notes'] ?? 'Site delivery verified and accepted.',
                'received_by' => $inspector->id,
                'received_at' => now('UTC'),
            ])->save();

            $resolvedCount = 0;
            if ($newStatus === 'DELIVERED') {
                $resolvedCount = $this->resolveLinkedMaterialConstraints($mr, $inspector);
            }

            $this->audit->record($mr->project_id, $inspector, 'procurement.delivery_accepted', $mr, [
                'status' => $newStatus,
                'received_qty' => $receivedQty,
                'delivered_qty' => $newDeliveredTotal,
                'resolved_constraints_count' => $resolvedCount,
            ]);

            return [
                'requisition' => $mr,
                'resolved_constraints_count' => $resolvedCount,
            ];
        });
    }

    private function resolveLinkedMaterialConstraints(MaterialRequisition $mr, User $actor): int
    {
        if ($mr->constraint_id === null && $mr->wbs_task_id === null) {
            return 0;
        }

        $query = ProjectConstraint::query()
            ->where('project_id', $mr->project_id)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->where(function ($q) use ($mr): void {
                if ($mr->constraint_id !== null) {
                    $q->orWhere('id', $mr->constraint_id);
                }
                if ($mr->wbs_task_id !== null) {
                    $q->orWhere(function ($sub) use ($mr): void {
                        $sub->where('task_id', $mr->wbs_task_id)
                            ->where('type', ProjectConstraint::TYPE_MATERIAL);
                    });
                }
            });

        $constraints = $query->get();
        foreach ($constraints as $constraint) {
            $constraint->forceFill([
                'status' => ProjectConstraint::STATUS_RESOLVED,
                'resolved_at' => now('UTC'),
                'resolution_notes' => sprintf(
                    'Auto-resolved upon full site delivery of %s (%s %s %s, DO: %s).',
                    $mr->reference_no,
                    number_format((float) $mr->delivered_qty, 2),
                    $mr->unit,
                    $mr->material_name,
                    $mr->delivery_order_no ?? '-'
                ),
            ])->save();

            $this->audit->record($mr->project_id, $actor, 'constraint.auto_resolved_by_procurement', $constraint, [
                'material_requisition_id' => $mr->id,
                'reference_no' => $mr->reference_no,
            ]);
        }

        return $constraints->count();
    }
}
