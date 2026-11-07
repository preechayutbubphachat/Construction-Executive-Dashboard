<?php

namespace Tests\Unit;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\EarnedValueManagementService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EarnedValueManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private EarnedValueManagementService $service;

    private Project $project;

    private User $maker;

    private User $approver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EarnedValueManagementService;

        $this->maker = User::factory()->create();
        $this->approver = User::factory()->create();

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'CED-EVM-01',
            'name' => 'Metro Expressway EVM Test',
            'baseline_start_date' => CarbonImmutable::now()->subWeeks(4)->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addWeeks(4)->toDateString(),
            'budget_amount' => '1000000.00',
            'status' => 'ACTIVE',
        ]);

        $this->project->members()->attach($this->maker, ['role' => 'PROJECT_MANAGER']);
        $this->project->members()->attach($this->approver, ['role' => 'EXECUTIVE']);
    }

    public function test_calculates_evm_metrics_variances_and_indices(): void
    {
        // 1. Create a leaf WBS task with 100% weight spanning 8 weeks
        $task = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.1',
            'name' => 'Superstructure Civil Works',
            'baseline_start_date' => $this->project->baseline_start_date->toDateString(),
            'baseline_finish_date' => $this->project->baseline_finish_date->toDateString(),
            'weight' => 100.0,
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);

        // 2. Record 40% actual progress today
        ProgressEntry::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'task_id' => $task->id,
            'progress_date' => CarbonImmutable::now()->toDateString(),
            'progress_percent' => '40.00',
            'recorded_by' => $this->maker->id,
            'recorded_at' => CarbonImmutable::now(),
        ]);

        // 3. Post actual cost transactions totaling 450,000.00
        FinanceTransaction::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'direction' => 'EXPENSE',
            'description' => 'Concrete and steel reinforcement procurement',
            'amount' => '450000.00',
            'status' => FinanceStatus::Posted,
            'created_by' => $this->maker->id,
            'approved_by' => $this->approver->id,
            'submitted_at' => CarbonImmutable::now()->subDays(5),
            'approved_at' => CarbonImmutable::now()->subDays(4),
            'posted_at' => CarbonImmutable::now()->subDays(3),
        ]);

        $result = $this->service->calculate($this->project);

        $this->assertEquals('CED_EVM_SCURVE_V1', $result['version']);
        $this->assertEquals(1000000.00, $result['metrics']['bac']);
        $this->assertEquals(400000.00, $result['metrics']['ev']); // 40% of 1M
        $this->assertEquals(450000.00, $result['metrics']['ac']);

        // Check Variances
        $this->assertEquals(-50000.00, $result['variances']['cv']); // EV - AC = 400k - 450k
        $this->assertLessThan(0, $result['variances']['sv']); // Behind schedule

        // Check Indices
        $this->assertLessThan(1.0, $result['indices']['cpi']); // 400k / 450k = 0.89
        $this->assertLessThan(1.0, $result['indices']['spi']);

        // Check Forecasts
        $this->assertGreaterThan(1000000.00, $result['forecasts']['eac']); // Projected cost overrun
        $this->assertLessThan(0.0, $result['forecasts']['vac']);

        // Check S-Curve time series
        $this->assertNotEmpty($result['s_curve']['series']);
        $firstPoint = $result['s_curve']['series'][0];
        $this->assertArrayHasKey('date', $firstPoint);
        $this->assertArrayHasKey('pv', $firstPoint);
        $this->assertArrayHasKey('ev', $firstPoint);
        $this->assertArrayHasKey('ac', $firstPoint);
    }
}
