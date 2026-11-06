<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Http\Controllers\EvidenceDownloadController;
use App\Models\AuditEvent;
use App\Models\EvidenceDocument;
use App\Models\FinanceTransaction;
use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\AuditRecorder;
use App\Services\CedExecutiveSourceLinkResolver;
use App\Services\CedProjectHealthV1ReadModel;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorizationEvidenceAndDrillDownTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_project_user_cannot_open_executive_drill_down(): void
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $other = $this->project('CED-PROJECT-002', 'CED-002');
        $user = User::factory()->create();
        $other->members()->attach($user, ['role' => 'EXECUTIVE']);
        $this->actingAs($user)->get(route('projects.executive', $project))->assertForbidden();
    }

    public function test_executive_drill_down_denies_guest_and_unaffiliated_user_by_default(): void
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $this->get(route('projects.executive', $project))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('projects.executive', $project))->assertForbidden();
    }

    public function test_private_evidence_denies_outsider_and_audits_authorized_download(): void
    {
        Storage::fake('evidence');
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        Storage::disk('evidence')->put('CED-PROJECT-001/invoice.pdf', 'private-evidence');
        $evidence = EvidenceDocument::query()->create(['id' => 'CED-EVIDENCE-001', 'project_id' => $project->id, 'storage_disk' => 'evidence', 'storage_path' => 'CED-PROJECT-001/invoice.pdf', 'original_name' => 'invoice.pdf', 'mime_type' => 'application/pdf', 'byte_size' => 16, 'sha256' => hash('sha256', 'private-evidence'), 'access_class' => 'PRIVATE', 'review_state' => 'ACCEPTED', 'uploaded_by' => $member->id]);
        $this->actingAs($outsider)->get(route('projects.evidence.download', [$project, $evidence]))->assertForbidden();
        $this->assertDatabaseMissing('audit_events', ['action' => 'evidence.downloaded']);
        $this->actingAs($member)->get(route('projects.evidence.download', [$project, $evidence]))->assertOk()->assertDownload('invoice.pdf');
        $this->assertDatabaseHas('audit_events', ['project_id' => $project->id, 'action' => 'evidence.downloaded', 'auditable_id' => $evidence->id]);
        self::assertFalse(config('filesystems.disks.evidence.serve'));
        self::assertArrayNotHasKey('url', config('filesystems.disks.evidence'));
    }

    public function test_ac08_canonical_authorized_portfolio_executive_cost_source_audit_get_chain(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();
        [$project, $member, $transaction] = $this->sourceFixture();
        $audit = app(AuditRecorder::class)->record($project->id, $member, 'finance.posted', $transaction);
        $portfolioUrl = route('projects.portfolio');
        $executiveUrl = route('projects.executive', $project);
        $portfolio = $this->actingAs($member)->get($portfolioUrl)->assertOk();
        $portfolioHealth = $portfolio->viewData('cards')[0]['health'];
        $costRef = $portfolioHealth['source_scope']['cost'][0];
        self::assertSame('finance_transaction', $costRef['source_type']);
        self::assertSame($transaction->id, $costRef['source_id']);
        self::assertStringContainsString('COST', $portfolio->getContent());
        self::assertStringContainsString('Posted expense is 95.00% of budget', $portfolio->getContent());
        $executive = $this->actingAs($member)->get($executiveUrl)->assertOk();
        $executiveHealth = $executive->viewData('health');
        self::assertSame($transaction->id, $executiveHealth['source_scope']['cost'][0]['source_id']);
        $sourceUrl = app(CedExecutiveSourceLinkResolver::class)->resolve($project, $costRef)['url'];
        $this->actingAs($member)->get($sourceUrl)->assertOk()->assertSee($transaction->id)->assertSee($project->id);
        $realAudit = AuditEvent::query()->where('action', 'finance.posted')->where('auditable_id', $transaction->id)->firstOrFail();
        self::assertSame($project->id, $realAudit->project_id);
        self::assertSame($transaction->id, $realAudit->auditable_id);
        $auditUrl = route('projects.audit.show', [$project, $realAudit]);
        $this->actingAs($member)->get($auditUrl)->assertOk()->assertSee($realAudit->id)->assertSee('finance.posted')->assertSee($transaction->id)->assertSee($project->id);
        CarbonImmutable::setTestNow();
    }

    /** RM08_AC11_CANONICAL_SECURITY_CLOSURE_TEST */
    public function test_ac11_single_security_matrix_full_rm08_path_closure(): void
    {
        $this->withoutVite();
        Storage::fake('evidence');
        [$project, $member, $transaction] = $this->sourceFixture();
        $outsider = User::factory()->create();
        $projectB = $this->project('CED-PROJECT-002', 'CED-002');
        $crossProject = User::factory()->create();
        $projectB->members()->attach($crossProject, ['role' => 'EXECUTIVE']);
        $task = WbsTask::query()->where('project_id', $project->id)->firstOrFail();
        $audit = app(AuditRecorder::class)->record($project->id, $member, 'finance.posted', $transaction);
        Storage::disk('evidence')->put('CED-PROJECT-001/invoice.pdf', 'private-evidence');
        $evidence = EvidenceDocument::query()->create(['id' => 'CED-EVIDENCE-001', 'project_id' => $project->id, 'storage_disk' => 'evidence', 'storage_path' => 'CED-PROJECT-001/invoice.pdf', 'original_name' => 'invoice.pdf', 'mime_type' => 'application/pdf', 'byte_size' => 16, 'sha256' => hash('sha256', 'private-evidence'), 'access_class' => 'PRIVATE', 'review_state' => 'ACCEPTED', 'uploaded_by' => $member->id]);
        $paths = [
            'P1 portfolio' => route('projects.portfolio'),
            'P2 executive' => route('projects.executive', $project),
            'P3 wbs_task' => route('projects.wbs.tasks.show', [$project, $task]),
            'P4 finance_transaction' => route('projects.finance.transactions.show', [$project, $transaction]),
            'P5 audit_target' => route('projects.audit.show', [$project, $audit]),
            'P6 private_evidence' => route('projects.evidence.download', [$project, $evidence]),
        ];

        $this->actingAs($member)->get($paths['P1 portfolio'])->assertOk()->assertSee($task->id)->assertSee($transaction->id);
        $this->actingAs($member)->get($paths['P2 executive'])->assertOk()->assertSee($task->id)->assertSee($transaction->id);
        $this->actingAs($member)->get($paths['P3 wbs_task'])->assertOk()->assertSee($task->id)->assertSee($project->id);
        $this->actingAs($member)->get($paths['P4 finance_transaction'])->assertOk()->assertSee($transaction->id)->assertSee($project->id);
        $this->actingAs($member)->get($paths['P5 audit_target'])->assertOk()->assertSee($audit->id)->assertSee($transaction->id);
        $this->actingAs($member)->get($paths['P6 private_evidence'])->assertOk()->assertDownload('invoice.pdf');

        $sensitive = [$task->id, $transaction->id, $audit->id, $evidence->id, 'invoice.pdf', $evidence->storage_path, $evidence->sha256, 'private-evidence', 'Posted cost', 'finance.posted'];
        $this->app['auth']->logout();
        foreach ($paths as $url) {
            $response = $this->get($url)->assertForbidden();
            foreach ($sensitive as $value) {
                $response->assertDontSee($value);
            }
        }
        $outsiderPortfolio = $this->actingAs($outsider)->get($paths['P1 portfolio'])->assertOk()->assertSee('No projects in permitted scope');
        foreach ($sensitive as $value) {
            $outsiderPortfolio->assertDontSee($value);
        }
        foreach (array_slice($paths, 1) as $url) {
            $response = $this->actingAs($outsider)->get($url)->assertForbidden();
            foreach ($sensitive as $value) {
                $response->assertDontSee($value);
            }
        }
        foreach (array_slice($paths, 1) as $url) {
            $response = $this->actingAs($crossProject)->get($url)->assertForbidden();
            foreach ($sensitive as $value) {
                $response->assertDontSee($value);
            }
        }
    }

    public function test_decision_signal_respects_membership_project_policy_and_cross_project_non_disclosure(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            [$project, $member] = $this->sourceFixture();
            $otherProject = $this->project('CED-PROJECT-002', 'CED-002');
            $crossProjectMember = User::factory()->create();
            $outsider = User::factory()->create();
            $otherProject->members()->attach($crossProjectMember, ['role' => 'EXECUTIVE']);

            $memberPortfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $decision = $memberPortfolio->viewData('cards')[0]['decision_required'];
            $reason = $decision['reasons']['SCHEDULE']['reason'];
            self::assertTrue($decision['required']);
            $memberPortfolio->assertSee('Decision required')->assertSee($reason)->assertSee(route('projects.executive', $project), false);
            self::assertStringContainsString('Risk CED-RISK-001 · Late material delivery', $memberPortfolio->getContent());
            self::assertStringNotContainsString('data-decision-source-id="CED-RISK-001"', $memberPortfolio->getContent());

            $crossPortfolio = $this->actingAs($crossProjectMember)->get(route('projects.portfolio'))->assertOk();
            $crossPortfolio->assertDontSee('Decision required')->assertDontSee($project->id)->assertDontSee($reason);
            $this->actingAs($crossProjectMember)->get(route('projects.executive', $project))->assertForbidden()->assertDontSee($reason);

            $outsiderPortfolio = $this->actingAs($outsider)->get(route('projects.portfolio'))->assertOk();
            $outsiderPortfolio->assertSee('No projects in permitted scope')->assertDontSee('Decision required')->assertDontSee($project->id)->assertDontSee($reason);

            $this->app['auth']->logout();
            $this->get(route('projects.portfolio'))->assertForbidden()->assertDontSee($reason);
            $this->get(route('projects.executive', $project))->assertForbidden()->assertDontSee($reason);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_decision_signal_render_is_read_only_and_preserves_existing_audit_behavior(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();
        Storage::fake('evidence');

        try {
            [$project, $member, $transaction] = $this->sourceFixture();
            $existingAudit = app(AuditRecorder::class)->record($project->id, $member, 'finance.posted', $transaction);
            Storage::disk('evidence')->put('CED-PROJECT-001/decision-source.pdf', 'decision-source');
            $evidence = EvidenceDocument::query()->create([
                'id' => 'CED-EVIDENCE-DECISION-001',
                'project_id' => $project->id,
                'storage_disk' => 'evidence',
                'storage_path' => 'CED-PROJECT-001/decision-source.pdf',
                'original_name' => 'decision-source.pdf',
                'mime_type' => 'application/pdf',
                'byte_size' => 15,
                'sha256' => hash('sha256', 'decision-source'),
                'access_class' => 'PRIVATE',
                'review_state' => 'ACCEPTED',
                'uploaded_by' => $member->id,
            ]);
            $beforePortfolio = AuditEvent::query()->orderBy('id')->pluck('id')->all();

            $this->actingAs($member)->get(route('projects.portfolio'))->assertOk()->assertSee('Decision required');

            self::assertSame($beforePortfolio, AuditEvent::query()->orderBy('id')->pluck('id')->all());
            $this->assertDatabaseHas('audit_events', ['id' => $existingAudit->id, 'action' => 'finance.posted']);
            $this->assertDatabaseMissing('audit_events', ['action' => 'decision.required']);
            $this->actingAs($member)->get(route('projects.evidence.download', [$project, $evidence]))->assertOk()->assertDownload('decision-source.pdf');
            $this->assertDatabaseHas('audit_events', ['project_id' => $project->id, 'action' => 'evidence.downloaded', 'auditable_id' => $evidence->id]);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_health_risk_explanation_project_drilldown_is_authorized_read_only_and_non_disclosing(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            [$project, $member, $transaction] = $this->sourceFixture();
            $task = WbsTask::query()->where('project_id', $project->id)->firstOrFail();
            $risk = ProjectRisk::query()->where('project_id', $project->id)->firstOrFail();
            $beforeCounts = [
                'audit_events' => AuditEvent::query()->count(),
                'finance_transactions' => FinanceTransaction::query()->count(),
                'progress_entries' => ProgressEntry::query()->count(),
                'project_risks' => ProjectRisk::query()->count(),
                'wbs_tasks' => WbsTask::query()->count(),
            ];

            $response = $this->actingAs($member)->get(route('projects.executive', $project))->assertOk();
            $health = $response->viewData('health');
            $content = $response->getContent();

            $response->assertSee('Project status: ACTIVE')
                ->assertSee('data-health-status="'.$health['status'].'"', false)
                ->assertSee($health['explanation'])
                ->assertSee('Rule version: '.$health['rule_version'])
                ->assertSee('Calculated at: '.$health['calculated_at']);
            foreach ($health['dimensions'] as $dimension => $signal) {
                self::assertSame($signal['reason'], $health['reasons'][$dimension]);
                self::assertStringContainsString('data-health-source-dimension="'.$dimension.'"', $content);
                $response->assertSee($dimension.' · '.$signal['status'])
                    ->assertSee('Dimension reason: '.$signal['reason'])
                    ->assertSee('Canonical reason: '.$health['reasons'][$dimension]);
                if ($signal['value'] !== null) {
                    $response->assertSee('Value: '.$signal['value'].' '.$signal['unit']);
                }
            }
            $response->assertSee($task->id)->assertSee($transaction->id)->assertSee($risk->id);
            self::assertSame($beforeCounts, [
                'audit_events' => AuditEvent::query()->count(),
                'finance_transactions' => FinanceTransaction::query()->count(),
                'progress_entries' => ProgressEntry::query()->count(),
                'project_risks' => ProjectRisk::query()->count(),
                'wbs_tasks' => WbsTask::query()->count(),
            ]);

            $sensitive = [$project->id, $task->id, $transaction->id, $risk->id, $risk->title, $health['explanation'], ...array_values($health['reasons'])];
            $outsider = User::factory()->create();
            $otherProject = $this->project('CED-PROJECT-EXPLANATION-OTHER', 'CED-OTHER');
            $crossProjectMember = User::factory()->create();
            $otherProject->members()->attach($crossProjectMember, ['role' => 'EXECUTIVE']);

            $this->app['auth']->logout();
            foreach ([$this->get(route('projects.executive', $project)), $this->actingAs($outsider)->get(route('projects.executive', $project)), $this->actingAs($crossProjectMember)->get(route('projects.executive', $project))] as $denied) {
                $denied->assertForbidden();
                foreach ($sensitive as $value) {
                    $denied->assertDontSee($value);
                }
            }
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_health_risk_explanation_uses_only_resolved_same_project_links_and_no_guessed_href(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            [$project, $member, $transaction] = $this->sourceFixture();
            $task = WbsTask::query()->where('project_id', $project->id)->firstOrFail();
            $health = app(CedProjectHealthV1ReadModel::class)->forProject($project);
            $foreignProject = $this->project('CED-PROJECT-FOREIGN-SOURCE', 'CED-FOREIGN');
            $health['source_scope']['risk'][] = [
                'source_type' => 'unsupported_source',
                'source_id' => 'CED-UNSUPPORTED-SOURCE-001',
                'project_id' => $project->id,
                'label' => 'Unsupported same-project source',
            ];
            $health['source_scope']['risk'][] = [
                'source_type' => 'project_risk',
                'source_id' => 'CED-FOREIGN-RISK-SECRET',
                'project_id' => $foreignProject->id,
                'label' => 'FOREIGN_SOURCE_MUST_NOT_RENDER',
            ];
            $healthBoundary = \Mockery::mock(CedProjectHealthV1ReadModel::class);
            $healthBoundary->shouldReceive('forProject')->once()
                ->withArgs(fn (Project $value): bool => $value->is($project))
                ->andReturn($health);
            $this->app->instance(CedProjectHealthV1ReadModel::class, $healthBoundary);

            $response = $this->actingAs($member)->get(route('projects.executive', $project))->assertOk();
            $content = $response->getContent();
            $taskUrl = route('projects.wbs.tasks.show', [$project, $task]);
            $transactionUrl = route('projects.finance.transactions.show', [$project, $transaction]);

            $response->assertSee($taskUrl, false)
                ->assertSee($transactionUrl, false)
                ->assertSee('Progress entry CED-PROGRESS-001')
                ->assertSee('Risk CED-RISK-001 · Late material delivery')
                ->assertSee('Unsupported same-project source')
                ->assertDontSee('FOREIGN_SOURCE_MUST_NOT_RENDER');
            self::assertStringContainsString('data-health-source-id="'.$task->id.'"', $content);
            self::assertStringContainsString('data-health-source-id="'.$transaction->id.'"', $content);
            self::assertStringNotContainsString('data-health-source-id="CED-PROGRESS-001"', $content);
            self::assertStringNotContainsString('data-health-source-id="CED-RISK-001"', $content);
            self::assertStringNotContainsString('data-health-source-id="CED-UNSUPPORTED-SOURCE-001"', $content);
            self::assertDoesNotMatchRegularExpression('/href="[^"]*(?:CED-PROGRESS-001|CED-RISK-001|CED-UNSUPPORTED-SOURCE-001)/', $content);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_finance_exception_drills_only_to_authorized_same_project_posted_transactions(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            [$project, $member, $posted] = $this->sourceFixture();
            $unposted = FinanceTransaction::query()->create([
                'id' => 'CED-FIN-UNPOSTED',
                'project_id' => $project->id,
                'direction' => 'EXPENSE',
                'description' => 'Approved but unposted cost',
                'amount' => '25.00',
                'status' => FinanceStatus::Approved,
                'created_by' => $member->id,
                'approved_by' => User::factory()->create()->id,
                'approved_at' => now(),
            ]);
            $foreignProject = $this->project('CED-PROJECT-FINANCE-FOREIGN', 'CED-FINANCE-FOREIGN');
            $foreign = FinanceTransaction::query()->create([
                'id' => 'CED-FIN-FOREIGN-POSTED',
                'project_id' => $foreignProject->id,
                'direction' => 'EXPENSE',
                'description' => 'Foreign posted cost',
                'amount' => '999.00',
                'status' => FinanceStatus::Posted,
                'created_by' => $member->id,
                'approved_by' => User::factory()->create()->id,
                'approved_at' => now(),
                'posted_at' => now(),
            ]);

            $portfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $card = $portfolio->viewData('cards')[0];
            $exception = $card['finance_exception'];
            $postedUrl = route('projects.finance.transactions.show', [$project, $posted]);
            $unpostedUrl = route('projects.finance.transactions.show', [$project, $unposted]);
            $foreignUrl = route('projects.finance.transactions.show', [$foreignProject, $foreign]);

            self::assertSame([$posted->id], array_column($exception['source_scope'], 'source_id'));
            self::assertSame([$posted->id], $card['finance']['contributing_source_record_scope']['included_record_ids']);
            self::assertSame(1, substr_count($portfolio->getContent(), 'data-finance-exception="true"'));
            self::assertStringContainsString('data-finance-exception-source-id="'.$posted->id.'"', $portfolio->getContent());
            $portfolio->assertSee($postedUrl, false)
                ->assertDontSee($unpostedUrl, false)
                ->assertDontSee($foreignUrl, false);

            $detail = $this->actingAs($member)->get(route('projects.executive', $project))->assertOk();
            self::assertSame([$posted->id], array_column($detail->viewData('financeException')['source_scope'], 'source_id'));
            self::assertSame(1, substr_count($detail->getContent(), 'data-finance-exception="true"'));
            $detail->assertSee($postedUrl, false)
                ->assertDontSee($unpostedUrl, false)
                ->assertDontSee($foreignUrl, false);
            $this->actingAs($member)->get($postedUrl)->assertOk()
                ->assertSee($posted->id)
                ->assertSee($project->id)
                ->assertSee(FinanceStatus::Posted->value);

            $resolver = app(CedExecutiveSourceLinkResolver::class);
            $postedRef = $exception['source_scope'][0];
            self::assertSame($postedUrl, $resolver->resolve($project, $postedRef)['url']);
            self::assertNull($resolver->resolve($project, [...$postedRef, 'source_id' => $unposted->id]));
            self::assertNull($resolver->resolve($project, [...$postedRef, 'source_id' => 'CED-FIN-MISSING']));
            self::assertNull($resolver->resolve($project, [...$postedRef, 'source_type' => 'unsupported']));
            self::assertNull($resolver->resolve($project, [...$postedRef, 'source_id' => $foreign->id, 'project_id' => $foreignProject->id]));

            $forgedCard = $card;
            $forgedCard['finance_exception']['source_scope'] = [[
                'source_type' => 'finance_transaction',
                'source_id' => $unposted->id,
                'project_id' => $project->id,
                'label' => 'Unposted finance source',
            ]];
            $forgedContent = view('projects.portfolio', ['cards' => [$forgedCard]])->render();
            self::assertStringContainsString('Unposted finance source', $forgedContent);
            self::assertStringNotContainsString('href="'.$unpostedUrl.'"', $forgedContent);
            self::assertStringNotContainsString('data-finance-exception-source-id="'.$unposted->id.'"', $forgedContent);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_finance_exception_is_non_disclosing_read_only_and_never_guesses_links(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            [$project, $member, $posted] = $this->sourceFixture();
            $unposted = FinanceTransaction::query()->create([
                'id' => 'CED-FIN-READ-ONLY-UNPOSTED',
                'project_id' => $project->id,
                'direction' => 'EXPENSE',
                'description' => 'Unposted secret cost',
                'amount' => '25.00',
                'status' => FinanceStatus::Approved,
                'created_by' => $member->id,
                'approved_by' => User::factory()->create()->id,
                'approved_at' => now(),
            ]);
            $foreignProject = $this->project('CED-PROJECT-READ-ONLY-FOREIGN', 'CED-READ-ONLY-FOREIGN');
            $foreign = FinanceTransaction::query()->create([
                'id' => 'CED-FIN-READ-ONLY-FOREIGN',
                'project_id' => $foreignProject->id,
                'direction' => 'EXPENSE',
                'description' => 'Foreign secret cost',
                'amount' => '777.00',
                'status' => FinanceStatus::Posted,
                'created_by' => $member->id,
                'approved_by' => User::factory()->create()->id,
                'approved_at' => now(),
                'posted_at' => now(),
            ]);
            $beforeCounts = [
                'audit_events' => AuditEvent::query()->count(),
                'evidence_documents' => EvidenceDocument::query()->count(),
                'finance_transactions' => FinanceTransaction::query()->count(),
                'progress_entries' => ProgressEntry::query()->count(),
                'project_risks' => ProjectRisk::query()->count(),
                'projects' => Project::query()->count(),
                'wbs_tasks' => WbsTask::query()->count(),
            ];

            $portfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $card = $portfolio->viewData('cards')[0];
            $exception = $card['finance_exception'];
            self::assertNotNull($exception);
            $detail = $this->actingAs($member)->get(route('projects.executive', $project))->assertOk();
            $postedUrl = route('projects.finance.transactions.show', [$project, $posted]);
            $this->actingAs($member)->get($postedUrl)->assertOk();

            self::assertSame($beforeCounts, [
                'audit_events' => AuditEvent::query()->count(),
                'evidence_documents' => EvidenceDocument::query()->count(),
                'finance_transactions' => FinanceTransaction::query()->count(),
                'progress_entries' => ProgressEntry::query()->count(),
                'project_risks' => ProjectRisk::query()->count(),
                'projects' => Project::query()->count(),
                'wbs_tasks' => WbsTask::query()->count(),
            ]);
            self::assertSame($exception, $detail->viewData('financeException'));
            self::assertSame($card['finance'], $detail->viewData('finance'));

            $projectionSource = file_get_contents(app_path('Services/CedFinanceExceptionV1Projection.php'));
            self::assertIsString($projectionSource);
            foreach (['FinanceTransaction::', 'financeTransactions(', '::query(', 'DB::', 'Cache::', 'session(', 'Storage::', 'Http::', '->save(', '->create(', '90.0', '100.0'] as $forbiddenImplementation) {
                self::assertStringNotContainsString($forbiddenImplementation, $projectionSource);
            }

            $routeNames = collect(app('router')->getRoutes())->map(fn ($route): ?string => $route->getName())->filter();
            self::assertTrue($routeNames->contains('projects.finance.transactions.show'));
            self::assertFalse($routeNames->contains(fn (string $name): bool => str_contains($name, 'finance.exception')));

            $unresolvedSources = [
                'missing' => [
                    'source_type' => 'finance_transaction',
                    'source_id' => 'CED-FIN-MISSING',
                    'project_id' => $project->id,
                    'label' => 'Missing canonical finance source',
                    'guessed_url' => route('projects.finance.transactions.show', [$project, 'CED-FIN-MISSING']),
                ],
                'unposted' => [
                    'source_type' => 'finance_transaction',
                    'source_id' => $unposted->id,
                    'project_id' => $project->id,
                    'label' => 'Unposted canonical finance source',
                    'guessed_url' => route('projects.finance.transactions.show', [$project, $unposted]),
                ],
                'unsupported' => [
                    'source_type' => 'unsupported',
                    'source_id' => 'CED-FIN-UNSUPPORTED',
                    'project_id' => $project->id,
                    'label' => 'Unsupported canonical finance source',
                    'guessed_url' => url('/projects/'.$project->id.'/finance/transactions/CED-FIN-UNSUPPORTED'),
                ],
                'foreign' => [
                    'source_type' => 'finance_transaction',
                    'source_id' => $foreign->id,
                    'project_id' => $foreignProject->id,
                    'label' => 'Foreign canonical finance source',
                    'guessed_url' => route('projects.finance.transactions.show', [$foreignProject, $foreign]),
                ],
            ];

            foreach ($unresolvedSources as $case => $source) {
                $forgedCard = $card;
                $forgedCard['finance_exception']['source_scope'] = [[
                    'source_type' => $source['source_type'],
                    'source_id' => $source['source_id'],
                    'project_id' => $source['project_id'],
                    'label' => $source['label'],
                ]];
                $content = view('projects.portfolio', ['cards' => [$forgedCard]])->render();
                self::assertStringContainsString($source['label'], $content, $case);
                self::assertStringNotContainsString('href="'.$source['guessed_url'].'"', $content, $case);
                self::assertStringNotContainsString('data-finance-exception-source-id="'.$source['source_id'].'"', $content, $case);
            }

            $sensitive = [
                $project->id,
                $posted->id,
                $exception['reason'],
                $card['finance']['expense'],
                $card['finance']['net'],
                $card['finance']['explanation'],
                $exception['source_scope'][0]['label'],
                'Posted cost',
                $postedUrl,
            ];
            $outsider = User::factory()->create();
            $crossProject = $this->project('CED-PROJECT-CROSS-FINANCE', 'CED-CROSS-FINANCE');
            $crossProjectMember = User::factory()->create();
            $crossProject->members()->attach($crossProjectMember, ['role' => 'EXECUTIVE']);

            $this->app['auth']->logout();
            foreach ([
                $this->get(route('projects.portfolio'))->assertForbidden(),
                $this->get(route('projects.executive', $project))->assertForbidden(),
                $this->get($postedUrl)->assertForbidden(),
            ] as $denied) {
                foreach ($sensitive as $value) {
                    $denied->assertDontSee($value);
                }
            }

            $outsiderPortfolio = $this->actingAs($outsider)->get(route('projects.portfolio'))->assertOk()
                ->assertSee('No projects in permitted scope')
                ->assertDontSee('Finance exception');
            foreach ($sensitive as $value) {
                $outsiderPortfolio->assertDontSee($value);
            }
            foreach ([
                $this->actingAs($outsider)->get(route('projects.executive', $project))->assertForbidden(),
                $this->actingAs($outsider)->get($postedUrl)->assertForbidden(),
                $this->actingAs($crossProjectMember)->get(route('projects.executive', $project))->assertForbidden(),
                $this->actingAs($crossProjectMember)->get($postedUrl)->assertForbidden(),
            ] as $denied) {
                foreach ($sensitive as $value) {
                    $denied->assertDontSee($value);
                }
            }

            $crossPortfolio = $this->actingAs($crossProjectMember)->get(route('projects.portfolio'))->assertOk();
            foreach ($sensitive as $value) {
                $crossPortfolio->assertDontSee($value);
            }
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_evidence_data_quality_exception_drills_to_existing_authorized_evidence_records_and_controlled_download(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();
        Storage::fake('evidence');

        try {
            [$project, $member] = $this->sourceFixture();
            ProgressEntry::query()->where('project_id', $project->id)->update(['recorded_at' => '2026-05-02 12:00:00']);
            $accepted = $this->privateEvidence($project, $member, 'CED-EVIDENCE-FRESHNESS-ACCEPTED', 'accepted-source.pdf', 'accepted-private-evidence', 'ACCEPTED');
            $uploaded = $this->privateEvidence($project, $member, 'CED-EVIDENCE-FRESHNESS-UPLOADED', 'uploaded-source.pdf', 'uploaded-private-evidence', 'UPLOADED');
            $foreignProject = $this->project('CED-EVIDENCE-FRESHNESS-FOREIGN-DETAIL', 'CED-EVIDENCE-FOREIGN-DETAIL');
            $foreignMember = User::factory()->create();
            $foreignProject->members()->attach($foreignMember, ['role' => 'EXECUTIVE']);
            $foreignEvidence = $this->privateEvidence($foreignProject, $foreignMember, 'CED-EVIDENCE-FRESHNESS-FOREIGN', 'foreign-source.pdf', 'foreign-private-evidence', 'ACCEPTED');
            $beforeAuditIds = AuditEvent::query()->orderBy('id')->pluck('id')->all();

            $portfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $card = $portfolio->viewData('cards')[0];
            $exception = $card['evidence_data_quality_exception'];
            self::assertNotNull($exception);
            self::assertSame($card['health']['dimensions']['DATA_FRESHNESS']['status'], $exception['status']);
            self::assertSame($card['health']['source_scope']['freshness'], $exception['source_scope']);
            self::assertSame([$accepted->id, $uploaded->id], $card['evidence_ids']);
            self::assertSame(1, substr_count($portfolio->getContent(), 'data-evidence-data-quality-exception="true"'));
            $portfolio->assertSee(route('projects.executive', $project), false)
                ->assertDontSee(route('projects.evidence.download', [$project, $accepted]), false)
                ->assertDontSee(route('projects.evidence.download', [$project, $uploaded]), false)
                ->assertDontSee($accepted->original_name)
                ->assertDontSee($accepted->storage_path)
                ->assertDontSee($accepted->sha256);

            $detail = $this->actingAs($member)->get(route('projects.executive', $project))->assertOk();
            foreach ([$accepted, $uploaded] as $evidence) {
                $detail->assertSee($evidence->id)
                    ->assertSee($evidence->original_name)
                    ->assertSee($evidence->review_state)
                    ->assertSee(route('projects.evidence.download', [$project, $evidence]), false);
            }
            $detail->assertDontSee($foreignEvidence->id)
                ->assertDontSee($foreignEvidence->original_name)
                ->assertDontSee(route('projects.evidence.download', [$foreignProject, $foreignEvidence]), false);
            self::assertSame($beforeAuditIds, AuditEvent::query()->orderBy('id')->pluck('id')->all());

            $downloadRoute = app('router')->getRoutes()->getByName('projects.evidence.download');
            self::assertNotNull($downloadRoute);
            self::assertSame(EvidenceDownloadController::class, $downloadRoute->getActionName());

            $downloadsBefore = AuditEvent::query()->where('action', 'evidence.downloaded')->count();
            foreach ([$accepted, $uploaded] as $index => $evidence) {
                $this->actingAs($member)->get(route('projects.evidence.download', [$project, $evidence]))
                    ->assertOk()
                    ->assertDownload($evidence->original_name);
                self::assertSame(
                    $downloadsBefore + $index + 1,
                    AuditEvent::query()->where('action', 'evidence.downloaded')->count(),
                );
                self::assertSame(1, AuditEvent::query()
                    ->where('project_id', $project->id)
                    ->where('action', 'evidence.downloaded')
                    ->where('auditable_id', $evidence->id)
                    ->count());
            }

            self::assertFalse(config('filesystems.disks.evidence.serve'));
            self::assertArrayNotHasKey('url', config('filesystems.disks.evidence'));
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_evidence_data_quality_exception_is_project_scoped_non_disclosing_and_read_only(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();
        Storage::fake('evidence');

        try {
            [$project, $member] = $this->sourceFixture();
            ProgressEntry::query()->where('project_id', $project->id)->update(['recorded_at' => '2026-05-02 12:00:00']);
            $evidence = $this->privateEvidence($project, $member, 'CED-EVIDENCE-FRESHNESS-PRIVATE', 'freshness-private.pdf', 'freshness-private-content', 'ACCEPTED');
            $progress = ProgressEntry::query()->where('project_id', $project->id)->firstOrFail();
            $health = app(CedProjectHealthV1ReadModel::class)->forProject($project);
            $reason = $health['reasons']['DATA_FRESHNESS'];
            $otherProject = $this->project('CED-EVIDENCE-FRESHNESS-CROSS', 'CED-EVIDENCE-FRESHNESS-CROSS');
            $crossProjectMember = User::factory()->create();
            $otherProject->members()->attach($crossProjectMember, ['role' => 'EXECUTIVE']);
            $outsider = User::factory()->create();
            $downloadUrl = route('projects.evidence.download', [$project, $evidence]);
            $sensitive = [
                $project->id,
                $progress->id,
                $reason,
                $evidence->id,
                $evidence->original_name,
                $evidence->sha256,
                $evidence->review_state,
                $evidence->storage_path,
                $downloadUrl,
            ];
            $beforeCounts = [
                'audit_events' => AuditEvent::query()->count(),
                'evidence_documents' => EvidenceDocument::query()->count(),
                'finance_transactions' => FinanceTransaction::query()->count(),
                'progress_entries' => ProgressEntry::query()->count(),
                'project_risks' => ProjectRisk::query()->count(),
                'projects' => Project::query()->count(),
                'wbs_tasks' => WbsTask::query()->count(),
            ];

            $authorizedPortfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            self::assertNotNull($authorizedPortfolio->viewData('cards')[0]['evidence_data_quality_exception']);
            $authorizedPortfolio->assertSee($reason)
                ->assertSee($progress->id)
                ->assertSee(route('projects.executive', $project), false)
                ->assertDontSee($evidence->original_name)
                ->assertDontSee($evidence->sha256)
                ->assertDontSee($evidence->storage_path)
                ->assertDontSee($downloadUrl, false);
            $this->actingAs($member)->get(route('projects.executive', $project))->assertOk()
                ->assertSee($evidence->id)
                ->assertSee($evidence->original_name)
                ->assertSee($downloadUrl, false);

            foreach ([$outsider, $crossProjectMember] as $unauthorizedUser) {
                $portfolio = $this->actingAs($unauthorizedUser)->get(route('projects.portfolio'))->assertOk();
                foreach ($sensitive as $value) {
                    $portfolio->assertDontSee($value);
                }

                foreach ([
                    $this->actingAs($unauthorizedUser)->get(route('projects.executive', $project))->assertForbidden(),
                    $this->actingAs($unauthorizedUser)->get($downloadUrl)->assertForbidden(),
                ] as $denied) {
                    foreach ($sensitive as $value) {
                        $denied->assertDontSee($value);
                    }
                }
            }

            $this->actingAs($member)->get(route('projects.evidence.download', [$project, 'CED-EVIDENCE-MISSING']))
                ->assertNotFound();
            $this->actingAs($crossProjectMember)->get(route('projects.evidence.download', [$otherProject, $evidence]))
                ->assertNotFound();

            $this->app['auth']->logout();
            foreach ([
                $this->get(route('projects.portfolio'))->assertForbidden(),
                $this->get(route('projects.executive', $project))->assertForbidden(),
                $this->get($downloadUrl)->assertForbidden(),
            ] as $denied) {
                foreach ($sensitive as $value) {
                    $denied->assertDontSee($value);
                }
            }

            self::assertSame($beforeCounts, [
                'audit_events' => AuditEvent::query()->count(),
                'evidence_documents' => EvidenceDocument::query()->count(),
                'finance_transactions' => FinanceTransaction::query()->count(),
                'progress_entries' => ProgressEntry::query()->count(),
                'project_risks' => ProjectRisk::query()->count(),
                'projects' => Project::query()->count(),
                'wbs_tasks' => WbsTask::query()->count(),
            ]);
            $this->assertDatabaseMissing('audit_events', ['action' => 'evidence.downloaded']);
            $this->assertDatabaseMissing('audit_events', ['action' => 'evidence.data_quality_exception']);
            self::assertTrue(Storage::disk('evidence')->exists($evidence->storage_path));
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_evidence_data_quality_exception_never_infers_from_evidence_records_or_guesses_source_links(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();
        Storage::fake('evidence');

        try {
            [$project, $member] = $this->sourceFixture();
            $evidence = $this->privateEvidence($project, $member, 'CED-EVIDENCE-FRESHNESS-CONTEXT', 'freshness-context.pdf', 'freshness-context-content', 'UPLOADED');

            $recentPortfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $recentCard = $recentPortfolio->viewData('cards')[0];
            self::assertSame('GREEN', $recentCard['health']['dimensions']['DATA_FRESHNESS']['status']);
            self::assertNull($recentCard['evidence_data_quality_exception']);
            self::assertSame([$evidence->id], $recentCard['evidence_ids']);
            self::assertStringNotContainsString('data-evidence-data-quality-exception="true"', $recentPortfolio->getContent());

            ProgressEntry::query()->where('project_id', $project->id)->update(['recorded_at' => '2026-05-02 12:00:00']);
            $stalePortfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $staleCard = $stalePortfolio->viewData('cards')[0];
            $exception = $staleCard['evidence_data_quality_exception'];
            self::assertNotNull($exception);
            self::assertSame($staleCard['health']['dimensions']['DATA_FRESHNESS']['status'], $exception['status']);
            self::assertSame($staleCard['health']['dimensions']['DATA_FRESHNESS']['value'], $exception['value']);
            self::assertSame($staleCard['health']['dimensions']['DATA_FRESHNESS']['unit'], $exception['unit']);
            self::assertSame($staleCard['health']['reasons']['DATA_FRESHNESS'], $exception['reason']);
            self::assertSame($staleCard['health']['source_scope']['freshness'], $exception['source_scope']);
            self::assertSame(['CED-PROGRESS-001'], array_column($exception['source_scope'], 'source_id'));
            self::assertNotContains($evidence->id, array_column($exception['source_scope'], 'source_id'));

            $audit = app(AuditRecorder::class)->record($project->id, $member, 'evidence.reviewed', $evidence, [
                'access_class' => $evidence->access_class,
                'review_state' => $evidence->review_state,
            ]);
            $withAudit = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk()->viewData('cards')[0];
            self::assertSame($exception, $withAudit['evidence_data_quality_exception']);
            self::assertContains((string) $audit->id, $withAudit['audit_ids']);

            $evidenceDownloadUrl = route('projects.evidence.download', [$project, $evidence]);
            $evidence->delete();
            $withoutEvidencePortfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $withoutEvidenceCard = $withoutEvidencePortfolio->viewData('cards')[0];
            self::assertSame([], $withoutEvidenceCard['evidence_ids']);
            self::assertSame($exception, $withoutEvidenceCard['evidence_data_quality_exception']);
            $this->actingAs($member)->get(route('projects.executive', $project))->assertOk()->assertSee('No evidence');

            self::assertSame(1, preg_match('/<section[^>]+data-evidence-data-quality-exception="true".*?<\/section>/s', $withoutEvidencePortfolio->getContent(), $matches));
            $callout = $matches[0];
            self::assertStringContainsString($exception['source_scope'][0]['source_id'], $callout);
            self::assertSame(1, substr_count($callout, 'href='));
            self::assertStringContainsString('href="'.route('projects.executive', $project).'"', $callout);
            self::assertStringNotContainsString($evidenceDownloadUrl, $callout);
            self::assertDoesNotMatchRegularExpression('/href="[^"]*'.preg_quote($exception['source_scope'][0]['source_id'], '/').'[^"]*"/', $callout);

            $forgedCard = $withoutEvidenceCard;
            $forgedCard['evidence_data_quality_exception']['source_scope'] = [[
                'source_type' => 'evidence_document',
                'source_id' => $evidence->id,
                'project_id' => $project->id,
                'label' => 'Evidence source must remain text-only',
            ]];
            $forgedContent = view('projects.portfolio', ['cards' => [$forgedCard]])->render();
            self::assertSame(1, preg_match('/<section[^>]+data-evidence-data-quality-exception="true".*?<\/section>/s', $forgedContent, $forgedMatches));
            self::assertStringContainsString('Evidence source must remain text-only', $forgedMatches[0]);
            self::assertSame(1, substr_count($forgedMatches[0], 'href='));
            self::assertStringNotContainsString($evidenceDownloadUrl, $forgedMatches[0]);

            $routeNames = collect(app('router')->getRoutes())->map(fn ($route): ?string => $route->getName())->filter();
            self::assertTrue($routeNames->contains('projects.executive'));
            self::assertTrue($routeNames->contains('projects.evidence.download'));
            self::assertFalse($routeNames->contains(fn (string $name): bool => str_contains($name, 'evidence.data_quality') || str_contains($name, 'data-quality')));
            $this->assertDatabaseMissing('audit_events', ['action' => 'evidence.downloaded']);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    private function sourceFixture(): array
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $task = WbsTask::query()->create(['id' => 'CED-TASK-001', 'project_id' => $project->id, 'wbs_code' => '1.0', 'name' => 'Foundation', 'baseline_start_date' => '2026-05-01', 'baseline_finish_date' => '2026-05-11', 'weight' => '100.0000', 'status' => 'IN_PROGRESS', 'is_baseline' => true, 'is_leaf' => true]);
        ProgressEntry::query()->create(['id' => 'CED-PROGRESS-001', 'project_id' => $project->id, 'task_id' => $task->id, 'progress_date' => '2026-05-06', 'progress_percent' => '40.00', 'recorded_by' => $member->id, 'recorded_at' => '2026-05-05 12:00:00', 'note' => 'Measured on site']);
        $transaction = FinanceTransaction::query()->create(['id' => 'CED-FIN-001', 'project_id' => $project->id, 'direction' => 'EXPENSE', 'description' => 'Posted cost', 'amount' => '950.00', 'status' => FinanceStatus::Posted, 'created_by' => $member->id, 'approved_by' => User::factory()->create()->id, 'approved_at' => now(), 'posted_at' => now()]);
        ProjectRisk::query()->create(['id' => 'CED-RISK-001', 'project_id' => $project->id, 'title' => 'Late material delivery', 'severity' => 'HIGH', 'status' => 'OPEN']);

        return [$project, $member, $transaction];
    }

    private function privateEvidence(Project $project, User $uploader, string $id, string $originalName, string $contents, string $reviewState): EvidenceDocument
    {
        $storagePath = $project->id.'/'.$originalName;
        Storage::disk('evidence')->put($storagePath, $contents);

        return EvidenceDocument::query()->create([
            'id' => $id,
            'project_id' => $project->id,
            'storage_disk' => 'evidence',
            'storage_path' => $storagePath,
            'original_name' => $originalName,
            'mime_type' => 'application/pdf',
            'byte_size' => strlen($contents),
            'sha256' => hash('sha256', $contents),
            'access_class' => 'PRIVATE',
            'review_state' => $reviewState,
            'uploaded_by' => $uploader->id,
        ]);
    }

    private function project(string $id, string $code): Project
    {
        return Project::query()->create(['id' => $id, 'code' => $code, 'name' => "Project {$code}", 'baseline_start_date' => '2026-05-01', 'baseline_finish_date' => '2026-05-31', 'budget_amount' => '1000.00', 'status' => 'ACTIVE']);
    }
}
