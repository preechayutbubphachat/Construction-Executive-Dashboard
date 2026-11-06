<?php

namespace Tests\Feature;

use App\Models\EvidenceDocument;
use App\Models\Project;
use App\Models\User;
use App\Services\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateEvidenceAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_upload_and_route_is_authenticated(): void
    {
        $project = $this->project('CED-PROJECT-001');

        self::assertContains('auth', Route::getRoutes()->getByName('projects.evidence.store')->middleware());
        $this->assertDatabaseCount('evidence_documents', 0);
    }

    public function test_member_uploads_supported_pdf_with_server_identity_hash_and_private_key(): void
    {
        Storage::fake('evidence');
        $project = $this->project('CED-PROJECT-001');
        $member = $this->member($project);
        $contents = "%PDF-1.7\nprivate evidence bytes\n%%EOF";

        $response = $this->actingAs($member)->post(route('projects.evidence.store', $project), [
            'evidence' => UploadedFile::fake()->createWithContent('../client-name.pdf', $contents),
            'sha256' => str_repeat('0', 64),
        ]);

        $response->assertRedirect(route('projects.executive', $project));
        $evidence = EvidenceDocument::query()->sole();
        self::assertSame($project->id, $evidence->project_id);
        self::assertSame(hash('sha256', $contents), $evidence->sha256);
        self::assertSame('PRIVATE', $evidence->access_class);
        self::assertSame('evidence', $evidence->storage_disk);
        self::assertStringStartsWith($project->id.'/', $evidence->storage_path);
        self::assertStringNotContainsString('client-name', $evidence->storage_path);
        self::assertSame('client-name.pdf', $evidence->original_name);
        Storage::disk('evidence')->assertExists($evidence->storage_path);
        $this->assertDatabaseHas('audit_events', [
            'project_id' => $project->id,
            'action' => 'evidence.created',
            'auditable_id' => $evidence->id,
        ]);
    }

    public function test_cross_project_member_cannot_tamper_route_project_id(): void
    {
        Storage::fake('evidence');
        $project = $this->project('CED-PROJECT-001');
        $foreignProject = $this->project('CED-PROJECT-002');
        $member = $this->member($project);

        $this->actingAs($member)->post(route('projects.evidence.store', $foreignProject), [
            'evidence' => $this->pdf(),
        ])->assertForbidden();

        $this->assertDatabaseCount('evidence_documents', 0);
        Storage::disk('evidence')->assertDirectoryEmpty($foreignProject->id);
    }

    public function test_unsupported_mime_script_archive_and_oversize_are_rejected(): void
    {
        Storage::fake('evidence');
        $project = $this->project('CED-PROJECT-001');
        $member = $this->member($project);

        foreach ([
            UploadedFile::fake()->createWithContent('run.php', '<?php echo "no";'),
            UploadedFile::fake()->createWithContent('payload.zip', "PK\x03\x04archive"),
            UploadedFile::fake()->createWithContent('misleading.pdf', '<?php echo "script";'),
            UploadedFile::fake()->create('large.pdf', 20481, 'application/pdf'),
        ] as $upload) {
            $response = $this->actingAs($member)
                ->post(route('projects.evidence.store', $project), ['evidence' => $upload]);
            self::assertTrue(
                $response->getSession()->has('errors'),
                'Accepted disallowed upload: '.$upload->getClientOriginalName(),
            );
        }

        $this->assertDatabaseCount('evidence_documents', 0);
        Storage::disk('evidence')->assertDirectoryEmpty($project->id);
    }

    public function test_audit_and_download_are_project_scoped_and_no_public_or_delete_route_exists(): void
    {
        Storage::fake('evidence');
        $project = $this->project('CED-PROJECT-001');
        $member = $this->member($project);
        $otherProject = $this->project('CED-PROJECT-002');
        $otherMember = $this->member($otherProject);
        $contents = "%PDF-1.7\nprivate\n%%EOF";

        $this->actingAs($member)->post(route('projects.evidence.store', $project), [
            'evidence' => UploadedFile::fake()->createWithContent('invoice.pdf', $contents),
        ])->assertRedirect();
        $evidence = EvidenceDocument::query()->sole();

        $this->actingAs($otherMember)
            ->get(route('projects.evidence.download', [$project, $evidence]))
            ->assertForbidden();
        $this->actingAs($member)
            ->get(route('projects.evidence.download', [$project, $evidence]))
            ->assertOk()
            ->assertDownload('invoice.pdf');
        $this->assertDatabaseHas('audit_events', [
            'project_id' => $project->id,
            'action' => 'evidence.downloaded',
            'auditable_id' => $evidence->id,
        ]);
        self::assertFalse(config('filesystems.disks.evidence.serve'));
        self::assertArrayNotHasKey('url', config('filesystems.disks.evidence'));
        self::assertNull(Route::getRoutes()->getByName('projects.evidence.delete'));
    }

    public function test_audit_events_are_append_only_and_isolated(): void
    {
        $project = $this->project('CED-PROJECT-001');
        $otherProject = $this->project('CED-PROJECT-002');
        $member = $this->member($project);
        $otherMember = $this->member($otherProject);
        $event = app(AuditRecorder::class)->record($project->id, $member, 'evidence.created', $project);
        app(AuditRecorder::class)->record($otherProject->id, $otherMember, 'evidence.created', $otherProject);

        $this->assertDatabaseCount('audit_events', 2);
        $this->assertDatabaseHas('audit_events', ['id' => $event->id, 'project_id' => $project->id]);
        $this->expectException(\LogicException::class);
        $event->delete();
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('evidence.pdf', "%PDF-1.7\ncontent\n%%EOF");
    }

    private function project(string $id): Project
    {
        return Project::query()->create([
            'id' => $id,
            'code' => str_replace('CED-PROJECT-', 'CED-', $id),
            'name' => $id,
            'baseline_start_date' => '2026-06-01',
            'baseline_finish_date' => '2026-06-30',
            'budget_amount' => '1000.00',
            'status' => 'ACTIVE',
        ]);
    }

    private function member(Project $project): User
    {
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'EXECUTIVE']);

        return $member;
    }
}
