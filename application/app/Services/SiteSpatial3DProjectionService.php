<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectConstraint;
use App\Models\WbsTask;

class SiteSpatial3DProjectionService
{
    public const VERSION = 'CED_SITE_SPATIAL_3D_V1';

    /**
     * Projects a deterministic 3D spatial building digital twin representation
     * from project WBS tasks and real-time site constraints.
     *
     * @return array<string, mixed>
     */
    public function project(Project $project): array
    {
        $tasks = WbsTask::query()
            ->where('project_id', $project->id)
            ->where('is_leaf', true)
            ->orderBy('wbs_code')
            ->get();

        $openConstraints = ProjectConstraint::query()
            ->where('project_id', $project->id)
            ->where('status', 'OPEN')
            ->get();

        $constraintsByTask = $openConstraints->groupBy('task_id');

        $elements = [];
        $totalHeight = 0;
        $levelHeight = 3.5;

        foreach ($tasks as $index => $task) {
            $taskConstraints = $constraintsByTask->get($task->id, collect());
            $hasBlocker = $taskConstraints->isNotEmpty();
            $worstSeverity = $hasBlocker ? $taskConstraints->sortByDesc(fn ($c) => match ($c->severity) {
                'CRITICAL' => 4,
                'HIGH' => 3,
                'MEDIUM' => 2,
                default => 1,
            })->first()?->severity : null;

            $isFoundation = str_contains(strtolower($task->name), 'foundation') || str_contains(strtolower($task->name), 'pile');
            $isRoof = str_contains(strtolower($task->name), 'roof');

            $posY = $isFoundation ? -1.5 : ($index * $levelHeight);
            $dimWidth = $isFoundation ? 18.0 : ($isRoof ? 14.0 : 16.0);
            $dimHeight = $isFoundation ? 2.0 : 2.8;
            $dimDepth = $isFoundation ? 18.0 : ($isRoof ? 14.0 : 16.0);

            // Determine status colors
            if ($hasBlocker) {
                $colorHex = '#f43f5e'; // Rose-500 Blocker
                $emissiveHex = '#881337';
                $opacity = 0.95;
            } elseif ($task->status === 'COMPLETED') {
                $colorHex = '#10b981'; // Emerald-500
                $emissiveHex = '#064e3b';
                $opacity = 0.90;
            } elseif ($task->status === 'IN_PROGRESS') {
                $colorHex = '#f59e0b'; // Amber-500
                $emissiveHex = '#78350f';
                $opacity = 0.85;
            } else {
                $colorHex = '#64748b'; // Slate-500 (Planned/Ghosted)
                $emissiveHex = '#0f172a';
                $opacity = 0.40;
            }

            $elements[] = [
                'id' => 'elem_'.$task->id,
                'task_id' => $task->id,
                'wbs_code' => $task->wbs_code,
                'name' => $task->name,
                'type' => $isFoundation ? 'FOUNDATION' : ($isRoof ? 'ROOF' : 'FLOOR_STRUCTURE'),
                'level_index' => $index,
                'status' => $task->status,
                'weight' => (float) $task->weight,
                'is_blocked' => $hasBlocker,
                'constraint_severity' => $worstSeverity,
                'constraints_count' => $taskConstraints->count(),
                'active_constraints' => $taskConstraints->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'type' => $c->type,
                    'severity' => $c->severity,
                ])->values()->all(),
                'position' => [
                    'x' => 0.0,
                    'y' => (float) $posY,
                    'z' => 0.0,
                ],
                'dimensions' => [
                    'width' => (float) $dimWidth,
                    'height' => (float) $dimHeight,
                    'depth' => (float) $dimDepth,
                ],
                'color_hex' => $colorHex,
                'emissive_hex' => $emissiveHex,
                'opacity' => (float) $opacity,
            ];

            if ($posY > $totalHeight) {
                $totalHeight = $posY;
            }
        }

        $elementsCol = collect($elements);

        return [
            'version' => self::VERSION,
            'project_id' => $project->id,
            'project_code' => $project->code,
            'project_name' => $project->name,
            'summary' => [
                'total_elements' => $elementsCol->count(),
                'completed_count' => $elementsCol->where('status', 'COMPLETED')->count(),
                'in_progress_count' => $elementsCol->where('status', 'IN_PROGRESS')->count(),
                'not_started_count' => $elementsCol->where('status', 'NOT_STARTED')->count(),
                'blocked_count' => $elementsCol->where('is_blocked', true)->count(),
            ],
            'camera_bounds' => [
                'center' => [0.0, (float) ($totalHeight / 2), 0.0],
                'recommended_distance' => max(40.0, (float) ($totalHeight * 2.2)),
            ],
            'elements' => $elements,
        ];
    }
}
