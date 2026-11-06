<?php

namespace Tests\Feature;

use App\Domain\Finance\FinanceStatus;
use App\Http\Controllers\ExecutivePortfolioController;
use App\Models\EvidenceDocument;
use App\Models\FinanceTransaction;
use App\Models\ProgressEntry;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExecutiveRootIntegrationRm13Test extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_member_enters_existing_portfolio_boundary_from_root_with_membership_isolation_and_drill_down(): void
    {
        $this->withoutVite();
        $permitted = $this->project('CED-RM13-PROJECT-001', 'RM13-001');
        $foreign = $this->project('CED-RM13-PROJECT-002', 'RM13-002');
        $member = User::factory()->create();
        $permitted->members()->attach($member, ['role' => 'EXECUTIVE']);
        $foreign->members()->attach(User::factory()->create(), ['role' => 'EXECUTIVE']);

        $rootRoute = Route::getRoutes()->match(Request::create('/', 'GET'));
        self::assertSame(ExecutivePortfolioController::class, $rootRoute->getControllerClass());

        $response = $this->actingAs($member)->get('/');

        $response->assertOk()
            ->assertViewIs('projects.portfolio')
            ->assertSee('Executive Portfolio')
            ->assertSee($permitted->code)
            ->assertSee(route('projects.executive', $permitted), false)
            ->assertDontSee($foreign->code)
            ->assertDontSee($foreign->id);
        self::assertCount(1, $response->viewData('cards'));
        self::assertSame($permitted->id, $response->viewData('cards')[0]['project']->id);
    }

    public function test_guest_root_request_fails_closed_without_sensitive_fixture_leakage(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-RM13-PRIVATE-PROJECT', 'RM13-PRIVATE');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $task = WbsTask::query()->create([
            'id' => 'CED-RM13-PRIVATE-TASK',
            'project_id' => $project->id,
            'wbs_code' => '1.0',
            'name' => 'Private foundation work',
            'baseline_start_date' => '2026-09-01',
            'baseline_finish_date' => '2026-09-10',
            'weight' => '100.0000',
            'status' => 'IN_PROGRESS',
            'is_baseline' => true,
            'is_leaf' => true,
        ]);
        $progress = ProgressEntry::query()->create([
            'id' => 'CED-RM13-PRIVATE-PROGRESS',
            'project_id' => $project->id,
            'task_id' => $task->id,
            'progress_date' => '2026-09-05',
            'progress_percent' => '25.00',
            'recorded_by' => $member->id,
            'recorded_at' => '2026-09-05 12:00:00',
            'note' => 'Private progress provenance',
        ]);
        $transaction = FinanceTransaction::query()->create([
            'id' => 'CED-RM13-PRIVATE-FINANCE',
            'project_id' => $project->id,
            'direction' => 'EXPENSE',
            'description' => 'Private finance description',
            'amount' => '250.00',
            'status' => FinanceStatus::Posted,
            'created_by' => $member->id,
            'approved_by' => User::factory()->create()->id,
            'approved_at' => now(),
            'posted_at' => now(),
        ]);
        $risk = ProjectRisk::query()->create([
            'id' => 'CED-RM13-PRIVATE-RISK',
            'project_id' => $project->id,
            'title' => 'Private risk title',
            'severity' => 'HIGH',
            'status' => 'OPEN',
        ]);
        $evidence = EvidenceDocument::query()->create([
            'id' => 'CED-RM13-PRIVATE-EVIDENCE',
            'project_id' => $project->id,
            'storage_disk' => 'evidence',
            'storage_path' => 'CED-RM13-PRIVATE-PROJECT/private-evidence.pdf',
            'original_name' => 'private-evidence.pdf',
            'mime_type' => 'application/pdf',
            'byte_size' => 24,
            'sha256' => hash('sha256', 'rm13-private-evidence'),
            'access_class' => 'PRIVATE',
            'review_state' => 'ACCEPTED',
            'uploaded_by' => $member->id,
        ]);
        $audit = app(AuditRecorder::class)->record($project->id, $member, 'rm13.private.audit', $transaction);

        $response = $this->get('/')->assertForbidden();

        $sensitiveValues = [
            $project->id,
            $project->code,
            $task->id,
            $task->name,
            $progress->id,
            $progress->note,
            $transaction->id,
            $transaction->description,
            $risk->id,
            $risk->title,
            $evidence->id,
            $evidence->original_name,
            $evidence->storage_path,
            $evidence->sha256,
            $audit->id,
            $audit->action,
        ];

        foreach ($sensitiveValues as $sensitiveValue) {
            $response->assertDontSee($sensitiveValue);
        }
    }

    public function test_authenticated_user_without_memberships_sees_empty_permitted_scope(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-RM13-UNRELATED-PROJECT', 'RM13-UNRELATED');
        $project->members()->attach(User::factory()->create(), ['role' => 'EXECUTIVE']);
        $unaffiliated = User::factory()->create();

        $response = $this->actingAs($unaffiliated)->get('/');

        $response->assertOk()
            ->assertViewIs('projects.portfolio')
            ->assertSee('No projects in permitted scope')
            ->assertDontSee($project->id)
            ->assertDontSee($project->code);
        self::assertCount(0, $response->viewData('cards'));
    }

    public function test_root_preserves_unavailable_health_semantics_when_accepted_inputs_are_absent(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-RM13-NO-HEALTH-INPUTS', 'RM13-NO-INPUTS');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);

        $response = $this->actingAs($member)->get('/');

        $response->assertOk()
            ->assertViewIs('projects.portfolio')
            ->assertSee('Health unavailable')
            ->assertSee('Not available from accepted inputs.')
            ->assertDontSee('GREEN')
            ->assertDontSee('data-health-status=', false);
        self::assertNull($response->viewData('cards')[0]['health']);
    }

    public function test_compatibility_portfolio_route_and_name_retain_existing_boundary(): void
    {
        $this->withoutVite();
        $project = $this->project('CED-RM13-COMPATIBILITY', 'RM13-COMPAT');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);
        $compatibilityRoute = Route::getRoutes()->getByName('projects.portfolio');

        self::assertNotNull($compatibilityRoute);
        self::assertContains('GET', $compatibilityRoute->methods());
        self::assertSame('projects/portfolio', $compatibilityRoute->uri());
        self::assertSame(ExecutivePortfolioController::class, $compatibilityRoute->getControllerClass());

        $this->actingAs($member)
            ->get(route('projects.portfolio'))
            ->assertOk()
            ->assertViewIs('projects.portfolio')
            ->assertSee($project->code);
    }

    private function project(string $id, string $code): Project
    {
        return Project::query()->create([
            'id' => $id,
            'code' => $code,
            'name' => "Project {$code}",
            'baseline_start_date' => '2026-09-01',
            'baseline_finish_date' => '2026-09-30',
            'budget_amount' => '1000.00',
            'status' => 'ACTIVE',
        ]);
    }
}
