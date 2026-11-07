<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Livewire\FinanceTransactionWorkflow;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\User;
use App\Services\CedFinanceV1ReadModel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceTransactionSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_member_can_create_a_server_identified_draft(): void
    {
        [$project, $maker] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($maker);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->set('direction', 'EXPENSE')
            ->set('description', 'Concrete payment')
            ->set('amount', '250.00')
            ->call('createDraft')
            ->assertHasNoErrors()
            ->assertSee('Draft transaction created.');

        $transaction = FinanceTransaction::query()->firstOrFail();
        $this->assertSame($project->id, $transaction->project_id);
        $this->assertSame($maker->id, $transaction->created_by);
        $this->assertSame(FinanceStatus::Draft, $transaction->status);
        $this->assertNotSame('', $transaction->id);
    }

    public function test_invalid_draft_input_is_rejected_without_mutation(): void
    {
        [$project, $maker] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($maker);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->set('direction', 'INVALID')
            ->set('description', '')
            ->set('amount', '0')
            ->call('createDraft')
            ->assertHasErrors(['direction', 'description', 'amount']);

        $this->assertDatabaseCount('finance_transactions', 0);
    }

    public function test_submit_approve_does_not_auto_post_and_explicit_post_is_required(): void
    {
        [$project, $maker] = $this->projectWithMember('FINANCE_APPROVER');
        $approver = User::factory()->create();
        $project->members()->attach($approver, ['role' => 'FINANCE_APPROVER']);
        $this->actingAs($maker);
        $component = Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->set('direction', 'INCOME')
            ->set('description', 'Certified payment')
            ->set('amount', '10.00')
            ->call('createDraft');
        $transaction = FinanceTransaction::query()->firstOrFail();
        $component->call('submit', $transaction->id);
        $this->assertSame(FinanceStatus::Submitted, $transaction->fresh()->status);

        $this->actingAs($approver);
        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->call('approve', $transaction->id)
            ->assertSee('Transaction approved.');
        $this->assertSame(FinanceStatus::Approved, $transaction->fresh()->status);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->call('post', $transaction->id)
            ->assertSee('Transaction posted.');
        $this->assertSame(FinanceStatus::Posted, $transaction->fresh()->status);
    }

    public function test_maker_cannot_approve_and_repeated_transition_fails_closed(): void
    {
        [$project, $maker] = $this->projectWithMember('FINANCE_APPROVER');
        $transaction = FinanceTransaction::query()->create([
            'id' => (string) str()->uuid(),
            'project_id' => $project->id,
            'direction' => 'EXPENSE',
            'description' => 'Pending item',
            'amount' => '12.00',
            'status' => FinanceStatus::Submitted,
            'created_by' => $maker->id,
        ]);
        $this->actingAs($maker);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->call('approve', $transaction->id)
            ->assertHasErrors('workflow');

        $denied = User::factory()->create();
        $this->actingAs($denied);
        $this->get(route('projects.finance', $project))->assertForbidden();
    }

    public function test_rejection_is_explicitly_out_of_scope_and_absent_from_rm06(): void
    {
        [$project, $maker] = $this->projectWithMember('PROJECT_MEMBER');
        $this->actingAs($maker);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->assertDontSee('Reject')
            ->assertDontSee('rejection');

        $methods = array_map(
            static fn (\ReflectionMethod $method): string => $method->getName(),
            (new \ReflectionClass(FinanceTransactionWorkflow::class))->getMethods()
        );
        $this->assertNotContains('reject', $methods);
        $this->assertNotContains('REJECTED', array_map(
            static fn (FinanceStatus $status): string => $status->name,
            FinanceStatus::cases()
        ));
        $this->assertFalse(Schema::hasColumn('finance_transactions', 'rejection_reason'));
        $this->assertFalse(Schema::hasColumn('finance_transactions', 'rejected_by'));
        $this->assertFalse(Schema::hasColumn('finance_transactions', 'rejected_at'));
    }

    public function test_guest_is_denied_and_posted_totals_ignore_unposted_transactions(): void
    {
        [$project, $maker] = $this->projectWithMember('FINANCE_APPROVER');
        $approver = User::factory()->create();
        $project->members()->attach($approver, ['role' => 'FINANCE_APPROVER']);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])->assertForbidden();

        foreach ([
            ['id' => 'CED-FIN-INCOME', 'direction' => 'INCOME', 'amount' => '100.10', 'status' => FinanceStatus::Posted, 'approved_by' => $approver->id],
            ['id' => 'CED-FIN-EXPENSE', 'direction' => 'EXPENSE', 'amount' => '40.05', 'status' => FinanceStatus::Posted, 'approved_by' => $approver->id],
            ['id' => 'CED-FIN-DRAFT', 'direction' => 'INCOME', 'amount' => '999.99', 'status' => FinanceStatus::Draft, 'approved_by' => null],
        ] as $attributes) {
            FinanceTransaction::query()->create($attributes + [
                'project_id' => $project->id,
                'description' => $attributes['id'],
                'created_by' => $maker->id,
            ]);
        }

        $this->actingAs($maker);
        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->assertSet('postedTotals.income', '100.10')
            ->assertSet('postedTotals.expense', '40.05')
            ->assertSet('postedTotals.net', '60.05')
            ->assertSet('postedTotals.rule_version', 'CED_FINANCE_V1')
            ->assertSee('Posted income')
            ->assertSee('Posted expense')
            ->assertSee('Posted net');
    }

    public function test_posted_totals_expose_deterministic_provenance_for_only_posted_records_in_selected_project(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 15, 8, 26, 0, 'UTC'));
        try {
            [$project, $maker] = $this->projectWithMember('PROJECT_MEMBER');
            [$otherProject, $otherMaker] = $this->projectWithMember('PROJECT_MEMBER');

            foreach ([
                ['id' => 'CED-FIN-POSTED-INCOME', 'project_id' => $project->id, 'direction' => 'INCOME', 'amount' => '100.10', 'status' => FinanceStatus::Posted],
                ['id' => 'CED-FIN-POSTED-EXPENSE', 'project_id' => $project->id, 'direction' => 'EXPENSE', 'amount' => '40.05', 'status' => FinanceStatus::Posted],
                ['id' => 'CED-FIN-DRAFT', 'project_id' => $project->id, 'direction' => 'INCOME', 'amount' => '999.99', 'status' => FinanceStatus::Draft],
                ['id' => 'CED-FIN-WRONG-PROJECT', 'project_id' => $otherProject->id, 'direction' => 'INCOME', 'amount' => '777.77', 'status' => FinanceStatus::Posted],
            ] as $attributes) {
                FinanceTransaction::query()->create($attributes + [
                    'description' => $attributes['id'],
                    'created_by' => $attributes['project_id'] === $project->id ? $maker->id : $otherMaker->id,
                ]);
            }

            $this->actingAs($maker);
            Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
                ->assertSet('postedTotals.rule_version', 'CED_FINANCE_V1')
                ->assertSet('postedTotals.calculated_at', '2026-09-15T08:26:00+00:00')
                ->assertSet('postedTotals.contributing_source_record_scope.project_id', $project->id)
                ->assertSet('postedTotals.contributing_source_record_scope.status', FinanceStatus::Posted->value)
                ->assertSet('postedTotals.contributing_source_record_scope.included_record_ids', [
                    'CED-FIN-POSTED-EXPENSE',
                    'CED-FIN-POSTED-INCOME',
                ])
                ->assertSet('postedTotals.income', '100.10')
                ->assertSet('postedTotals.expense', '40.05')
                ->assertSet('postedTotals.net', '60.05')
                ->assertSet('postedTotals.explanation', 'Posted records in project '.$project->id.' produce income 100.10, expense 40.05, and net 60.05 (income minus expense).')
                ->assertSee('CED_FINANCE_V1')
                ->assertSee('100.10')
                ->assertSee('40.05')
                ->assertSee('60.05');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_transaction_from_another_project_cannot_be_transitioned(): void
    {
        [$project, $maker] = $this->projectWithMember('FINANCE_APPROVER');
        [$otherProject, $otherMaker] = $this->projectWithMember('FINANCE_APPROVER');
        $transaction = FinanceTransaction::query()->create([
            'id' => 'CED-FIN-CROSS',
            'project_id' => $otherProject->id,
            'direction' => 'EXPENSE',
            'description' => 'Other project item',
            'amount' => '12.34',
            'status' => FinanceStatus::Draft,
            'created_by' => $otherMaker->id,
        ]);

        $this->actingAs($maker);
        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->call('submit', $transaction->id)
            ->assertHasErrors('workflow');

        $this->assertSame(FinanceStatus::Draft, $transaction->fresh()->status);
    }

    public function test_public_read_model_matches_rm06_posted_money_and_scope_contract(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 16, 4, 26, 0, 'UTC'));
        try {
            [$project, $maker] = $this->projectWithMember('PROJECT_MEMBER');
            [$otherProject, $otherMaker] = $this->projectWithMember('PROJECT_MEMBER');
            foreach ([
                ['id' => 'RM06-Z-INCOME', 'project_id' => $project->id, 'direction' => 'INCOME', 'amount' => '100.10', 'status' => FinanceStatus::Posted, 'created_by' => $maker->id],
                ['id' => 'RM06-A-EXPENSE', 'project_id' => $project->id, 'direction' => 'EXPENSE', 'amount' => '40.05', 'status' => FinanceStatus::Posted, 'created_by' => $maker->id],
                ['id' => 'RM06-DRAFT', 'project_id' => $project->id, 'direction' => 'INCOME', 'amount' => '900.00', 'status' => FinanceStatus::Draft, 'created_by' => $maker->id],
                ['id' => 'RM06-SUBMITTED', 'project_id' => $project->id, 'direction' => 'EXPENSE', 'amount' => '800.00', 'status' => FinanceStatus::Submitted, 'created_by' => $maker->id],
                ['id' => 'RM06-OTHER', 'project_id' => $otherProject->id, 'direction' => 'INCOME', 'amount' => '700.00', 'status' => FinanceStatus::Posted, 'created_by' => $otherMaker->id],
            ] as $attributes) {
                FinanceTransaction::query()->create($attributes + ['description' => $attributes['id']]);
            }

            $result = app(CedFinanceV1ReadModel::class)->forProject($project);

            $this->assertSame($project->id, $result['project_id']);
            $this->assertSame([
                'rule_version' => 'CED_FINANCE_V1',
                'calculated_at' => '2026-09-16T04:26:00+00:00',
                'project_id' => $project->id,
                'contributing_source_record_scope' => [
                    'project_id' => $project->id,
                    'status' => FinanceStatus::Posted->value,
                    'included_record_ids' => ['RM06-A-EXPENSE', 'RM06-Z-INCOME'],
                ],
                'income' => '100.10',
                'expense' => '40.05',
                'net' => '60.05',
                'explanation' => 'Posted records in project '.$project->id.' produce income 100.10, expense 40.05, and net 60.05 (income minus expense).',
            ], $result);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_public_read_model_is_read_only_and_does_not_audit_or_mutate_sources(): void
    {
        [$project, $maker] = $this->projectWithMember('PROJECT_MEMBER');
        $transaction = FinanceTransaction::query()->create([
            'id' => 'RM06-READ-ONLY',
            'project_id' => $project->id,
            'direction' => 'INCOME',
            'description' => 'Read-only source',
            'amount' => '12.34',
            'status' => FinanceStatus::Posted,
            'created_by' => $maker->id,
        ]);
        $beforeProject = $project->fresh()->getRawOriginal();
        $beforeTransaction = $transaction->fresh()->getRawOriginal();
        $beforeAuditCount = $project->auditEvents()->count();

        app(CedFinanceV1ReadModel::class)->forProject($project->fresh());

        $this->assertSame($beforeProject, $project->fresh()->getRawOriginal());
        $this->assertSame($beforeTransaction, $transaction->fresh()->getRawOriginal());
        $this->assertSame($beforeAuditCount, $project->auditEvents()->count());
    }

    public function test_livewire_render_delegates_finance_totals_without_duplicate_aggregation(): void
    {
        [$project] = $this->projectWithMember('PROJECT_MEMBER');
        $expected = [
            'rule_version' => 'CED_FINANCE_V1',
            'calculated_at' => '2026-09-16T00:00:00+00:00',
            'contributing_source_record_scope' => [
                'project_id' => $project->id,
                'status' => FinanceStatus::Posted->value,
                'included_record_ids' => [],
            ],
            'income' => '0.00',
            'expense' => '0.00',
            'net' => '0.00',
            'explanation' => 'delegated',
        ];
        $service = \Mockery::mock(CedFinanceV1ReadModel::class);
        $service->shouldReceive('forProject')->once()->withArgs(fn (Project $received): bool => $received->is($project))->andReturn($expected);
        $this->app->instance(CedFinanceV1ReadModel::class, $service);
        $this->actingAs($project->members()->first());

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->assertSet('postedTotals', $expected);

        $source = file_get_contents(__DIR__.'/../../app/Livewire/FinanceTransactionWorkflow.php');
        $this->assertStringContainsString('CedFinanceV1ReadModel::class)->forProject($project)', $source);
        $this->assertStringNotContainsString('private function postedTotals', $source);
        $this->assertStringNotContainsString('private function cents', $source);
        $this->assertStringNotContainsString('private function money', $source);
    }

    private function projectWithMember(string $role): array
    {
        $project = Project::query()->create([
            'id' => (string) str()->uuid(),
            'code' => 'CED-'.strtoupper(bin2hex(random_bytes(4))),
            'name' => 'Finance Surface',
            'baseline_start_date' => '2026-04-01',
            'baseline_finish_date' => '2026-04-30',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);
        $user = User::factory()->create();
        $project->members()->attach($user, ['role' => $role]);

        return [$project, $user];
    }
}
