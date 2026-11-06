<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\CedExecutiveSourceLinkResolver;
use App\Services\CedFinanceExceptionV1Projection;
use App\Services\CedFinanceV1ReadModel;
use App\Services\CedProjectHealthV1ReadModel;
use App\Services\CedScheduleProgressExceptionV1Projection;
use App\Services\ExecutivePortfolioReadModel;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutivePortfolioRm08Test extends TestCase
{
    use RefreshDatabase;

    public function test_ac04_schedule_source_links_to_real_wbs_task_read_surface(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $task = $this->taskWithProgress($project, $member);

        $executive = $this->actingAs($member)->get(route('projects.executive', $project))->assertOk();
        $health = $executive->viewData('health');
        $schedule = collect($health['source_scope']['schedule'])->firstWhere('source_type', 'wbs_task');
        self::assertNotNull($schedule);
        self::assertSame($task->id, $schedule['source_id']);
        self::assertSame($project->id, $schedule['project_id']);
        self::assertStringContainsString('SCHEDULE', $executive->getContent());
        self::assertStringContainsString($schedule['label'], $executive->getContent());
        $link = app(CedExecutiveSourceLinkResolver::class)->resolve($project, $schedule);
        self::assertSame('projects.wbs.tasks.show', $link['route_name']);
        self::assertSame(route('projects.wbs.tasks.show', [$project, $task]), $link['url']);
        self::assertNotSame(route('projects.wbs.show', $project), $link['url']);
        self::assertNotSame(route('projects.schedule.show', $project), $link['url']);
        $this->actingAs($member)->get(route('projects.portfolio'))->assertOk()->assertSee($link['url'], false)->assertSee($task->id);
        $this->actingAs($member)->get($link['url'])->assertOk()->assertSee($task->id)->assertSee($project->id)->assertSee('Foundation');
    }

    public function test_ac04_wbs_task_route_and_resolver_deny_guest_unaffiliated_cross_project_missing_and_unsupported(): void
    {
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $foreign = $this->project('CED-PROJECT-002', 'CED-002');
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $foreign->members()->attach($outsider, ['role' => 'EXECUTIVE']);
        $task = $this->taskWithProgress($project, $member);
        $ref = ['source_type' => 'wbs_task', 'source_id' => $task->id, 'project_id' => $project->id, 'label' => 'Task source'];
        $resolver = app(CedExecutiveSourceLinkResolver::class);
        self::assertNull($resolver->resolve($project, $ref));
        self::assertNull($resolver->resolve($project, [...$ref, 'source_id' => 'missing']));
        self::assertNull($resolver->resolve($project, [...$ref, 'source_type' => 'unsupported']));
        self::assertNull($resolver->resolve($project, [...$ref, 'project_id' => $foreign->id]));
        $this->get(route('projects.wbs.tasks.show', [$project, $task]))->assertForbidden();
        $this->actingAs($outsider)->get(route('projects.wbs.tasks.show', [$project, $task]))->assertForbidden();
        $this->actingAs($member)->get(route('projects.wbs.tasks.show', [$foreign, $task]))->assertForbidden();
    }

    public function test_authorized_portfolio_is_membership_scoped_and_renders_dimension_specific_provenance(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $foreign = $this->project('CED-PROJECT-002', 'CED-002');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $foreign->members()->attach(User::factory()->create(), ['role' => 'EXECUTIVE']);
        $task = $this->taskWithProgress($project, $member);
        $transaction = $this->postedTransaction($project, $member, 'CED-FIN-001');

        $response = $this->actingAs($member)->get(route('projects.portfolio'));

        $response->assertOk()->assertSee('CED-001')->assertSee(route('projects.executive', $project), false)
            ->assertSee(route('projects.finance.transactions.show', [$project, $transaction]), false)
            ->assertDontSee(route('projects.schedule.show', $project), false)->assertDontSee('CED-002');
        self::assertCount(1, $response->viewData('cards'));
        self::assertContains($task->id, $response->viewData('cards')[0]['schedule_source_scope']['task_ids']);
    }

    public function test_guest_and_outsider_cannot_access_project_portfolio_data(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $this->get(route('projects.portfolio'))->assertForbidden();
        $this->actingAs(User::factory()->create())->get(route('projects.portfolio'))->assertOk()->assertSee('No projects in permitted scope')->assertDontSee($project->id);
        $this->get(route('projects.executive', $project))->assertForbidden();
    }

    public function test_health_has_exact_boundary_keys_truthful_dimension_scope_and_cost_resolver_contract(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $this->taskWithProgress($project, $member);
        $transaction = $this->postedTransaction($project, $member, 'CED-FIN-001');
        $foreign = $this->project('CED-PROJECT-002', 'CED-002');
        $foreignTransaction = $this->postedTransaction($foreign, $member, 'CED-FIN-FOREIGN');

        $response = $this->actingAs($member)->get(route('projects.portfolio'));
        $health = $response->viewData('cards')[0]['health'];
        self::assertSame(['project_id', 'rule_version', 'calculated_at', 'status', 'dimensions', 'reasons', 'source_scope', 'explanation'], array_keys($health));
        self::assertSame(['schedule', 'cost', 'risk', 'freshness'], array_keys($health['source_scope']));
        self::assertSame('finance_transaction', $health['source_scope']['cost'][0]['source_type']);
        self::assertSame($transaction->id, $health['source_scope']['cost'][0]['source_id']);
        self::assertSame($project->id, $health['source_scope']['cost'][0]['project_id']);
        $resolver = app(CedExecutiveSourceLinkResolver::class);
        $link = $resolver->resolve($project, $health['source_scope']['cost'][0]);
        self::assertSame('projects.finance.transactions.show', $link['route_name']);
        self::assertSame(['project' => $project->id, 'transaction' => $transaction->id], ['project' => $link['route_params']['project']->id, 'transaction' => $link['route_params']['transaction']->id]);
        self::assertSame(route('projects.finance.transactions.show', [$project, $transaction]), $link['url']);
        self::assertNull($resolver->resolve($project, [...$health['source_scope']['cost'][0], 'project_id' => $foreign->id]));
        self::assertNull($resolver->resolve($project, [...$health['source_scope']['cost'][0], 'source_id' => 'missing']));
        self::assertNull($resolver->resolve($project, ['source_type' => 'project', 'source_id' => $project->id, 'project_id' => $project->id, 'label' => 'Project fallback']));
        self::assertNotSame('project', $health['source_scope']['schedule'][0]['source_type']);
        self::assertNotSame('project', $health['source_scope']['freshness'][0]['source_type']);
        self::assertNotSame($foreignTransaction->id, $health['source_scope']['cost'][0]['source_id']);
    }

    public function test_finance_boundary_is_delegated_without_portfolio_aggregation(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $this->taskWithProgress($project, $member);
        $finance = $this->mock(CedFinanceV1ReadModel::class);
        $finance->shouldReceive('forProject')->twice()->withArgs(fn (Project $value): bool => $value->is($project))->andReturn(['rule_version' => 'CED_FINANCE_V1', 'calculated_at' => '2026-05-06T12:00:00Z', 'project_id' => $project->id, 'contributing_source_record_scope' => ['project_id' => $project->id, 'status' => 'POSTED', 'included_record_ids' => []], 'income' => '0.00', 'expense' => '0.00', 'net' => '0.00', 'explanation' => 'Canonical finance read boundary.']);
        $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
    }

    public function test_empty_and_incomplete_inputs_are_truthfully_unavailable(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-PROJECT-001', 'CED-001');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $this->actingAs($member)->get(route('projects.portfolio'))->assertOk()->assertSee('Health unavailable')->assertSee('Not available from accepted inputs.')->assertDontSee('GREEN');
    }

    public function test_decision_signal_is_derived_for_amber_and_red_health_with_explainable_reasons_and_project_drilldown(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $amberProject = $this->project('CED-PROJECT-AMBER', 'CED-001');
            $redProject = $this->project('CED-PROJECT-RED', 'CED-002');
            $member = User::factory()->create();
            $amberProject->members()->attach($member, ['role' => 'EXECUTIVE']);
            $redProject->members()->attach($member, ['role' => 'EXECUTIVE']);
            $amberTask = $this->taskWithProgress($amberProject, $member);
            $this->taskWithProgress($redProject, $member);
            $redTransaction = $this->postedTransaction($redProject, $member, 'CED-FIN-RED', '1100.00');

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);
            $amber = $cards->get('CED-001');
            $red = $cards->get('CED-002');

            self::assertSame('AMBER', $amber['health']['status']);
            self::assertTrue($amber['decision_required']['required']);
            self::assertSame($amber['health']['status'], $amber['decision_required']['severity']);
            self::assertSame(['SCHEDULE'], array_keys($amber['decision_required']['reasons']));
            self::assertSame($amber['health']['dimensions']['SCHEDULE']['status'], $amber['decision_required']['reasons']['SCHEDULE']['status']);
            self::assertSame($amber['health']['dimensions']['SCHEDULE']['reason'], $amber['decision_required']['reasons']['SCHEDULE']['reason']);
            self::assertSame($amber['health']['source_scope']['schedule'], $amber['decision_required']['reasons']['SCHEDULE']['source_scope']);
            self::assertSame('RED', $red['health']['status']);
            self::assertTrue($red['decision_required']['required']);
            self::assertSame($red['health']['status'], $red['decision_required']['severity']);
            self::assertSame(['SCHEDULE', 'COST'], array_keys($red['decision_required']['reasons']));
            self::assertSame($red['health']['dimensions']['COST']['reason'], $red['decision_required']['reasons']['COST']['reason']);
            self::assertSame($red['health']['source_scope']['cost'], $red['decision_required']['reasons']['COST']['source_scope']);
            self::assertSame(2, substr_count($response->getContent(), 'data-decision-required="true"'));
            self::assertStringContainsString('data-decision-source-id="'.$amberTask->id.'"', $response->getContent());
            self::assertStringContainsString('data-decision-source-id="'.$redTransaction->id.'"', $response->getContent());
            $response->assertSee('Decision required')->assertSee('Severity: AMBER')->assertSee('Severity: RED')
                ->assertSee($amber['decision_required']['reasons']['SCHEDULE']['reason'])
                ->assertSee($red['decision_required']['reasons']['COST']['reason'])
                ->assertSee(route('projects.executive', $amberProject), false)
                ->assertSee(route('projects.executive', $redProject), false);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_decision_signal_is_absent_for_green_unknown_unavailable_and_malformed_health(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $greenProject = $this->project('CED-PROJECT-GREEN', 'CED-001');
            $unknownProject = $this->project('CED-PROJECT-UNKNOWN', 'CED-002');
            $unavailableProject = $this->project('CED-PROJECT-UNAVAILABLE', 'CED-003');
            $member = User::factory()->create();

            foreach ([$greenProject, $unknownProject, $unavailableProject] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
            }

            $this->taskWithProgress($greenProject, $member, '50.00');
            $unknownProject->update(['budget_amount' => '0.00']);
            $this->taskWithProgress($unknownProject, $member, '50.00');

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);
            $green = $cards->get('CED-001');
            $unknown = $cards->get('CED-002');
            $unavailable = $cards->get('CED-003');

            self::assertSame('GREEN', $green['health']['status']);
            self::assertFalse($green['decision_required']['required']);
            self::assertSame('UNKNOWN', $unknown['health']['status']);
            self::assertFalse($unknown['decision_required']['required']);
            self::assertNull($unavailable['health']);
            self::assertFalse($unavailable['decision_required']['required']);
            self::assertStringNotContainsString('Decision required', $response->getContent());

            $malformed = $this->cardForHealth($unavailableProject, ['status' => 'AMBER']);
            self::assertNull($malformed['health']);
            self::assertSame(['required' => false, 'severity' => null, 'reasons' => []], $malformed['decision_required']);

            $reasonlessHealth = $green['health'];
            $reasonlessHealth['status'] = 'AMBER';
            $reasonlessHealth['dimensions']['SCHEDULE']['status'] = 'AMBER';
            $reasonlessHealth['dimensions']['SCHEDULE']['reason'] = '';
            $reasonlessHealth['reasons']['SCHEDULE'] = '';
            $reasonless = $this->cardForHealth($unavailableProject, $reasonlessHealth);
            self::assertFalse($reasonless['decision_required']['required']);

            $unrecognizedHealth = $green['health'];
            $unrecognizedHealth['status'] = 'BLUE';
            $unrecognized = $this->cardForHealth($unavailableProject, $unrecognizedHealth);
            self::assertFalse($unrecognized['decision_required']['required']);
            self::assertStringNotContainsString('Decision required', view('projects.portfolio', ['cards' => [$reasonless, $unrecognized]])->render());
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_decision_signal_is_deterministic_at_a_frozen_clock_and_preserves_portfolio_order(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $second = $this->project('CED-PROJECT-002', 'CED-002');
            $first = $this->project('CED-PROJECT-001', 'CED-001');
            $member = User::factory()->create();
            $second->members()->attach($member, ['role' => 'EXECUTIVE']);
            $first->members()->attach($member, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($second, $member);
            $this->taskWithProgress($first, $member);

            $firstEvaluation = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk()->viewData('cards');
            $secondEvaluation = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk()->viewData('cards');

            self::assertSame(['CED-001', 'CED-002'], array_map(fn (array $card): string => $card['project']->code, $firstEvaluation));
            self::assertSame(
                array_column($firstEvaluation, 'decision_required'),
                array_column($secondEvaluation, 'decision_required'),
            );
            self::assertTrue($firstEvaluation[0]['decision_required']['required']);
            self::assertTrue($firstEvaluation[1]['decision_required']['required']);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_health_risk_explanation_surface_renders_canonical_summary_dimensions_values_units_reasons_and_provenance(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $project = $this->project('CED-PROJECT-EXPLANATION', 'CED-EXPLANATION');
            $member = User::factory()->create();
            $project->members()->attach($member, ['role' => 'EXECUTIVE']);
            $task = $this->taskWithProgress($project, $member);
            $transaction = $this->postedTransaction($project, $member, 'CED-FIN-EXPLANATION', '950.00');
            $risk = ProjectRisk::query()->create([
                'id' => 'CED-RISK-EXPLANATION',
                'project_id' => $project->id,
                'title' => 'Late approved delivery',
                'severity' => 'HIGH',
                'status' => 'OPEN',
            ]);

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $health = $response->viewData('cards')[0]['health'];
            $content = $response->getContent();

            self::assertNotNull($health);
            self::assertSame('CED_HEALTH_V1_0_0', $health['rule_version']);
            self::assertSame('2026-05-06T12:00:00Z', $health['calculated_at']);
            self::assertStringContainsString('data-health-status="'.$health['status'].'"', $content);
            $response->assertSee($health['explanation'])
                ->assertSee('Rule version: '.$health['rule_version'])
                ->assertSee('Calculated at: '.$health['calculated_at']);

            $dimensionPositions = [];
            foreach ($health['dimensions'] as $dimension => $signal) {
                self::assertSame($signal['reason'], $health['reasons'][$dimension]);
                self::assertStringContainsString('data-dimension="'.$dimension.'"', $content);
                $response->assertSee($dimension.' · '.$signal['status'])
                    ->assertSee('Dimension reason: '.$signal['reason'])
                    ->assertSee('Canonical reason: '.$health['reasons'][$dimension]);
                if ($signal['value'] !== null) {
                    $response->assertSee('Value: '.$signal['value'].' '.$signal['unit']);
                }
                $dimensionPositions[] = strpos($content, 'data-dimension="'.$dimension.'"');
            }
            self::assertSame($dimensionPositions, collect($dimensionPositions)->sort()->values()->all());

            $scheduleLink = app(CedExecutiveSourceLinkResolver::class)->resolve($project, $health['source_scope']['schedule'][0]);
            $costLink = app(CedExecutiveSourceLinkResolver::class)->resolve($project, $health['source_scope']['cost'][0]);
            self::assertSame($task->id, $scheduleLink['source_id']);
            self::assertSame($transaction->id, $costLink['source_id']);
            $response->assertSee($scheduleLink['url'], false)
                ->assertSee($costLink['url'], false)
                ->assertSee("Risk {$risk->id} · {$risk->title}");
            self::assertStringNotContainsString('data-source-id="'.$risk->id.'"', $content);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_health_risk_explanation_surface_preserves_unknown_unavailable_and_separates_project_status(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $healthyProject = $this->project('CED-PROJECT-HEALTHY', 'CED-HEALTHY');
            $healthyMember = User::factory()->create();
            $healthyProject->members()->attach($healthyMember, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($healthyProject, $healthyMember, '50.00');

            $healthy = $this->actingAs($healthyMember)->get(route('projects.portfolio'))->assertOk();
            self::assertSame('ACTIVE', $healthy->viewData('cards')[0]['status']);
            self::assertSame('GREEN', $healthy->viewData('cards')[0]['health']['status']);
            $healthy->assertSee('Project status: ACTIVE')->assertSee('data-health-status="GREEN"', false);

            $unknownProject = $this->project('CED-PROJECT-UNKNOWN-EXPLANATION', 'CED-UNKNOWN');
            $unknownProject->update(['budget_amount' => '0.00']);
            $unknownMember = User::factory()->create();
            $unknownProject->members()->attach($unknownMember, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($unknownProject, $unknownMember, '50.00');

            $unknown = $this->actingAs($unknownMember)->get(route('projects.portfolio'))->assertOk();
            $unknownHealth = $unknown->viewData('cards')[0]['health'];
            self::assertSame('UNKNOWN', $unknownHealth['status']);
            self::assertNull($unknownHealth['dimensions']['COST']['value']);
            $unknown->assertSee('data-health-status="UNKNOWN"', false)
                ->assertSee('COST · UNKNOWN')
                ->assertSee($unknownHealth['dimensions']['COST']['reason'])
                ->assertDontSee('Value: 0 percent_of_budget');

            $unavailableProject = $this->project('CED-PROJECT-UNAVAILABLE-EXPLANATION', 'CED-UNAVAILABLE');
            $unavailableMember = User::factory()->create();
            $unavailableProject->members()->attach($unavailableMember, ['role' => 'EXECUTIVE']);

            $unavailable = $this->actingAs($unavailableMember)->get(route('projects.portfolio'))->assertOk();
            self::assertNull($unavailable->viewData('cards')[0]['health']);
            $unavailable->assertSee('Project status: ACTIVE')
                ->assertSee('Health unavailable')
                ->assertSee('Not available from accepted inputs.')
                ->assertDontSee('data-health-status=', false);

            $malformed = $this->cardForHealth($unavailableProject, [
                'project_id' => $unavailableProject->id,
                'rule_version' => 'CED_HEALTH_V1_0_0',
                'calculated_at' => '2026-05-06T12:00:00Z',
                'status' => 'GREEN',
                'dimensions' => [],
                'reasons' => [],
                'source_scope' => ['schedule' => [], 'cost' => [], 'risk' => [], 'freshness' => []],
                'explanation' => 'MALFORMED_GREEN_EXPLANATION',
            ]);
            $malformedContent = view('projects.portfolio', ['cards' => [$malformed]])->render();
            self::assertStringContainsString('Health unavailable', $malformedContent);
            self::assertStringNotContainsString('data-health-status="GREEN"', $malformedContent);
            self::assertStringNotContainsString('MALFORMED_GREEN_EXPLANATION', $malformedContent);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_health_risk_explanation_preserves_decision_signal_and_portfolio_order(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $second = $this->project('CED-PROJECT-ORDER-002', 'CED-ORDER-002');
            $first = $this->project('CED-PROJECT-ORDER-001', 'CED-ORDER-001');
            $member = User::factory()->create();
            $second->members()->attach($member, ['role' => 'EXECUTIVE']);
            $first->members()->attach($member, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($second, $member);
            $this->taskWithProgress($first, $member);

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = $response->viewData('cards');
            $content = $response->getContent();

            self::assertSame(['CED-ORDER-001', 'CED-ORDER-002'], array_map(fn (array $card): string => $card['project']->code, $cards));
            self::assertTrue($cards[0]['decision_required']['required']);
            self::assertTrue($cards[1]['decision_required']['required']);
            self::assertSame(2, substr_count($content, 'data-decision-required="true"'));
            self::assertSame(2, substr_count($content, 'data-health-explanation'));
            self::assertLessThan(
                strpos($content, 'data-project-id="'.$second->id.'"'),
                strpos($content, 'data-project-id="'.$first->id.'"'),
            );
            $response->assertSee($cards[0]['health']['explanation'])
                ->assertSee($cards[1]['health']['explanation'])
                ->assertSee($cards[0]['decision_required']['reasons']['SCHEDULE']['reason'])
                ->assertSee($cards[1]['decision_required']['reasons']['SCHEDULE']['reason']);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_schedule_progress_exception_projects_canonical_schedule_dimension_verbatim_for_amber_and_red(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $amberProject = $this->project('CED-SCHEDULE-EXCEPTION-AMBER', 'CED-EXCEPTION-001');
            $redProject = $this->project('CED-SCHEDULE-EXCEPTION-RED', 'CED-EXCEPTION-002');
            $member = User::factory()->create();

            foreach ([$amberProject, $redProject] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
            }

            $this->taskWithProgress($amberProject, $member);
            $this->taskWithProgress($redProject, $member, '0.00');

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);
            $projection = app(CedScheduleProgressExceptionV1Projection::class);

            foreach ([
                'CED-EXCEPTION-001' => 'AMBER',
                'CED-EXCEPTION-002' => 'RED',
            ] as $code => $expectedStatus) {
                $card = $cards->get($code);
                $health = $card['health'];
                $schedule = $health['dimensions']['SCHEDULE'];
                $expected = [
                    'dimension' => 'SCHEDULE',
                    'project_id' => $health['project_id'],
                    'rule_version' => $health['rule_version'],
                    'calculated_at' => $health['calculated_at'],
                    'status' => $schedule['status'],
                    'value' => $schedule['value'],
                    'unit' => $schedule['unit'],
                    'reason' => $health['reasons']['SCHEDULE'],
                    'source_scope' => $health['source_scope']['schedule'],
                ];

                self::assertSame($expectedStatus, $health['status']);
                self::assertSame($expectedStatus, $schedule['status']);
                self::assertSame($schedule['reason'], $health['reasons']['SCHEDULE']);
                self::assertSame($expected, $card['schedule_progress_exception']);
                self::assertSame($expected, $projection->forProject($card['project'], $health));
                self::assertSame(
                    $projection->forProject($card['project'], $health),
                    $projection->forProject($card['project'], $health),
                );

                foreach ($expected['source_scope'] as $source) {
                    self::assertSame($card['project']->id, $source['project_id']);
                    $response->assertSee($source['label'])
                        ->assertSee('Source type: '.$source['source_type'])
                        ->assertSee('Source ID: '.$source['source_id'])
                        ->assertSee('Project ID: '.$source['project_id']);
                }

                $response->assertSeeText('Status: '.$expected['status'])
                    ->assertSeeText('Schedule variance: '.$expected['value'].' '.$expected['unit'])
                    ->assertSeeText('Reason: '.$expected['reason'])
                    ->assertSee('Rule version: '.$expected['rule_version'])
                    ->assertSee('Calculated at: '.$expected['calculated_at']);
            }

            self::assertSame(2, substr_count($response->getContent(), 'data-schedule-progress-exception="true"'));
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_schedule_progress_exception_omits_green_unknown_unavailable_and_malformed_payloads(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $amberProject = $this->project('CED-SCHEDULE-INVALID-AMBER', 'CED-INVALID-AMBER');
            $foreignProject = $this->project('CED-SCHEDULE-INVALID-FOREIGN', 'CED-INVALID-FOREIGN');
            $member = User::factory()->create();
            $amberProject->members()->attach($member, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($amberProject, $member);

            $health = app(CedProjectHealthV1ReadModel::class)->forProject($amberProject);
            $projection = app(CedScheduleProgressExceptionV1Projection::class);
            self::assertSame('AMBER', $health['dimensions']['SCHEDULE']['status']);

            $green = $health;
            $green['status'] = 'GREEN';
            $green['dimensions']['SCHEDULE']['status'] = 'GREEN';
            $unknown = $health;
            $unknown['status'] = 'UNKNOWN';
            $unknown['dimensions']['SCHEDULE']['status'] = 'UNKNOWN';
            $unknown['dimensions']['SCHEDULE']['value'] = null;
            $greenSchedule = $health;
            $greenSchedule['dimensions']['SCHEDULE']['status'] = 'GREEN';
            $reasonMismatch = $health;
            $reasonMismatch['reasons']['SCHEDULE'] = 'A mismatched reason must not be used.';
            $nonnumericValue = $health;
            $nonnumericValue['dimensions']['SCHEDULE']['value'] = '-10.0';
            $nonFiniteValue = $health;
            $nonFiniteValue['dimensions']['SCHEDULE']['value'] = NAN;
            $missingUnit = $health;
            unset($missingUnit['dimensions']['SCHEDULE']['unit']);
            $emptyReason = $health;
            $emptyReason['dimensions']['SCHEDULE']['reason'] = ' ';
            $emptyReason['reasons']['SCHEDULE'] = ' ';
            $foreignHealth = $health;
            $foreignHealth['project_id'] = $foreignProject->id;
            $nonListSources = $health;
            $nonListSources['source_scope']['schedule'] = ['source' => $health['source_scope']['schedule'][0]];
            $malformedSource = $health;
            unset($malformedSource['source_scope']['schedule'][0]['label']);
            $foreignSource = $health;
            $foreignSource['source_scope']['schedule'][0]['project_id'] = $foreignProject->id;
            $emptySourceIdentity = $health;
            $emptySourceIdentity['source_scope']['schedule'][0]['source_id'] = '';

            foreach ([
                'null payload' => null,
                'empty payload' => [],
                'green payload' => $green,
                'unknown payload' => $unknown,
                'green schedule under elevated overall health' => $greenSchedule,
                'reason mismatch' => $reasonMismatch,
                'nonnumeric value' => $nonnumericValue,
                'non-finite value' => $nonFiniteValue,
                'missing unit' => $missingUnit,
                'empty reason' => $emptyReason,
                'foreign project' => $foreignHealth,
                'non-list sources' => $nonListSources,
                'malformed source' => $malformedSource,
                'foreign source' => $foreignSource,
                'empty source identity' => $emptySourceIdentity,
            ] as $case => $payload) {
                self::assertNull($projection->forProject($amberProject, $payload), $case);
            }

            $trustedStatusHealth = $health;
            $trustedStatusHealth['status'] = 'RED';
            $trustedStatusHealth['dimensions']['SCHEDULE']['status'] = 'RED';
            $trustedStatusHealth['dimensions']['SCHEDULE']['value'] = 0;
            $trustedStatusHealth['dimensions']['SCHEDULE']['unit'] = 'canonical_unit';
            $trustedStatusHealth['dimensions']['SCHEDULE']['reason'] = 'Canonical reason copied without a projection threshold.';
            $trustedStatusHealth['reasons']['SCHEDULE'] = $trustedStatusHealth['dimensions']['SCHEDULE']['reason'];
            $trustedProjection = $projection->forProject($amberProject, $trustedStatusHealth);
            self::assertSame('RED', $trustedProjection['status']);
            self::assertSame(0, $trustedProjection['value']);
            self::assertSame('canonical_unit', $trustedProjection['unit']);
            self::assertSame($trustedStatusHealth['reasons']['SCHEDULE'], $trustedProjection['reason']);

            $greenProject = $this->project('CED-SCHEDULE-GREEN', 'CED-NO-EXCEPTION-GREEN');
            $unknownProject = $this->project('CED-SCHEDULE-UNKNOWN', 'CED-NO-EXCEPTION-UNKNOWN');
            $unavailableProject = $this->project('CED-SCHEDULE-UNAVAILABLE', 'CED-NO-EXCEPTION-UNAVAILABLE');
            foreach ([$greenProject, $unknownProject, $unavailableProject] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
            }
            $this->taskWithProgress($greenProject, $member, '50.00');
            $unknownProject->update(['budget_amount' => '0.00']);
            $this->taskWithProgress($unknownProject, $member, '50.00');

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);
            self::assertNull($cards['CED-NO-EXCEPTION-GREEN']['schedule_progress_exception']);
            self::assertNull($cards['CED-NO-EXCEPTION-UNKNOWN']['schedule_progress_exception']);
            self::assertNull($cards['CED-NO-EXCEPTION-UNAVAILABLE']['schedule_progress_exception']);
            self::assertSame('UNKNOWN', $cards['CED-NO-EXCEPTION-UNKNOWN']['health']['status']);
            self::assertNull($cards['CED-NO-EXCEPTION-UNAVAILABLE']['health']);
            self::assertSame(1, substr_count($response->getContent(), 'data-schedule-progress-exception="true"'));
            $response->assertSee('Health unavailable')
                ->assertSee('data-health-status="UNKNOWN"', false)
                ->assertDontSee('Value: 0 canonical_unit')
                ->assertDontSee('A mismatched reason must not be used.');

            $malformedCard = $this->cardForHealth($amberProject, $reasonMismatch);
            $malformedContent = view('projects.portfolio', ['cards' => [$malformedCard]])->render();
            self::assertNull($malformedCard['schedule_progress_exception']);
            self::assertStringNotContainsString('data-schedule-progress-exception="true"', $malformedContent);
            self::assertStringNotContainsString('A mismatched reason must not be used.', $malformedContent);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_schedule_progress_exception_preserves_health_decision_signal_and_portfolio_order(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $second = $this->project('CED-SCHEDULE-ORDER-002', 'CED-SCHEDULE-ORDER-002');
            $first = $this->project('CED-SCHEDULE-ORDER-001', 'CED-SCHEDULE-ORDER-001');
            $member = User::factory()->create();
            $second->members()->attach($member, ['role' => 'EXECUTIVE']);
            $first->members()->attach($member, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($second, $member);
            $this->taskWithProgress($first, $member);

            $firstResponse = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $secondResponse = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $firstCards = $firstResponse->viewData('cards');
            $secondCards = $secondResponse->viewData('cards');

            self::assertSame(['CED-SCHEDULE-ORDER-001', 'CED-SCHEDULE-ORDER-002'], array_map(fn (array $card): string => $card['project']->code, $firstCards));
            self::assertSame(array_column($firstCards, 'health'), array_column($secondCards, 'health'));
            self::assertSame(array_column($firstCards, 'decision_required'), array_column($secondCards, 'decision_required'));
            self::assertSame(array_column($firstCards, 'schedule_progress_exception'), array_column($secondCards, 'schedule_progress_exception'));
            self::assertSame(array_column($firstCards, 'finance'), array_column($secondCards, 'finance'));
            self::assertSame(array_column($firstCards, 'schedule_source_scope'), array_column($secondCards, 'schedule_source_scope'));

            foreach ($firstCards as $card) {
                self::assertSame('ACTIVE', $card['status']);
                self::assertTrue($card['decision_required']['required']);
                self::assertSame($card['health']['status'], $card['decision_required']['severity']);
                self::assertSame($card['health']['dimensions']['SCHEDULE']['status'], $card['schedule_progress_exception']['status']);
                self::assertSame($card['health']['reasons']['SCHEDULE'], $card['schedule_progress_exception']['reason']);
                self::assertSame($card['health']['source_scope']['schedule'], $card['schedule_progress_exception']['source_scope']);
            }

            self::assertSame(2, substr_count($firstResponse->getContent(), 'data-schedule-progress-exception="true"'));
            self::assertLessThan(
                strpos($firstResponse->getContent(), 'data-project-id="'.$second->id.'"'),
                strpos($firstResponse->getContent(), 'data-project-id="'.$first->id.'"'),
            );

            $noProjects = $this->actingAs(User::factory()->create())->get(route('projects.portfolio'))->assertOk();
            self::assertSame([], $noProjects->viewData('cards'));
            $noProjects->assertSee('No projects in permitted scope')
                ->assertDontSee('data-schedule-progress-exception="true"', false);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_finance_exception_projects_canonical_cost_and_finance_explanation_verbatim_for_amber_and_red(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $amberProject = $this->project('CED-FINANCE-EXCEPTION-AMBER', 'CED-FINANCE-001');
            $redProject = $this->project('CED-FINANCE-EXCEPTION-RED', 'CED-FINANCE-002');
            $member = User::factory()->create();

            foreach ([$amberProject, $redProject] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
                $this->taskWithProgress($project, $member);
            }

            $this->postedTransaction($amberProject, $member, 'CED-FINANCE-AMBER-EXPENSE', '950.00');
            $this->postedTransaction($amberProject, $member, 'CED-FINANCE-AMBER-INCOME', '125.50', 'INCOME');
            $this->postedTransaction($redProject, $member, 'CED-FINANCE-RED-EXPENSE', '1100.00');
            $this->postedTransaction($redProject, $member, 'CED-FINANCE-RED-INCOME', '200.25', 'INCOME');

            $portfolio = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($portfolio->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);

            foreach ([
                'CED-FINANCE-001' => 'AMBER',
                'CED-FINANCE-002' => 'RED',
            ] as $code => $expectedStatus) {
                $card = $cards->get($code);
                $cost = $card['health']['dimensions']['COST'];
                $exception = $card['finance_exception'];
                $expected = [
                    'dimension' => 'COST',
                    'project_id' => $card['health']['project_id'],
                    'health_rule_version' => $card['health']['rule_version'],
                    'health_calculated_at' => $card['health']['calculated_at'],
                    'finance_rule_version' => $card['finance']['rule_version'],
                    'finance_calculated_at' => $card['finance']['calculated_at'],
                    'status' => $cost['status'],
                    'value' => $cost['value'],
                    'unit' => $cost['unit'],
                    'reason' => $card['health']['reasons']['COST'],
                    'source_scope' => $card['health']['source_scope']['cost'],
                ];

                self::assertSame($expectedStatus, $cost['status']);
                self::assertSame($cost['reason'], $card['health']['reasons']['COST']);
                self::assertSame($expected, $exception);
                self::assertSame(
                    $card['finance']['contributing_source_record_scope']['included_record_ids'],
                    array_column($exception['source_scope'], 'source_id'),
                );

                $portfolio->assertSeeText('Status: '.$exception['status'])
                    ->assertSeeText('Cost projection: '.$exception['value'].' '.$exception['unit'])
                    ->assertSeeText('Reason: '.$exception['reason'])
                    ->assertSee('Health rule version: '.$exception['health_rule_version'])
                    ->assertSee('Health calculated at: '.$exception['health_calculated_at'])
                    ->assertSee('Finance rule version: '.$exception['finance_rule_version'])
                    ->assertSee('Finance calculated at: '.$exception['finance_calculated_at'])
                    ->assertSeeText('Income: '.$card['finance']['income'].' · Expense: '.$card['finance']['expense'].' · Net: '.$card['finance']['net'])
                    ->assertSee($card['finance']['explanation']);

                $detail = $this->actingAs($member)->get(route('projects.executive', $card['project']))->assertOk();
                self::assertSame($card['finance'], $detail->viewData('finance'));
                self::assertSame($exception, $detail->viewData('financeException'));
                self::assertSame(1, substr_count($detail->getContent(), 'data-finance-exception="true"'));
                $detail->assertSeeText('Canonical finance source scope')
                    ->assertSee($card['finance']['explanation']);
            }

            self::assertSame(2, substr_count($portfolio->getContent(), 'data-finance-exception="true"'));
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_finance_exception_omits_green_unknown_unavailable_malformed_and_cross_bound_drift(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $project = $this->project('CED-FINANCE-INVALID', 'CED-FINANCE-INVALID');
            $foreignProject = $this->project('CED-FINANCE-FOREIGN', 'CED-FINANCE-FOREIGN');
            $member = User::factory()->create();
            $project->members()->attach($member, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($project, $member);
            $transaction = $this->postedTransaction($project, $member, 'CED-FINANCE-INVALID-POSTED', '950.00');
            $health = app(CedProjectHealthV1ReadModel::class)->forProject($project);
            $finance = app(CedFinanceV1ReadModel::class)->forProject($project);
            $projection = app(CedFinanceExceptionV1Projection::class);
            self::assertNotNull($projection->forProject($project, $health, $finance));

            $green = $health;
            $green['dimensions']['COST']['status'] = 'GREEN';
            $unknown = $health;
            $unknown['status'] = 'UNKNOWN';
            $unknown['dimensions']['COST']['status'] = 'UNKNOWN';
            $unknown['dimensions']['COST']['value'] = null;
            $unrecognizedHealth = $health;
            $unrecognizedHealth['status'] = 'BLUE';
            $unrecognizedCost = $health;
            $unrecognizedCost['dimensions']['COST']['status'] = 'BLUE';
            $nonFinite = $health;
            $nonFinite['dimensions']['COST']['value'] = INF;
            $emptyUnit = $health;
            $emptyUnit['dimensions']['COST']['unit'] = ' ';
            $emptyReason = $health;
            $emptyReason['dimensions']['COST']['reason'] = ' ';
            $emptyReason['reasons']['COST'] = ' ';
            $reasonMismatch = $health;
            $reasonMismatch['reasons']['COST'] = 'A mismatched reason must be rejected.';
            $wrongHealthVersion = $health;
            $wrongHealthVersion['rule_version'] = 'CED_HEALTH_WRONG';
            $wrongHealthProject = $health;
            $wrongHealthProject['project_id'] = $foreignProject->id;
            $nonListSources = $health;
            $nonListSources['source_scope']['cost'] = ['source' => $health['source_scope']['cost'][0]];
            $duplicateSources = $health;
            $duplicateSources['source_scope']['cost'][] = $health['source_scope']['cost'][0];
            $duplicateFinance = $finance;
            $duplicateFinance['contributing_source_record_scope']['included_record_ids'][] = $transaction->id;
            $foreignSource = $health;
            $foreignSource['source_scope']['cost'][0]['project_id'] = $foreignProject->id;
            $unsupportedSource = $health;
            $unsupportedSource['source_scope']['cost'][0]['source_type'] = 'unsupported';
            $unequalSources = $finance;
            $unequalSources['contributing_source_record_scope']['included_record_ids'] = ['CED-FINANCE-OTHER'];
            $wrongFinanceVersion = $finance;
            $wrongFinanceVersion['rule_version'] = 'CED_FINANCE_WRONG';
            $wrongFinanceProject = $finance;
            $wrongFinanceProject['project_id'] = $foreignProject->id;
            $wrongScopeProject = $finance;
            $wrongScopeProject['contributing_source_record_scope']['project_id'] = $foreignProject->id;
            $wrongScopeStatus = $finance;
            $wrongScopeStatus['contributing_source_record_scope']['status'] = 'APPROVED';
            $nonListFinanceIds = $finance;
            $nonListFinanceIds['contributing_source_record_scope']['included_record_ids'] = ['source' => $transaction->id];
            $malformedIncome = $finance;
            $malformedIncome['income'] = '0';
            $malformedExpense = $finance;
            $malformedExpense['expense'] = '950.0';
            $malformedNet = $finance;
            $malformedNet['net'] = 'not-money';
            $emptyExplanation = $finance;
            $emptyExplanation['explanation'] = ' ';

            foreach ([
                'null health' => [null, $finance],
                'null finance' => [$health, null],
                'empty health' => [[], $finance],
                'empty finance' => [$health, []],
                'green COST' => [$green, $finance],
                'unknown health and COST' => [$unknown, $finance],
                'unrecognized health status' => [$unrecognizedHealth, $finance],
                'unrecognized COST status' => [$unrecognizedCost, $finance],
                'non-finite COST value' => [$nonFinite, $finance],
                'empty COST unit' => [$emptyUnit, $finance],
                'empty COST reason' => [$emptyReason, $finance],
                'reason mismatch' => [$reasonMismatch, $finance],
                'wrong health version' => [$wrongHealthVersion, $finance],
                'wrong health project' => [$wrongHealthProject, $finance],
                'non-list health sources' => [$nonListSources, $finance],
                'duplicate health and finance sources' => [$duplicateSources, $duplicateFinance],
                'foreign health source' => [$foreignSource, $finance],
                'unsupported health source' => [$unsupportedSource, $finance],
                'unequal health and finance source IDs' => [$health, $unequalSources],
                'wrong finance version' => [$health, $wrongFinanceVersion],
                'wrong finance project' => [$health, $wrongFinanceProject],
                'wrong finance scope project' => [$health, $wrongScopeProject],
                'wrong finance scope status' => [$health, $wrongScopeStatus],
                'non-list finance IDs' => [$health, $nonListFinanceIds],
                'malformed income' => [$health, $malformedIncome],
                'malformed expense' => [$health, $malformedExpense],
                'malformed net' => [$health, $malformedNet],
                'empty explanation' => [$health, $emptyExplanation],
            ] as $case => [$candidateHealth, $candidateFinance]) {
                self::assertNull($projection->forProject($project, $candidateHealth, $candidateFinance), $case);
            }

            $trustedStatus = $health;
            $trustedStatus['status'] = 'RED';
            $trustedStatus['dimensions']['COST']['status'] = 'RED';
            $trustedStatus['dimensions']['COST']['value'] = 0;
            $trustedStatus['dimensions']['COST']['unit'] = 'canonical_unit';
            $trustedStatus['dimensions']['COST']['reason'] = 'Canonical COST status is copied without a projection threshold.';
            $trustedStatus['reasons']['COST'] = $trustedStatus['dimensions']['COST']['reason'];
            $trustedProjection = $projection->forProject($project, $trustedStatus, $finance);
            self::assertSame('RED', $trustedProjection['status']);
            self::assertSame(0, $trustedProjection['value']);
            self::assertSame($trustedStatus['dimensions']['COST']['reason'], $trustedProjection['reason']);

            $nonExceptionMember = User::factory()->create();
            $greenProject = $this->project('CED-FINANCE-GREEN', 'CED-FINANCE-GREEN');
            $unknownProject = $this->project('CED-FINANCE-UNKNOWN', 'CED-FINANCE-UNKNOWN');
            $unavailableProject = $this->project('CED-FINANCE-UNAVAILABLE', 'CED-FINANCE-UNAVAILABLE');
            foreach ([$greenProject, $unknownProject, $unavailableProject] as $candidateProject) {
                $candidateProject->members()->attach($nonExceptionMember, ['role' => 'EXECUTIVE']);
            }
            $this->taskWithProgress($greenProject, $nonExceptionMember, '50.00');
            $unknownProject->update(['budget_amount' => '0.00']);
            $this->taskWithProgress($unknownProject, $nonExceptionMember, '50.00');

            $response = $this->actingAs($nonExceptionMember)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);
            self::assertNull($cards['CED-FINANCE-GREEN']['finance_exception']);
            self::assertNull($cards['CED-FINANCE-UNKNOWN']['finance_exception']);
            self::assertNull($cards['CED-FINANCE-UNAVAILABLE']['finance_exception']);
            self::assertStringNotContainsString('data-finance-exception="true"', $response->getContent());

            $malformedCard = $this->cardForPayloads($project, $reasonMismatch, $finance);
            self::assertNull($malformedCard['finance_exception']);
            self::assertStringNotContainsString('data-finance-exception="true"', view('projects.portfolio', ['cards' => [$malformedCard]])->render());
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_finance_exception_is_deterministic_and_preserves_prior_card_fields_and_order(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $second = $this->project('CED-FINANCE-ORDER-002', 'CED-FINANCE-ORDER-002');
            $first = $this->project('CED-FINANCE-ORDER-001', 'CED-FINANCE-ORDER-001');
            $member = User::factory()->create();

            foreach ([$second, $first] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
                $this->taskWithProgress($project, $member);
            }

            $this->postedTransaction($second, $member, 'CED-FINANCE-ORDER-TX-002', '1100.00');
            $this->postedTransaction($first, $member, 'CED-FINANCE-ORDER-TX-001', '950.00');

            $firstResponse = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $secondResponse = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $firstCards = $firstResponse->viewData('cards');
            $secondCards = $secondResponse->viewData('cards');
            $priorFields = [
                'status',
                'health',
                'decision_required',
                'schedule_progress_exception',
                'finance',
                'schedule_source_scope',
                'evidence_ids',
                'audit_ids',
            ];

            self::assertSame(['CED-FINANCE-ORDER-001', 'CED-FINANCE-ORDER-002'], array_map(fn (array $card): string => $card['project']->code, $firstCards));
            self::assertSame(
                array_map(fn (array $card): string => (string) $card['project']->id, $firstCards),
                array_map(fn (array $card): string => (string) $card['project']->id, $secondCards),
            );
            self::assertSame(array_column($firstCards, 'finance_exception'), array_column($secondCards, 'finance_exception'));
            foreach ($priorFields as $field) {
                self::assertSame(array_column($firstCards, $field), array_column($secondCards, $field), $field);
            }
            self::assertSame([
                'project',
                'status',
                'health',
                'decision_required',
                'schedule_progress_exception',
                'finance_exception',
                'evidence_data_quality_exception',
                'finance',
                'schedule_source_scope',
                'evidence_ids',
                'audit_ids',
            ], array_keys($firstCards[0]));
            self::assertSame(2, substr_count($firstResponse->getContent(), 'data-finance-exception="true"'));
            self::assertLessThan(
                strpos($firstResponse->getContent(), 'data-project-id="'.$second->id.'"'),
                strpos($firstResponse->getContent(), 'data-project-id="'.$first->id.'"'),
            );

            $noProjects = $this->actingAs(User::factory()->create())->get(route('projects.portfolio'))->assertOk();
            self::assertSame([], $noProjects->viewData('cards'));
            $noProjects->assertSee('No projects in permitted scope')
                ->assertDontSee('data-finance-exception="true"', false);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_evidence_data_quality_exception_projects_canonical_freshness_verbatim_for_amber_and_red(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $amberProject = $this->project('CED-EVIDENCE-FRESHNESS-AMBER', 'CED-EVIDENCE-FRESHNESS-001');
            $redProject = $this->project('CED-EVIDENCE-FRESHNESS-RED', 'CED-EVIDENCE-FRESHNESS-002');
            $member = User::factory()->create();

            foreach ([$amberProject, $redProject] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
                $this->taskWithProgress($project, $member, '50.00');
            }

            ProgressEntry::query()->where('project_id', $amberProject->id)->update(['recorded_at' => '2026-05-02 12:00:00']);
            ProgressEntry::query()->where('project_id', $redProject->id)->update(['recorded_at' => '2026-04-28 12:00:00']);

            $response = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);

            foreach ([
                'CED-EVIDENCE-FRESHNESS-001' => 'AMBER',
                'CED-EVIDENCE-FRESHNESS-002' => 'RED',
            ] as $code => $expectedStatus) {
                $card = $cards->get($code);
                $health = $card['health'];
                $freshness = $health['dimensions']['DATA_FRESHNESS'];
                $expected = [
                    'dimension' => 'DATA_FRESHNESS',
                    'project_id' => $health['project_id'],
                    'rule_version' => $health['rule_version'],
                    'calculated_at' => $health['calculated_at'],
                    'status' => $freshness['status'],
                    'value' => $freshness['value'],
                    'unit' => $freshness['unit'],
                    'reason' => $health['reasons']['DATA_FRESHNESS'],
                    'source_scope' => $health['source_scope']['freshness'],
                ];

                self::assertSame($expectedStatus, $health['status']);
                self::assertSame($expectedStatus, $freshness['status']);
                self::assertSame($freshness['reason'], $health['reasons']['DATA_FRESHNESS']);
                self::assertSame($expected, $card['evidence_data_quality_exception']);

                foreach ($expected['source_scope'] as $source) {
                    self::assertSame('progress_entry', $source['source_type']);
                    self::assertSame($card['project']->id, $source['project_id']);
                    $response->assertSee($source['label'])
                        ->assertSee('Source type: '.$source['source_type'])
                        ->assertSee('Source ID: '.$source['source_id'])
                        ->assertSee('Project ID: '.$source['project_id']);
                }

                $response->assertSeeText('Status: '.$expected['status'])
                    ->assertSeeText('Data freshness: '.$expected['value'].' '.$expected['unit'])
                    ->assertSeeText('Reason: '.$expected['reason'])
                    ->assertSee('Rule version: '.$expected['rule_version'])
                    ->assertSee('Calculated at: '.$expected['calculated_at'])
                    ->assertSee(route('projects.executive', $card['project']), false);
            }

            self::assertSame(2, substr_count($response->getContent(), 'data-evidence-data-quality-exception="true"'));
            self::assertSame(2, substr_count($response->getContent(), 'data-evidence-data-quality-drill-down'));
            self::assertDoesNotMatchRegularExpression('/<a[^>]+href="[^"]*(?:PROGRESS|progress)[^"]*"/', $response->getContent());
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_evidence_data_quality_exception_omits_green_unknown_unavailable_and_malformed_payloads(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $project = $this->project('CED-EVIDENCE-FRESHNESS-INVALID', 'CED-EVIDENCE-FRESHNESS-INVALID');
            $foreignProject = $this->project('CED-EVIDENCE-FRESHNESS-FOREIGN', 'CED-EVIDENCE-FRESHNESS-FOREIGN');
            $member = User::factory()->create();
            $project->members()->attach($member, ['role' => 'EXECUTIVE']);
            $this->taskWithProgress($project, $member, '50.00');
            ProgressEntry::query()->where('project_id', $project->id)->update(['recorded_at' => '2026-05-02 12:00:00']);
            $health = app(CedProjectHealthV1ReadModel::class)->forProject($project);
            self::assertSame('AMBER', $health['dimensions']['DATA_FRESHNESS']['status']);

            $wrongOverallStatus = $health;
            $wrongOverallStatus['status'] = 'GREEN';
            $greenFreshness = $health;
            $greenFreshness['dimensions']['DATA_FRESHNESS']['status'] = 'GREEN';
            $unknownFreshness = $health;
            $unknownFreshness['dimensions']['DATA_FRESHNESS']['status'] = 'UNKNOWN';
            $unknownFreshness['dimensions']['DATA_FRESHNESS']['value'] = null;
            $missingFreshness = $health;
            unset($missingFreshness['dimensions']['DATA_FRESHNESS']);
            $wrongProject = $health;
            $wrongProject['project_id'] = $foreignProject->id;
            $wrongVersion = $health;
            $wrongVersion['rule_version'] = 'CED_HEALTH_WRONG';
            $emptyCalculatedAt = $health;
            $emptyCalculatedAt['calculated_at'] = ' ';
            $nonnumericValue = $health;
            $nonnumericValue['dimensions']['DATA_FRESHNESS']['value'] = '96';
            $nonFiniteValue = $health;
            $nonFiniteValue['dimensions']['DATA_FRESHNESS']['value'] = INF;
            $emptyUnit = $health;
            $emptyUnit['dimensions']['DATA_FRESHNESS']['unit'] = ' ';
            $emptyReason = $health;
            $emptyReason['dimensions']['DATA_FRESHNESS']['reason'] = ' ';
            $emptyReason['reasons']['DATA_FRESHNESS'] = ' ';
            $reasonMismatch = $health;
            $reasonMismatch['reasons']['DATA_FRESHNESS'] = 'MISMATCHED_FRESHNESS_REASON';
            $nonListSources = $health;
            $nonListSources['source_scope']['freshness'] = ['source' => $health['source_scope']['freshness'][0]];
            $emptySources = $health;
            $emptySources['source_scope']['freshness'] = [];
            $duplicateSources = $health;
            $duplicateSources['source_scope']['freshness'][] = $health['source_scope']['freshness'][0];
            $malformedSource = $health;
            unset($malformedSource['source_scope']['freshness'][0]['label']);
            $foreignSource = $health;
            $foreignSource['source_scope']['freshness'][0]['project_id'] = $foreignProject->id;
            $nonProgressSource = $health;
            $nonProgressSource['source_scope']['freshness'][0]['source_type'] = 'evidence_document';
            $emptySourceId = $health;
            $emptySourceId['source_scope']['freshness'][0]['source_id'] = ' ';
            $emptySourceLabel = $health;
            $emptySourceLabel['source_scope']['freshness'][0]['label'] = ' ';

            foreach ([
                'empty payload' => [],
                'wrong overall status' => $wrongOverallStatus,
                'green freshness' => $greenFreshness,
                'unknown freshness' => $unknownFreshness,
                'missing freshness' => $missingFreshness,
                'wrong project' => $wrongProject,
                'wrong version' => $wrongVersion,
                'empty calculated_at' => $emptyCalculatedAt,
                'nonnumeric value' => $nonnumericValue,
                'non-finite value' => $nonFiniteValue,
                'empty unit' => $emptyUnit,
                'empty reason' => $emptyReason,
                'reason mismatch' => $reasonMismatch,
                'non-list sources' => $nonListSources,
                'empty sources' => $emptySources,
                'duplicate sources' => $duplicateSources,
                'malformed source' => $malformedSource,
                'foreign source' => $foreignSource,
                'non-progress source' => $nonProgressSource,
                'empty source ID' => $emptySourceId,
                'empty source label' => $emptySourceLabel,
            ] as $case => $payload) {
                self::assertNull($this->cardForHealth($project, $payload)['evidence_data_quality_exception'], $case);
            }

            $greenProject = $this->project('CED-EVIDENCE-FRESHNESS-GREEN', 'CED-EVIDENCE-FRESHNESS-GREEN');
            $unknownProject = $this->project('CED-EVIDENCE-FRESHNESS-UNKNOWN', 'CED-EVIDENCE-FRESHNESS-UNKNOWN');
            $unavailableProject = $this->project('CED-EVIDENCE-FRESHNESS-UNAVAILABLE', 'CED-EVIDENCE-FRESHNESS-UNAVAILABLE');
            $nonExceptionMember = User::factory()->create();

            foreach ([$greenProject, $unknownProject, $unavailableProject] as $candidateProject) {
                $candidateProject->members()->attach($nonExceptionMember, ['role' => 'EXECUTIVE']);
            }

            $this->taskWithProgress($greenProject, $nonExceptionMember, '50.00');
            $unknownProject->update(['budget_amount' => '0.00']);
            $this->taskWithProgress($unknownProject, $nonExceptionMember, '50.00');

            $response = $this->actingAs($nonExceptionMember)->get(route('projects.portfolio'))->assertOk();
            $cards = collect($response->viewData('cards'))->keyBy(fn (array $card): string => $card['project']->code);
            self::assertSame('GREEN', $cards['CED-EVIDENCE-FRESHNESS-GREEN']['health']['dimensions']['DATA_FRESHNESS']['status']);
            self::assertNull($cards['CED-EVIDENCE-FRESHNESS-GREEN']['evidence_data_quality_exception']);
            self::assertNull($cards['CED-EVIDENCE-FRESHNESS-UNKNOWN']['evidence_data_quality_exception']);
            self::assertNull($cards['CED-EVIDENCE-FRESHNESS-UNAVAILABLE']['evidence_data_quality_exception']);
            self::assertStringNotContainsString('data-evidence-data-quality-exception="true"', $response->getContent());

            $malformedCard = $this->cardForHealth($project, $reasonMismatch);
            $malformedContent = view('projects.portfolio', ['cards' => [$malformedCard]])->render();
            self::assertStringNotContainsString('data-evidence-data-quality-exception="true"', $malformedContent);
            self::assertStringNotContainsString('MISMATCHED_FRESHNESS_REASON', $malformedContent);

            $method = new \ReflectionMethod(ExecutivePortfolioReadModel::class, 'evidenceDataQualityException');
            $sourceLines = file($method->getFileName());
            self::assertIsArray($sourceLines);
            $methodSource = implode('', array_slice($sourceLines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));
            foreach (['EvidenceDocument', 'evidenceDocuments(', 'ProgressEntry', 'progressEntries(', 'now(', 'diffIn', '72', '168'] as $forbiddenImplementation) {
                self::assertStringNotContainsString($forbiddenImplementation, $methodSource);
            }
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_evidence_data_quality_exception_is_deterministic_and_preserves_prior_card_fields_and_order(): void
    {
        CarbonImmutable::setTestNow('2026-05-06T12:00:00Z');
        $this->withoutVite();

        try {
            $second = $this->project('CED-EVIDENCE-ORDER-002', 'CED-EVIDENCE-ORDER-002');
            $first = $this->project('CED-EVIDENCE-ORDER-001', 'CED-EVIDENCE-ORDER-001');
            $member = User::factory()->create();

            foreach ([$second, $first] as $project) {
                $project->members()->attach($member, ['role' => 'EXECUTIVE']);
                $this->taskWithProgress($project, $member, '50.00');
                ProgressEntry::query()->where('project_id', $project->id)->update(['recorded_at' => '2026-05-02 12:00:00']);
            }

            $firstResponse = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $secondResponse = $this->actingAs($member)->get(route('projects.portfolio'))->assertOk();
            $firstCards = $firstResponse->viewData('cards');
            $secondCards = $secondResponse->viewData('cards');
            $priorFields = [
                'status',
                'health',
                'decision_required',
                'schedule_progress_exception',
                'finance_exception',
                'finance',
                'schedule_source_scope',
                'evidence_ids',
                'audit_ids',
            ];

            self::assertSame(['CED-EVIDENCE-ORDER-001', 'CED-EVIDENCE-ORDER-002'], array_map(fn (array $card): string => $card['project']->code, $firstCards));
            self::assertSame(
                array_map(fn (array $card): string => (string) $card['project']->id, $firstCards),
                array_map(fn (array $card): string => (string) $card['project']->id, $secondCards),
            );
            self::assertSame(array_column($firstCards, 'evidence_data_quality_exception'), array_column($secondCards, 'evidence_data_quality_exception'));
            foreach ($priorFields as $field) {
                self::assertSame(array_column($firstCards, $field), array_column($secondCards, $field), $field);
            }
            self::assertSame([
                'project',
                'status',
                'health',
                'decision_required',
                'schedule_progress_exception',
                'finance_exception',
                'evidence_data_quality_exception',
                'finance',
                'schedule_source_scope',
                'evidence_ids',
                'audit_ids',
            ], array_keys($firstCards[0]));

            foreach ($firstCards as $card) {
                $exception = $card['evidence_data_quality_exception'];
                self::assertNotNull($exception);
                self::assertSame($card['health']['dimensions']['DATA_FRESHNESS']['status'], $exception['status']);
                self::assertSame($card['health']['dimensions']['DATA_FRESHNESS']['value'], $exception['value']);
                self::assertSame($card['health']['reasons']['DATA_FRESHNESS'], $exception['reason']);
                self::assertSame($card['health']['source_scope']['freshness'], $exception['source_scope']);
            }

            self::assertSame(2, substr_count($firstResponse->getContent(), 'data-evidence-data-quality-exception="true"'));
            self::assertLessThan(
                strpos($firstResponse->getContent(), 'data-project-id="'.$second->id.'"'),
                strpos($firstResponse->getContent(), 'data-project-id="'.$first->id.'"'),
            );

            $noProjects = $this->actingAs(User::factory()->create())->get(route('projects.portfolio'))->assertOk();
            self::assertSame([], $noProjects->viewData('cards'));
            $noProjects->assertSee('No projects in permitted scope')
                ->assertDontSee('data-evidence-data-quality-exception="true"', false);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    private function project(string $id, string $code): Project
    {
        return Project::query()->create(['id' => $id, 'code' => $code, 'name' => "Project {$code}", 'baseline_start_date' => '2026-05-01', 'baseline_finish_date' => '2026-05-31', 'budget_amount' => '1000.00', 'status' => 'ACTIVE']);
    }

    private function taskWithProgress(Project $project, User $member, string $progressPercent = '40.00'): WbsTask
    {
        $task = WbsTask::query()->create(['id' => $project->id.'-TASK-001', 'project_id' => $project->id, 'wbs_code' => '1.0', 'name' => 'Foundation', 'baseline_start_date' => '2026-05-01', 'baseline_finish_date' => '2026-05-11', 'weight' => '100.0000', 'status' => 'IN_PROGRESS', 'is_baseline' => true, 'is_leaf' => true]);
        ProgressEntry::query()->create(['id' => $project->id.'-PROGRESS-001', 'project_id' => $project->id, 'task_id' => $task->id, 'progress_date' => '2026-05-06', 'progress_percent' => $progressPercent, 'recorded_by' => $member->id, 'recorded_at' => '2026-05-05 12:00:00', 'note' => 'Measured on site']);

        return $task;
    }

    /** @param array<string, mixed> $health */
    private function cardForHealth(Project $project, array $health): array
    {
        return $this->cardForPayloads($project, $health, app(CedFinanceV1ReadModel::class)->forProject($project));
    }

    /**
     * @param  array<string, mixed>  $health
     * @param  array<string, mixed>  $finance
     */
    private function cardForPayloads(Project $project, array $health, array $finance): array
    {
        $healthBoundary = \Mockery::mock(CedProjectHealthV1ReadModel::class);
        $healthBoundary->shouldReceive('forProject')->once()->withArgs(fn (Project $value): bool => $value->is($project))->andReturn($health);
        $financeBoundary = \Mockery::mock(CedFinanceV1ReadModel::class);
        $financeBoundary->shouldReceive('forProject')->once()->withArgs(fn (Project $value): bool => $value->is($project))->andReturn($finance);

        return (new ExecutivePortfolioReadModel(
            $healthBoundary,
            $financeBoundary,
            app(CedScheduleProgressExceptionV1Projection::class),
            app(CedFinanceExceptionV1Projection::class),
        ))->forProject($project);
    }

    private function postedTransaction(Project $project, User $member, string $id, string $amount = '100.00', string $direction = 'EXPENSE'): FinanceTransaction
    {
        return FinanceTransaction::query()->create(['id' => $id, 'project_id' => $project->id, 'direction' => $direction, 'description' => $direction === 'INCOME' ? 'Posted income' : 'Posted cost', 'amount' => $amount, 'status' => FinanceStatus::Posted, 'created_by' => $member->id, 'approved_by' => User::factory()->create()->id, 'approved_at' => now(), 'posted_at' => now()]);
    }
}
