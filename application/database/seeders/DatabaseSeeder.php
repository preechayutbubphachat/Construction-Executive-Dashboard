<?php

namespace Database\Seeders;

use App\Domain\Finance\FinanceStatus;
use App\Models\EngineeringRfi;
use App\Models\EngineeringSubmittal;
use App\Models\FinanceTransaction;
use App\Models\HandoverPunchItem;
use App\Models\HseSafetyLog;
use App\Models\InterimPaymentCertificate;
use App\Models\IpcItem;
use App\Models\MaterialRequisition;
use App\Models\NonConformanceReport;
use App\Models\PermitToWork;
use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\ProjectHandoverCertificate;
use App\Models\SiteMachineryLog;
use App\Models\SiteManpowerLog;
use App\Models\User;
use App\Models\VariationOrder;
use App\Models\WbsTask;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core Personas
        $director = User::query()->firstOrCreate(
            ['email' => 'director@ced.local'],
            [
                'name' => 'ศิริวรรณ เทพชัย (Executive Director)',
                'password' => Hash::make('password'),
            ]
        );

        $pm = User::query()->firstOrCreate(
            ['email' => 'pm@ced.local'],
            [
                'name' => 'ณัฐพงษ์ กิตติศักดิ์ (Project Manager)',
                'password' => Hash::make('password'),
            ]
        );

        $siteEngineer = User::query()->firstOrCreate(
            ['email' => 'site@ced.local'],
            [
                'name' => 'สมชาย ประเสริฐ (Site Supervisor / Engineer)',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Project 1: Riverside Skybridge & Grand Terminal
        $prj1 = Project::query()->firstOrCreate(
            ['code' => 'PRJ-SKYBRIDGE-01'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Riverside Skybridge & Grand Terminal',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-12-31',
                'budget_amount' => 45000000.00,
                'status' => 'ACTIVE',
            ]
        );

        $prj1->members()->syncWithoutDetaching([
            $director->id => ['role' => 'EXECUTIVE'],
            $pm->id => ['role' => 'PROJECT_MANAGER'],
            $siteEngineer->id => ['role' => 'SITE_SUPERVISOR'],
        ]);

        // WBS Tasks for Project 1
        $wbsPiling = WbsTask::query()->firstOrCreate(
            ['project_id' => $prj1->id, 'wbs_code' => '1.0'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Substructure & Bored Piling Works',
                'baseline_start_date' => '2026-01-01',
                'baseline_finish_date' => '2026-03-31',
                'weight' => 25.00,
                'is_baseline' => true,
                'is_leaf' => true,
                'status' => 'COMPLETED',
            ]
        );

        $wbsStructure = WbsTask::query()->firstOrCreate(
            ['project_id' => $prj1->id, 'wbs_code' => '2.0'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Steel Cable Stay & Superstructure',
                'baseline_start_date' => '2026-04-01',
                'baseline_finish_date' => '2026-08-31',
                'weight' => 45.00,
                'is_baseline' => true,
                'is_leaf' => true,
                'status' => 'COMPLETED',
            ]
        );

        $wbsArchitectural = WbsTask::query()->firstOrCreate(
            ['project_id' => $prj1->id, 'wbs_code' => '3.0'],
            [
                'id' => (string) Str::ulid(),
                'name' => 'Architectural Cladding & MEP Commissioning',
                'baseline_start_date' => '2026-09-01',
                'baseline_finish_date' => '2026-12-15',
                'weight' => 30.00,
                'is_baseline' => true,
                'is_leaf' => true,
                'status' => 'IN_PROGRESS',
            ]
        );

        // Progress Entries
        ProgressEntry::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'task_id' => $wbsArchitectural->id,
            'progress_date' => '2026-11-01',
            'progress_percent' => 88.50,
            'note' => 'Glazing panel installation 95% complete; MEP testing underway.',
            'recorded_by' => $siteEngineer->id,
            'recorded_at' => now('UTC'),
        ]);

        // Constraints
        ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'title' => 'River Barge Logistics Tide Window',
            'type' => 'SITE_ACCESS',
            'severity' => 'HIGH',
            'description' => 'River barge delivery window tide constraints for steel bridge girders.',
            'status' => 'RESOLVED',
            'created_by' => $pm->id,
            'resolved_at' => now('UTC'),
        ]);

        // Finance Transactions
        FinanceTransaction::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'direction' => 'INCOME',
            'amount' => 15000000.00,
            'description' => 'Client Milestone Advance Payment #1',
            'status' => FinanceStatus::Posted,
            'created_by' => $pm->id,
            'approved_by' => $director->id,
            'submitted_at' => '2026-01-10',
            'approved_at' => '2026-01-12',
            'posted_at' => '2026-01-12',
        ]);

        // 5B: Variation Order
        VariationOrder::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'vo_number' => 1,
            'reference_no' => 'VO-001',
            'title' => 'Curved Low-E Double Laminated Acoustic Glass Upgrade',
            'reason' => 'CLIENT_REQUEST',
            'type' => 'ADDITION',
            'description' => 'Owner requested premium acoustic insulation for terminal concourse.',
            'cost_impact' => 2500000.00,
            'time_impact_days' => 10,
            'status' => 'APPROVED',
            'created_by' => $siteEngineer->id,
            'site_engineer_id' => $siteEngineer->id,
            'site_verified_at' => '2026-05-10',
            'quantity_surveyor_id' => $pm->id,
            'qs_verified_at' => '2026-05-12',
            'project_director_id' => $director->id,
            'director_approved_at' => '2026-05-15',
        ]);

        // 5A: Interim Payment Certificate (IPC #1)
        $ipc1 = InterimPaymentCertificate::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'certificate_number' => 1,
            'reference_no' => 'IPC-001',
            'contractor_name' => 'Siam Infrastructure Engineering Co., Ltd.',
            'period_start' => '2026-01-01',
            'period_end' => '2026-05-31',
            'status' => 'POSTED',
            'gross_work_amount' => 18000000.00,
            'previous_certified_amount' => 0.00,
            'current_work_amount' => 18000000.00,
            'retention_rate_percent' => 5.00,
            'retention_deduction' => 900000.00,
            'advance_recovery_deduction' => 0.00,
            'other_deductions' => 0.00,
            'net_amount_before_vat' => 17100000.00,
            'vat_rate_percent' => 7.00,
            'vat_amount' => 1197000.00,
            'net_payable_amount' => 18297000.00,
            'site_engineer_id' => $siteEngineer->id,
            'site_verified_at' => '2026-06-02',
            'quantity_surveyor_id' => $pm->id,
            'qs_verified_at' => '2026-06-05',
            'project_director_id' => $director->id,
            'director_approved_at' => '2026-06-08',
            'created_by' => $siteEngineer->id,
        ]);

        IpcItem::query()->create([
            'id' => (string) Str::ulid(),
            'ipc_id' => $ipc1->id,
            'wbs_task_id' => $wbsPiling->id,
            'description' => 'Substructure complete piling and footing',
            'contract_amount' => 18000000.00,
            'previous_percent' => 0.00,
            'current_percent' => 100.00,
            'this_period_percent' => 100.00,
            'claimed_amount' => 18000000.00,
            'certified_amount' => 18000000.00,
        ]);

        // 5C: Non-Conformance Report (Closed)
        NonConformanceReport::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'ncr_number' => 1,
            'wbs_task_id' => $wbsStructure->id,
            'reference_no' => 'NCR-001',
            'title' => 'Weld Porosity on Stay Cable Pier Anchor 3',
            'category' => 'WORKMANSHIP',
            'severity' => 'MAJOR',
            'description' => 'Ultrasonic test revealed weld inclusion on anchor flange.',
            'status' => 'CLOSED',
            'hold_payment' => false,
            'location_zone' => 'Main Pylon Pier 3',
            'root_cause' => 'Shielding gas pressure drop during evening shift.',
            'corrective_action' => 'Gouged out weld flaw and re-welded with pre-heating.',
            'preventive_action' => 'Installed digital gas flow regulator with cutoff alarm.',
            'created_by' => $pm->id,
            'rectification_submitted_by' => $siteEngineer->id,
            'rectification_submitted_at' => '2026-07-15',
            'verified_by' => $pm->id,
            'verified_at' => '2026-07-18',
            'verification_notes' => 'NDT re-testing verified 100% sound penetration.',
        ]);

        // 5D: Material Requisition
        MaterialRequisition::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'mr_number' => 1,
            'reference_no' => 'MR-001',
            'material_name' => 'Structural Glazing Silicone & Weatherseal Compound',
            'category' => 'ARCHITECTURAL',
            'unit' => 'SAUSAGES (600ml)',
            'required_qty' => 120.00,
            'delivered_qty' => 120.00,
            'unit_cost' => 3750.00,
            'total_estimated_cost' => 450000.00,
            'supplier_name' => 'Dow Corning Thailand Co., Ltd.',
            'po_number' => 'PO-2026-0881',
            'required_on_site_date' => '2026-10-01',
            'status' => 'DELIVERED',
            'received_at' => '2026-09-28 14:00:00',
            'created_by' => $siteEngineer->id,
        ]);

        // 6A: Site Manpower Logs
        SiteManpowerLog::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'log_date' => '2026-11-06',
            'trade' => 'SUPERVISOR_ENGINEER',
            'contractor_company' => 'Glass Tech Engineering Ltd.',
            'planned_headcount' => 16,
            'actual_headcount' => 18,
            'working_hours' => 8.0,
            'total_man_hours' => 144.0,
            'shift' => 'DAY',
            'notes' => 'Facade glazing installation team.',
            'created_by' => $siteEngineer->id,
        ]);

        // 6B: Heavy Machinery Logs
        SiteMachineryLog::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'log_date' => '2026-11-06',
            'equipment_code' => 'CRANE-TOWER-01',
            'equipment_name' => 'Liebherr 280 EC-H 12 Litronic',
            'category' => 'TOWER_CRANE',
            'status' => 'OPERATIONAL',
            'operating_hours' => 9.5,
            'downtime_hours' => 0.0,
            'operator_name' => 'Somkid Jaidee',
            'notes' => 'Smooth operation all day.',
            'created_by' => $siteEngineer->id,
        ]);

        // 6C: HSE Safety Log & Permit-to-Work
        PermitToWork::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'ptw_number' => 1,
            'reference_no' => 'PTW-001',
            'permit_type' => 'WORKING_AT_HEIGHT',
            'work_title' => 'Suspension Skybridge Cable Stay Anchorage Inspection (Height: 48m)',
            'location_zone' => 'Pier 2 Tower Gantry',
            'valid_from' => '2026-11-06',
            'valid_until' => '2026-11-07',
            'precautions_checklist' => json_encode(['HARNESS_INSPECTED' => true, 'LIFELINE_ANCHORED' => true]),
            'status' => 'APPROVED',
            'approved_by' => $pm->id,
            'approved_at' => '2026-11-06 07:30:00',
            'safety_notes' => 'Strict 100% tie-off mandatory.',
            'created_by' => $siteEngineer->id,
        ]);

        HseSafetyLog::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'log_date' => '2026-11-06',
            'record_type' => 'TOOLBOX_TALK',
            'title' => '100% Tie-Off Double Lanyard Inspection on Cable Gantry',
            'description' => 'Daily safety briefing conducted before starting high-elevation facade work.',
            'attendees_count' => 48,
            'lost_time_hours' => 0.0,
            'created_by' => $siteEngineer->id,
        ]);

        // 7A: Engineering RFI
        EngineeringRfi::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'rfi_number' => 1,
            'reference_no' => 'RFI-001',
            'discipline' => 'ARCHITECTURAL',
            'subject' => 'Skybridge Expansion Joint Cover Flashing Tolerance',
            'drawing_ref' => 'DWG-ME-502',
            'question' => 'Clarify thermal expansion gap between pier 2 and terminal deck for seismic movement joint.',
            'proposed_solution' => 'Use articulated elastomeric finger joint with stainless steel cover plate.',
            'official_response' => 'Approved: Adopt elastomeric expansion joint per Detail DWG-ME-502 Rev.B.',
            'status' => 'CLOSED',
            'due_date' => '2026-10-15',
            'answered_by' => $pm->id,
            'answered_at' => '2026-10-12',
            'created_by' => $siteEngineer->id,
        ]);

        // 7B: Submittal
        EngineeringSubmittal::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'submittal_number' => 1,
            'reference_no' => 'SUB-001',
            'submittal_type' => 'SHOP_DRAWING',
            'discipline' => 'ARCHITECTURAL',
            'title' => 'Concourse Aerofoil Sunshade Louvre Layout Details',
            'revision' => 'Rev.1',
            'status' => 'APPROVED_AS_NOTED',
            'review_comments' => 'Proceed with fabrication. Fastener torque specs must conform to SS316 table.',
            'reviewed_by' => $pm->id,
            'reviewed_at' => '2026-10-25',
            'created_by' => $siteEngineer->id,
        ]);

        // 8A: Handover Punch List
        HandoverPunchItem::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'wbs_task_id' => $wbsArchitectural->id,
            'punch_code' => 'PL-001',
            'location_zone' => 'Terminal Concourse Gate 4 North Balcony',
            'discipline' => 'ARCHITECTURAL',
            'severity' => 'MINOR',
            'description' => 'Minor paint scuff on aluminum composite panel at column C4.',
            'rectification_notes' => 'Cleaned and recoated with factory PVDF touch-up paint.',
            'status' => 'VERIFIED_CLOSED',
            'reported_by' => $pm->id,
            'rectified_by' => $siteEngineer->id,
            'verified_by' => $director->id,
            'rectified_at' => '2026-11-02',
            'verified_at' => '2026-11-04',
        ]);

        // 8B: Taking-Over Certificate (TOC-001)
        ProjectHandoverCertificate::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $prj1->id,
            'certificate_number' => 'TOC-001',
            'certificate_type' => 'TAKING_OVER_CERTIFICATE',
            'handover_date' => '2026-11-05',
            'dlp_months' => 12,
            'dlp_expiry_date' => '2027-11-05',
            'final_contract_sum' => 47500000.00,
            'total_approved_vo_amount' => 2500000.00,
            'total_certified_ipc_amount' => 18000000.00,
            'retention_held_total' => 900000.00,
            'retention_release_amount' => 450000.00,
            'status' => 'ISSUED',
            'scope_remarks' => 'Substructure & Bridge Deck practical completion achieved. 50% retention released for TOC.',
            'prepared_by' => $pm->id,
            'certified_by' => $pm->id,
            'certified_at' => '2026-11-05',
        ]);
    }
}
