<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\SiteSpatial3DProjectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SiteSpatial3DController extends Controller
{
    /**
     * Show the interactive 3D Spatial Digital Twin page.
     */
    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('projects.spatial-3d', [
            'project' => $project,
        ]);
    }

    /**
     * Provide the pure 3D Spatial Digital Twin JSON projection.
     */
    public function data(Project $project, SiteSpatial3DProjectionService $service): JsonResponse
    {
        Gate::authorize('view', $project);

        $payload = $service->project($project);

        return response()->json($payload);
    }
}
