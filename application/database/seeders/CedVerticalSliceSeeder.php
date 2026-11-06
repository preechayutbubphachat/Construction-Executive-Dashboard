<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\WbsTask;
use Illuminate\Database\Seeder;

class CedVerticalSliceSeeder extends Seeder
{
    public const PROJECT_ID = 'CED-PROJECT-001';

    public const TASK_ID = 'CED-TASK-001';

    public function run(): void
    {
        Project::query()->firstOrCreate(
            ['id' => self::PROJECT_ID],
            [
                'code' => 'CED-DEMO-001',
                'name' => 'CED-2026 Local Vertical Slice',
                'baseline_start_date' => '2026-09-01',
                'baseline_finish_date' => '2026-09-30',
                'budget_amount' => '1000000.00',
                'status' => 'ACTIVE',
            ],
        );

        WbsTask::query()->firstOrCreate(
            ['id' => self::TASK_ID],
            [
                'project_id' => self::PROJECT_ID,
                'parent_id' => null,
                'wbs_code' => '1.0',
                'name' => 'Authorized baseline leaf task',
                'baseline_start_date' => '2026-09-01',
                'baseline_finish_date' => '2026-09-30',
                'weight' => '100.0000',
                'status' => 'IN_PROGRESS',
                'is_baseline' => true,
                'is_leaf' => true,
            ],
        );
    }
}
