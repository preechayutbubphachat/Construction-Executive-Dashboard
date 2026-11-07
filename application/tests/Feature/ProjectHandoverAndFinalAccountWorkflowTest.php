<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Models\FinanceTransaction;
use App\Models\HandoverPunchItem;
use App\Models\InterimPaymentCertificate;
use App\Models\NonConformanceReport;
use App\Models\Project;
use App\Models\ProjectHandoverCertificate;
use App\Models\User;
use App\Models\VariationOrder;
use App\Models\WbsTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectHandoverAndFinalAccountWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectTeam(): array
    {
        $siteEngineer = User::factory()->create(['name' => 'Site Engineer Somchai']);
        $projectManager = User::factory()->create(['name' => 'PM Nattapong']);
        $executive = User::factory()->create(['name' => 'Executive Director Siriwan']);

        $project = Project::query()->create([
            'id' => (string) Str::ulid(),
            'code' => 'PRJ-HANDOVER-08',
            'name' => 'Riverside Skybridge & Terminal',
            'baseline_start_date' => '2026-01-01',
            'baseline_finish_date' => '2026-12-31',
            'budget_amount' => 11000000.00, // Includes 1,000,000 approved VO
            'status' => 'ACTIVE',
        ]);

        $project->members()->attach($siteEngineer->id, ['role' => 'SITE_SUPERVISOR']);
        $project->members()->attach($projectManager->id, ['role' => 'PROJECT_MANAGER']);
        $project->members()->attach($executive->id, ['role' => 'EXECUTIVE']);

        $task = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $project->id,
            'wbs_code' => '8.1',
            'name' => 'Architectural Facade & Final MEP Commissioning',
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-11-30',
            'planned_weight_percent' => 100.00,
            'is_leaf' => true,
            'status' => 'IN_PROGRESS',
        ]);

        // Seed 1 Approved VO of 1,000,000 THB
        VariationOrder::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $project->id,
            'vo_number' => 1,
            'reference_no' => 'VO-001',
            'title' => 'Additional Acoustic Glazing Upgrade',
            'reason' => 'CLIENT_REQUEST',
            'type' => 'ADDITION',
            'description' => 'Upgraded double laminated acoustic glass.',
            'cost_impact' => 1000000.00,
            'time_impact_days' => 0,
            'status' => 'APPROVED',
            'created_by' => $siteEngineer->id,
            'project_director_id' => $executive->id,
            'director_approved_at' => now('UTC'),
        ]);

        // Seed 1 Posted IPC with 500,000 THB Retention Held
        InterimPaymentCertificate::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $project->id,
            'certificate_number' => 1,
            'reference_no' => 'IPC-001',
            'contractor_name' => 'Siam Engineering Builders Co., Ltd.',
            'period_start' => '2026-01-01',
            'period_end' => '2026-10-31',
            'status' => 'POSTED',
            'gross_work_amount' => 10000000.00,
            'previous_certified_amount' => 0.00,
            'current_work_amount' => 10000000.00,
            'retention_rate_percent' => 5.00,
            'retention_deduction' => 500000.00,
            'advance_recovery_deduction' => 0.00,
            'other_deductions' => 0.00,
            'net_amount_before_vat' => 9500000.00,
            'vat_rate_percent' => 7.00,
            'vat_amount' => 665000.00,
            'net_payable_amount' => 10165000.00,
            'created_by' => $siteEngineer->id,
            'project_director_id' => $executive->id,
            'director_approved_at' => now('UTC'),
        ]);

        return [$project, $task, $siteEngineer, $projectManager, $executive];
    }

    public function test_punch_list_and_open_ncr_gate_blocks_handover_until_verified_closed(): void
    {
        [$project, $task, $siteEngineer, $projectManager] = $this->createProjectTeam();

        // Create an open NCR
        $ncr = NonConformanceReport::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $project->id,
            'wbs_task_id' => $task->id,
            'ncr_number' => 1,
            'reference_no' => 'NCR-001',
            'title' => 'Fire Damper Sealant Incomplete',
            'category' => 'WORKMANSHIP',
            'severity' => 'MAJOR',
            'description' => 'Fire stopping sealant missing around duct penetration.',
            'status' => 'OPEN',
            'hold_payment' => true,
            'created_by' => $projectManager->id,
        ]);

        // Attempt to issue TOC while NCR is open -> blocked
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.issue', $project), [
                'certificate_type' => 'TAKING_OVER_CERTIFICATE',
                'handover_date' => '2026-11-15',
                'dlp_months' => 12,
                'retention_release_amount' => 250000.00,
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasErrors(['handover']);

        $this->assertDatabaseCount('project_handover_certificates', 0);

        // Close the NCR, then log a Punch List Item (PL-001)
        $ncr->forceFill(['status' => 'CLOSED'])->save();

        $this->actingAs($projectManager)
            ->post(route('projects.handover.punch.store', $project), [
                'wbs_task_id' => $task->id,
                'location_zone' => 'Main Terminal Skylight Zone B',
                'discipline' => 'ARCHITECTURAL',
                'severity' => 'CRITICAL_HANDOVER_BLOCKER',
                'description' => 'Silicone weatherproofing joint gap at mullion B-14.',
            ])
            ->assertRedirect(route('projects.handover.index', $project));

        $punchItem = HandoverPunchItem::query()->where('project_id', $project->id)->firstOrFail();
        $this->assertSame('PL-001', $punchItem->punch_code);
        $this->assertSame('OPEN', $punchItem->status);

        // Attempt to issue TOC while Punch Item is OPEN -> blocked
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.issue', $project), [
                'certificate_type' => 'TAKING_OVER_CERTIFICATE',
                'handover_date' => '2026-11-15',
                'dlp_months' => 12,
                'retention_release_amount' => 250000.00,
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasErrors(['handover']);

        // Site Engineer rectifies the punch item
        $this->actingAs($siteEngineer)
            ->post(route('projects.handover.punch.rectify', [$project, $punchItem]), [
                'rectification_notes' => 'Resealed mullion B-14 with structural weatherseal silicone and water-tested.',
            ])
            ->assertRedirect(route('projects.handover.index', $project));

        $punchItem->refresh();
        $this->assertSame('RECTIFIED_PENDING_INSPECTION', $punchItem->status);

        // Maker-Checker test: if PM rectifies an item, PM cannot verify their own rectification
        $this->actingAs($projectManager)
            ->post(route('projects.handover.punch.rectify', [$project, $punchItem]), [
                'rectification_notes' => 'PM updated rectification notes directly.',
            ]);

        $this->actingAs($projectManager)
            ->post(route('projects.handover.punch.verify', [$project, $punchItem]), [
                'verification_notes' => 'Self-verifying should fail.',
            ])
            ->assertSessionHasErrors(['handover']);

        // Site Engineer submits final rectification, then PM verifies & closes it
        $this->actingAs($siteEngineer)
            ->post(route('projects.handover.punch.rectify', [$project, $punchItem]), [
                'rectification_notes' => 'Final contractor rectification completed by site engineer.',
            ]);

        $this->actingAs($projectManager)
            ->post(route('projects.handover.punch.verify', [$project, $punchItem]), [
                'verification_notes' => 'Joint water-tightness verified on site.',
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasNoErrors();

        $punchItem->refresh();
        $this->assertSame('VERIFIED_CLOSED', $punchItem->status);
    }

    public function test_toc_and_dlc_retention_release_posts_finance_transactions_and_completes_project(): void
    {
        [$project, $task, , $projectManager, $executive] = $this->createProjectTeam();

        // Check Final Account Statement on Handover Dashboard
        $this->actingAs($projectManager)
            ->get(route('projects.handover.index', $project))
            ->assertOk()
            ->assertSee('10,000,000.00') // Original Contract Sum
            ->assertSee('11,000,000.00') // Final Contract Sum
            ->assertSee('500,000.00'); // Retention Held Total

        // Cannot request more retention release than 500,000 THB held
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.issue', $project), [
                'certificate_type' => 'TAKING_OVER_CERTIFICATE',
                'handover_date' => '2026-11-20',
                'dlp_months' => 12,
                'retention_release_amount' => 600000.00,
            ])
            ->assertSessionHasErrors(['handover']);

        // Step 1: PM issues Taking-Over Certificate (TOC-001) releasing first 50% retention (250,000 THB)
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.issue', $project), [
                'certificate_type' => 'TAKING_OVER_CERTIFICATE',
                'handover_date' => '2026-11-20',
                'dlp_months' => 12,
                'retention_release_amount' => 250000.00,
                'scope_remarks' => 'Practical completion achieved; releasing first 50% retention moiety.',
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasNoErrors();

        $toc = ProjectHandoverCertificate::query()->where('certificate_number', 'TOC-001')->firstOrFail();
        $this->assertSame('ISSUED', $toc->status);
        $this->assertSame('2027-11-20', $toc->dlp_expiry_date->toDateString());

        // Maker-Checker: PM who prepared TOC-001 cannot release the retention money themselves
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.release-retention', [$project, $toc]))
            ->assertSessionHasErrors(['handover']);

        // Executive approves & releases first 250,000 THB retention
        $this->actingAs($executive)
            ->post(route('projects.handover.certificates.release-retention', [$project, $toc]), [
                'mark_project_completed' => '0',
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasNoErrors();

        $toc->refresh();
        $this->assertSame('RETENTION_RELEASED', $toc->status);
        $this->assertNotNull($toc->finance_transaction_id);

        $tx1 = FinanceTransaction::query()->findOrFail($toc->finance_transaction_id);
        $this->assertSame('EXPENSE', $tx1->direction);
        $this->assertSame(FinanceStatus::Posted, $tx1->status);
        $this->assertEquals(250000.00, (float) $tx1->amount);

        // Step 2: Attempt to issue DLC while WBS task is still IN_PROGRESS -> blocked
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.issue', $project), [
                'certificate_type' => 'DEFECTS_LIABILITY_CERTIFICATE',
                'handover_date' => '2026-12-15',
                'dlp_months' => 12,
                'retention_release_amount' => 250000.00,
            ])
            ->assertSessionHasErrors(['handover']);

        // Complete all WBS tasks
        $task->forceFill(['status' => 'COMPLETED'])->save();

        // Issue DLC-001 for remaining 250,000 THB retention balance
        $this->actingAs($projectManager)
            ->post(route('projects.handover.certificates.issue', $project), [
                'certificate_type' => 'DEFECTS_LIABILITY_CERTIFICATE',
                'handover_date' => '2026-12-15',
                'dlp_months' => 12,
                'retention_release_amount' => 250000.00,
                'scope_remarks' => 'End of DLP & Final Account settlement.',
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasNoErrors();

        $dlc = ProjectHandoverCertificate::query()->where('certificate_number', 'DLC-002')->firstOrFail();

        // Executive releases final retention and closes out project to COMPLETED
        $this->actingAs($executive)
            ->post(route('projects.handover.certificates.release-retention', [$project, $dlc]), [
                'mark_project_completed' => '1',
            ])
            ->assertRedirect(route('projects.handover.index', $project))
            ->assertSessionHasNoErrors();

        $dlc->refresh();
        $project->refresh();
        $this->assertSame('RETENTION_RELEASED', $dlc->status);
        $this->assertSame('COMPLETED', $project->status);
    }

    public function test_unauthenticated_and_non_member_users_are_denied_handover_access(): void
    {
        [$project] = $this->createProjectTeam();
        $outsider = User::factory()->create();

        $this->get(route('projects.handover.index', $project))->assertForbidden();
        $this->actingAs($outsider)->get(route('projects.handover.index', $project))->assertForbidden();
    }
}
