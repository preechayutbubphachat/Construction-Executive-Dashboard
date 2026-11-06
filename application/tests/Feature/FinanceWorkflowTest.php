<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\User;
use App\Services\FinanceWorkflow;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_to_posted_requires_a_distinct_approver_and_creates_audit_events(): void
    {
        CarbonImmutable::setTestNow('2026-04-01T10:00:00Z');
        $project = $this->project();
        $maker = User::factory()->create();
        $approver = User::factory()->create();
        $project->members()->attach($maker, ['role' => 'FINANCE_APPROVER']);
        $project->members()->attach($approver, ['role' => 'FINANCE_APPROVER']);
        $transaction = FinanceTransaction::query()->create([
            'id' => 'CED-FIN-001',
            'project_id' => $project->id,
            'direction' => 'EXPENSE',
            'description' => 'Concrete payment',
            'amount' => '25000.00',
            'status' => FinanceStatus::Draft,
            'created_by' => $maker->id,
        ]);
        $workflow = app(FinanceWorkflow::class);

        $submitted = $workflow->submit($transaction, $maker);
        self::assertSame(FinanceStatus::Submitted, $submitted->status);

        try {
            $workflow->approve($submitted, $maker);
            self::fail('The maker was allowed to approve their own transaction.');
        } catch (DomainException $exception) {
            self::assertStringContainsString('created_by must differ from approved_by', $exception->getMessage());
        }

        $approved = $workflow->approve($submitted, $approver);
        $posted = $workflow->post($approved, $approver);

        self::assertSame(FinanceStatus::Posted, $posted->status);
        self::assertSame($maker->id, $posted->created_by);
        self::assertSame($approver->id, $posted->approved_by);
        self::assertNotSame($posted->created_by, $posted->approved_by);
        $this->assertDatabaseHas('audit_events', ['action' => 'finance.submitted', 'auditable_id' => 'CED-FIN-001']);
        $this->assertDatabaseHas('audit_events', ['action' => 'finance.approved', 'auditable_id' => 'CED-FIN-001']);
        $this->assertDatabaseHas('audit_events', ['action' => 'finance.posted', 'auditable_id' => 'CED-FIN-001']);
        self::assertSame(3, $project->auditEvents()->count());
        CarbonImmutable::setTestNow();
    }

    private function project(): Project
    {
        return Project::query()->create([
            'id' => 'CED-PROJECT-001',
            'code' => 'CED-001',
            'name' => 'Local Slice',
            'baseline_start_date' => '2026-04-01',
            'baseline_finish_date' => '2026-04-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);
    }
}
