<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ImportPreviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function project(string $id, string $code): Project
    {
        return Project::create(['id' => $id, 'code' => $code, 'name' => $code, 'baseline_start_date' => '2026-01-01', 'baseline_finish_date' => '2026-12-31', 'budget_amount' => 1000, 'status' => 'ACTIVE']);
    }

    public function test_preview_routes_require_authentication(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())->filter(fn($r) => str_contains($r->getName() ?? '', 'projects.import-preview'));
        self::assertCount(2, $routes);
        foreach ($routes as $route) self::assertContains('auth', $route->middleware());
    }

    public function test_concrete_project_member_can_request_preview(): void
    {
        $project = $this->project('project-member', 'CED-MEMBER');
        $member = User::factory()->create();
        $project->members()->attach($member, ['role' => 'MEMBER']);
        $response = $this->actingAs($member)->get(route('projects.import-preview.create', $project));
        $response->assertOk()->assertSee('CED-MEMBER')->assertSee('workbook');
    }

    public function test_authenticated_non_member_is_denied_without_project_data(): void
    {
        $project = $this->project('project-private', 'CED-PRIVATE');
        $outsider = User::factory()->create();
        $this->actingAs($outsider)->get(route('projects.import-preview.create', $project))->assertForbidden()->assertDontSee('CED-PRIVATE');
    }

    public function test_member_of_another_project_is_denied_cross_project(): void
    {
        $target = $this->project('project-target', 'CED-TARGET');
        $other = $this->project('project-other', 'CED-OTHER');
        $member = User::factory()->create();
        $other->members()->attach($member, ['role' => 'MEMBER']);
        $this->actingAs($member)->get(route('projects.import-preview.create', $target))->assertForbidden()->assertDontSee('CED-TARGET');
    }
}
