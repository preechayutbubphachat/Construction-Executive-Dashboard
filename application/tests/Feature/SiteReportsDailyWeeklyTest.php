<?php

namespace Tests\Feature;

use App\Models\EvidenceDocument;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteReportsDailyWeeklyTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private User $outsider;

    private Project $project;

    private WbsTask $leafTask;

    private ProjectConstraint $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = User::query()->create([
            'name' => 'Somchai Engineer',
            'email' => 'somchai.reports@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->outsider = User::query()->create([
            'name' => 'Stranger',
            'email' => 'stranger.reports@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-REP-01',
            'name' => 'Metro Line Blue Line Viaduct',
            'status' => 'ACTIVE',
            'budget_amount' => 12000000.00,
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->subWeeks(2)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(4)->toDateString(),
        ]);

        $this->project->members()->attach($this->member, ['role' => 'EXECUTIVE']);

        $this->leafTask = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.2.3',
            'name' => 'Segmental Box Girder Erection',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addWeeks(2)->toDateString(),
            'weight' => 20.0,
            'is_leaf' => true,
        ]);

        $this->constraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'type' => 'WEATHER',
            'title' => 'High Wind Speed Exceeding 12 m/s',
            'description' => 'Launching gantry crane operation temporarily suspended.',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'created_by' => $this->member->id,
        ]);
    }

    public function test_unauthenticated_user_is_denied(): void
    {
        $this->get(route('projects.reports.daily', $this->project))
            ->assertStatus(403);

        $this->get(route('projects.reports.weekly', $this->project))
            ->assertStatus(403);
    }

    public function test_non_member_cannot_access_reports(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('projects.reports.daily', $this->project))
            ->assertForbidden();

        $this->actingAs($this->outsider)
            ->get(route('projects.reports.weekly', $this->project))
            ->assertForbidden();
    }

    public function test_authorized_user_can_view_daily_site_report(): void
    {
        $this->actingAs($this->member);

        $response = $this->get(route('projects.reports.daily', $this->project));

        $response->assertOk()
            ->assertSee('Daily Site Construction Log Report')
            ->assertSee('รายงานประจำวันและบันทึกความคืบหน้าหน้างาน')
            ->assertSee($this->project->code)
            ->assertSee('Segmental Box Girder Erection')
            ->assertSee('1.2.3')
            ->assertSee('Weather')
            ->assertSee('Site Manpower')
            ->assertSee('Official Sign-off');
    }

    public function test_daily_report_displays_field_photo_evidence(): void
    {
        $this->actingAs($this->member);

        EvidenceDocument::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_task_id' => $this->leafTask->id,
            'storage_disk' => 'evidence',
            'storage_path' => $this->project->id.'/test.jpg',
            'original_name' => 'girder_alignment.jpg',
            'caption' => 'Pier 12 Girder Alignment Check',
            'gps_latitude' => 13.756331,
            'gps_longitude' => 100.501765,
            'captured_at' => Carbon::now(),
            'mime_type' => 'image/jpeg',
            'byte_size' => 1024,
            'sha256' => hash('sha256', 'sample'),
            'access_class' => 'PRIVATE',
            'review_state' => 'UPLOADED',
            'metadata' => ['category' => 'QUALITY_CHECK'],
            'uploaded_by' => $this->member->id,
        ]);

        $response = $this->get(route('projects.reports.daily', $this->project));

        $response->assertOk()
            ->assertSee('Pier 12 Girder Alignment Check')
            ->assertSee('13.756')
            ->assertSee('QUALITY_CHECK');
    }

    public function test_daily_report_date_navigation(): void
    {
        $this->actingAs($this->member);

        $specificDate = '2026-06-15';
        $response = $this->get(route('projects.reports.daily', [$this->project, 'date' => $specificDate]));

        $response->assertOk()
            ->assertSee('15 June 2026')
            ->assertSee('DSR-CED-REP-01-20260615');
    }

    public function test_authorized_user_can_view_weekly_executive_report_with_evm_and_lookahead(): void
    {
        $this->actingAs($this->member);

        $response = $this->get(route('projects.reports.weekly', $this->project));

        $response->assertOk()
            ->assertSee('Weekly Executive Status Report')
            ->assertSee('รายงานสรุปผลการดำเนินงานและสถานะโครงการประจำสัปดาห์')
            ->assertSee('Earned Value Management (EVM) Performance')
            ->assertSee('Cost Index (CPI)')
            ->assertSee('Schedule Index (SPI)')
            ->assertSee('Lookahead Execution')
            ->assertSee('Weekly Financial Activity')
            ->assertSee('Executive Endorsement');
    }

    public function test_weekly_report_shows_financial_transactions_of_the_week(): void
    {
        $this->actingAs($this->member);

        FinanceTransaction::query()->create([
            'id' => (string) Str::uuid(),
            'project_id' => $this->project->id,
            'direction' => 'OUTFLOW',
            'description' => 'Ready-Mix Concrete Batch Batch-09 payment',
            'amount' => 450000.00,
            'status' => 'POSTED',
            'created_by' => $this->member->id,
            'posted_at' => Carbon::now(),
        ]);

        $response = $this->get(route('projects.reports.weekly', $this->project));

        $response->assertOk()
            ->assertSee('Ready-Mix Concrete Batch Batch-09 payment')
            ->assertSee('450,000.00');
    }

    public function test_weekly_report_week_navigation(): void
    {
        $this->actingAs($this->member);

        $specificWeek = '2026-05-18';
        $response = $this->get(route('projects.reports.weekly', [$this->project, 'week' => $specificWeek]));

        $response->assertOk()
            ->assertSee('WSR-CED-REP-01-2026-W21')
            ->assertSee('2026-05-18');
    }
}
