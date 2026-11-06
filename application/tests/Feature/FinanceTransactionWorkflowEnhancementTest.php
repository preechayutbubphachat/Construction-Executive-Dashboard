<?php

namespace Tests\Feature;

use App\Livewire\FinanceTransactionWorkflow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceTransactionWorkflowEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_workflow_renders_budget_utilization_and_report_link(): void
    {
        $project = Project::query()->create([
            'id' => 'PRJ-FIN-TEST-001',
            'code' => 'FIN-001',
            'name' => 'Finance Test Project',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'budget_amount' => '100000.00',
            'status' => 'ACTIVE',
        ]);
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'FINANCE_APPROVER']);

        $this->actingAs($member);

        Livewire::test(FinanceTransactionWorkflow::class, ['project' => $project])
            ->assertOk()
            ->assertSee('Budget Utilization')
            ->assertSee(route('projects.report', $project));
    }
}
