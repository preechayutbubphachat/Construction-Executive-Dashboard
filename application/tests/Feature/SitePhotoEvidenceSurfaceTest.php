<?php

namespace Tests\Feature;

use App\Livewire\SiteSupervisorQuickCaptureView;
use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\User;
use App\Models\WbsTask;
use App\Services\SitePhotoWatermarkService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SitePhotoEvidenceSurfaceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    private User $outsider;

    private Project $project;

    private WbsTask $leafTask;

    private ProjectConstraint $constraint;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('evidence');

        $this->admin = User::query()->create([
            'name' => 'Site Director',
            'email' => 'director@ced-test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->member = User::query()->create([
            'name' => 'Field Supervisor',
            'email' => 'supervisor@ced-test.local',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->outsider = User::query()->create([
            'name' => 'External Visitor',
            'email' => 'outsider@ced-test.local',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->project = Project::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'SITE-01',
            'name' => 'Bangkok Transit Rail Extension',
            'status' => 'ACTIVE',
            'budget_amount' => 50000000,
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addMonths(6)->toDateString(),
        ]);

        $this->project->members()->attach($this->member, ['role' => 'EXECUTIVE']);

        $this->leafTask = WbsTask::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'wbs_code' => '1.1.1',
            'name' => 'Pier 14 Reinforcement Pouring',
            'status' => 'IN_PROGRESS',
            'baseline_start_date' => CarbonImmutable::now()->startOfWeek()->toDateString(),
            'baseline_finish_date' => CarbonImmutable::now()->addWeeks(2)->toDateString(),
            'weight' => 15.0,
            'is_leaf' => true,
        ]);

        $this->constraint = ProjectConstraint::query()->create([
            'id' => (string) Str::ulid(),
            'project_id' => $this->project->id,
            'type' => 'EQUIPMENT',
            'title' => 'Mobile Crane 50T Hydraulic Leak',
            'description' => 'Boom hoist cylinder needs gasket replacement.',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'created_by' => $this->member->id,
        ]);
    }

    public function test_unauthorized_user_cannot_view_or_upload_site_photos(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('projects.site', $this->project))
            ->assertForbidden();
    }

    public function test_authorized_user_can_access_site_supervisor_and_switch_to_photos_tab(): void
    {
        $this->actingAs($this->member);

        Livewire::test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->assertSee('Site Quick-Capture')
            ->call('switchTab', 'PHOTOS')
            ->assertSet('activeTab', 'PHOTOS')
            ->assertSee('Take Field Photo')
            ->assertSee('Field Inspection Gallery');
    }

    public function test_supervisor_can_upload_field_photo_with_gps_telemetry_attached_to_task(): void
    {
        $this->actingAs($this->member);

        $fakePhoto = UploadedFile::fake()->image('pier_inspection.jpg', 640, 480);

        Livewire::test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->call('switchTab', 'PHOTOS')
            ->set('photoFile', $fakePhoto)
            ->set('photoTaskId', $this->leafTask->id)
            ->set('photoCaption', 'Rebar spacing inspected and verified conforming to spec.')
            ->set('photoCategory', 'PROGRESS')
            ->set('photoLat', 13.756331)
            ->set('photoLng', 100.501765)
            ->call('uploadSitePhoto')
            ->assertHasNoErrors()
            ->assertSet('photoCaption', '')
            ->assertSee('Field photo uploaded and watermarked with telemetry.');

        $evidence = EvidenceDocument::query()
            ->where('project_id', $this->project->id)
            ->where('wbs_task_id', $this->leafTask->id)
            ->first();

        $this->assertNotNull($evidence);
        $this->assertEquals('PROGRESS', $evidence->metadata['category']);
        $this->assertEquals(13.756331, round($evidence->gps_latitude, 6));
        $this->assertEquals(100.501765, round($evidence->gps_longitude, 6));
        $this->assertNotNull($evidence->sha256);
        $this->assertTrue(Storage::disk('evidence')->exists($evidence->storage_path));

        $this->assertDatabaseHas('audit_events', [
            'project_id' => $this->project->id,
            'actor_id' => $this->member->id,
            'action' => 'evidence.site_photo_uploaded',
        ]);
    }

    public function test_supervisor_can_upload_photo_attached_to_constraint(): void
    {
        $this->actingAs($this->member);

        $fakePhoto = UploadedFile::fake()->image('hydraulic_leak.jpg', 800, 600);

        Livewire::test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->call('switchTab', 'PHOTOS')
            ->set('photoFile', $fakePhoto)
            ->set('photoConstraintId', $this->constraint->id)
            ->set('photoCaption', 'Hydraulic fluid leak on outrigger valve block.')
            ->set('photoCategory', 'DEFECT')
            ->set('photoLat', 13.758000)
            ->set('photoLng', 100.502000)
            ->call('uploadSitePhoto')
            ->assertHasNoErrors();

        $evidence = EvidenceDocument::query()
            ->where('project_id', $this->project->id)
            ->where('constraint_id', $this->constraint->id)
            ->first();

        $this->assertNotNull($evidence);
        $this->assertEquals('DEFECT', $evidence->metadata['category']);
        $this->assertTrue(Storage::disk('evidence')->exists($evidence->storage_path));
    }

    public function test_watermark_service_stamps_overlay_correctly(): void
    {
        $watermarkService = app(SitePhotoWatermarkService::class);

        $file = UploadedFile::fake()->image('sample.jpg', 400, 300);
        $result = $watermarkService->applyWatermark($file->get(), 'image/jpeg', [
            'category' => 'SAFETY',
            'project_code' => 'SITE-01',
            'ref_text' => 'Perimeter Barrier Check',
            'lat' => 13.750000,
            'lng' => 100.500000,
            'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
            'inspector_name' => 'Inspector Dave',
        ]);

        $this->assertNotEmpty($result['bytes']);
        $this->assertEquals('image/jpeg', $result['mime']);
    }

    public function test_evidence_preview_controller_serves_inline_stream(): void
    {
        $this->actingAs($this->member);

        $watermarkService = app(SitePhotoWatermarkService::class);
        $fakePhoto = UploadedFile::fake()->image('daily_pour.jpg', 640, 480);

        $evidence = $watermarkService->storeSitePhoto($this->project, $this->member, $fakePhoto, [
            'task_id' => $this->leafTask->id,
            'caption' => 'Concrete slump test passed.',
            'category' => 'QUALITY_CHECK',
            'gps_latitude' => 13.755555,
            'gps_longitude' => 100.505555,
        ]);

        $response = $this->get(route('projects.evidence.preview', [$this->project, $evidence]));

        $response->assertOk();
        $this->assertStringContainsString('image/', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_gallery_filtering_and_modal_view(): void
    {
        $this->actingAs($this->member);

        $watermarkService = app(SitePhotoWatermarkService::class);
        $file1 = UploadedFile::fake()->image('p1.jpg', 400, 300);
        $file2 = UploadedFile::fake()->image('p2.jpg', 400, 300);

        $photo1 = $watermarkService->storeSitePhoto($this->project, $this->member, $file1, [
            'task_id' => $this->leafTask->id,
            'category' => 'PROGRESS',
            'gps_latitude' => 13.700000,
            'gps_longitude' => 100.500000,
            'caption' => 'Formwork ready',
        ]);

        $photo2 = $watermarkService->storeSitePhoto($this->project, $this->member, $file2, [
            'constraint_id' => $this->constraint->id,
            'category' => 'DEFECT',
            'caption' => 'Spalling on column C3',
        ]);

        Livewire::test(SiteSupervisorQuickCaptureView::class, ['project' => $this->project])
            ->call('switchTab', 'PHOTOS')
            ->assertSee('Formwork ready')
            ->assertSee('Spalling on column C3')
            ->set('galleryFilter', 'DEFECT')
            ->assertSee('Spalling on column C3')
            ->assertDontSee('Formwork ready')
            ->call('openPhotoModal', $photo2->id)
            ->assertSet('previewEvidenceId', $photo2->id)
            ->assertSee('Spalling on column C3')
            ->call('closePhotoModal')
            ->assertSet('previewEvidenceId', null);
    }
}
