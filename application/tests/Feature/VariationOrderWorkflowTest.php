<?php

namespace Tests\Feature;

use App\Models\InterimPaymentCertificate;
use App\Models\Project;
use App\Models\User;
use App\Models\VariationOrder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VariationOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    private User $requester;

    private User $site;

    private User $qs;

    private User $director;

    private User $outsider;

    protected function setUp(): void
    {
        parent::setUp();

        $make = fn (string $name, string $email) => User::query()->create([
            'name' => $name, 'email' => $email, 'password' => bcrypt('password123'),
        ]);

        $this->requester = $make('Requester', 'req@vo.example.com');
        $this->site = $make('Site', 'site@vo.example.com');
        $this->qs = $make('QS', 'qs@vo.example.com');
        $this->director = $make('Director', 'dir@vo.example.com');
        $this->outsider = $make('Outsider', 'out@vo.example.com');

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-VO-01',
            'name' => 'VO Test Project',
            'status' => 'ACTIVE',
            'budget_amount' => 1000000.00,
            'baseline_start_date' => CarbonImmutable::parse('2026-01-01')->toDateString(),
            'baseline_finish_date' => CarbonImmutable::parse('2026-12-31')->toDateString(),
        ]);

        $this->project->members()->attach($this->requester, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->site, ['role' => 'SITE_SUPERVISOR']);
        $this->project->members()->attach($this->qs, ['role' => 'PROJECT_MANAGER']);
        $this->project->members()->attach($this->director, ['role' => 'EXECUTIVE']);
    }

    private function submit(User $by, string $type = 'ADDITION', float $cost = 100000, int $days = 10): VariationOrder
    {
        $this->actingAs($by)->post(route('projects.vo.store', $this->project), [
            'title' => 'Extra retaining wall',
            'reason' => 'SITE_CONDITION',
            'type' => $type,
            'cost_amount' => $cost,
            'time_impact_days' => $days,
        ])->assertSessionHasNoErrors();

        return VariationOrder::query()->orderByDesc('vo_number')->firstOrFail();
    }

    private function approveToQs(VariationOrder $vo): void
    {
        $this->actingAs($this->site)->post(route('projects.vo.verify-site', [$this->project, $vo]));
        $this->actingAs($this->qs)->post(route('projects.vo.verify-qs', [$this->project, $vo]));
    }

    public function test_outsider_is_forbidden(): void
    {
        $this->actingAs($this->outsider)->get(route('projects.vo.index', $this->project))->assertStatus(403);
        $this->actingAs($this->outsider)->get(route('projects.vo.create', $this->project))->assertStatus(403);
    }

    public function test_member_can_view_register_and_submit(): void
    {
        $this->actingAs($this->requester)->get(route('projects.vo.index', $this->project))->assertOk()->assertSee('Variation Orders');

        $vo = $this->submit($this->requester);
        $this->assertSame('VO-CED-VO-01-001', $vo->reference_no);
        $this->assertSame('SUBMITTED', $vo->status);
        $this->assertEquals(100000.00, (float) $vo->cost_impact);
        $this->assertSame(10, $vo->time_impact_days);

        $this->actingAs($this->requester)->get(route('projects.vo.show', [$this->project, $vo]))->assertOk()->assertSee('Extra retaining wall');
    }

    public function test_omission_stores_negative_impacts(): void
    {
        $vo = $this->submit($this->requester, 'OMISSION', 25000, 5);
        $this->assertEquals(-25000.00, (float) $vo->cost_impact);
        $this->assertSame(-5, $vo->time_impact_days);
    }

    public function test_full_approval_updates_budget_and_finish_date(): void
    {
        $vo = $this->submit($this->requester);
        $this->approveToQs($vo);

        $this->actingAs($this->director)
            ->post(route('projects.vo.approve-director', [$this->project, $vo]))
            ->assertSessionHasNoErrors();

        $vo->refresh();
        $this->project->refresh();

        $this->assertSame('APPROVED', $vo->status);
        $this->assertEquals(1100000.00, (float) $this->project->budget_amount);
        $this->assertSame('2027-01-10', $this->project->baseline_finish_date->toDateString());
        $this->assertEquals(1000000.00, (float) $vo->previous_budget_amount);
        $this->assertEquals(1100000.00, (float) $vo->new_budget_amount);
        $this->assertSame('2026-12-31', $vo->previous_finish_date->toDateString());
        $this->assertSame('2027-01-10', $vo->new_finish_date->toDateString());
    }

    public function test_omission_reduces_budget(): void
    {
        $vo = $this->submit($this->requester, 'OMISSION', 200000, 0);
        $this->approveToQs($vo);
        $this->actingAs($this->director)->post(route('projects.vo.approve-director', [$this->project, $vo]));

        $this->assertEquals(800000.00, (float) $this->project->refresh()->budget_amount);
    }

    public function test_cannot_skip_levels_or_use_insufficient_role(): void
    {
        $vo = $this->submit($this->requester);

        $this->actingAs($this->director)
            ->post(route('projects.vo.approve-director', [$this->project, $vo]))
            ->assertSessionHasErrors('error');
        $this->assertSame('SUBMITTED', $vo->refresh()->status);

        $this->actingAs($this->site)->post(route('projects.vo.verify-site', [$this->project, $vo]));
        $this->actingAs($this->site)
            ->post(route('projects.vo.verify-qs', [$this->project, $vo]))
            ->assertSessionHasErrors('error');
        $this->assertSame('VERIFIED_SITE', $vo->refresh()->status);
    }

    public function test_maker_checker_blocks_requester_final_approval(): void
    {
        $vo = $this->submit($this->director);
        $this->approveToQs($vo);

        $this->actingAs($this->director)
            ->post(route('projects.vo.approve-director', [$this->project, $vo]))
            ->assertSessionHasErrors('error');

        $this->assertSame('VERIFIED_QS', $vo->refresh()->status);
        $this->assertEquals(1000000.00, (float) $this->project->refresh()->budget_amount);
    }

    public function test_reject_flow_and_no_budget_change(): void
    {
        $vo = $this->submit($this->requester);

        $this->actingAs($this->qs)
            ->post(route('projects.vo.reject', [$this->project, $vo]), ['reason' => 'Not in scope'])
            ->assertSessionHasNoErrors();

        $vo->refresh();
        $this->assertSame('REJECTED', $vo->status);
        $this->assertSame('Not in scope', $vo->rejection_reason);
        $this->assertEquals(1000000.00, (float) $this->project->refresh()->budget_amount);
    }

    public function test_approved_vo_can_be_claimed_in_ipc_but_unapproved_cannot(): void
    {
        $approved = $this->submit($this->requester, 'ADDITION', 100000, 0);
        $this->approveToQs($approved);
        $this->actingAs($this->director)->post(route('projects.vo.approve-director', [$this->project, $approved]));

        $pending = $this->submit($this->requester, 'ADDITION', 50000, 0);

        $this->actingAs($this->requester)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'VO Contractor',
                'period_start' => '2026-11-01',
                'period_end' => '2026-11-30',
                'items' => [['variation_order_id' => $approved->id, 'current_percent' => 50]],
            ])->assertSessionHasNoErrors();

        $ipc = InterimPaymentCertificate::query()->firstOrFail();
        $this->assertEquals(50000.00, (float) $ipc->current_work_amount);
        $this->assertSame($approved->id, $ipc->items()->first()->variation_order_id);

        $this->actingAs($this->requester)
            ->post(route('projects.ipc.store', $this->project), [
                'contractor_name' => 'VO Contractor',
                'period_start' => '2026-12-01',
                'period_end' => '2026-12-31',
                'items' => [['variation_order_id' => $pending->id, 'current_percent' => 50]],
            ])->assertSessionHasErrors('error');

        $this->assertSame(1, InterimPaymentCertificate::query()->count());
    }
}
