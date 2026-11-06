<?php

use App\Http\Controllers\EvidenceDownloadController;
use App\Http\Controllers\EvidenceUploadController;
use App\Http\Controllers\ExecutivePortfolioController;
use App\Http\Controllers\ExecutiveReportController;
use App\Http\Controllers\FinanceTransactionReadController;
use App\Http\Controllers\ImportPreviewController;
use App\Http\Controllers\ProgressEntryController;
use App\Http\Controllers\ProjectAuditReadController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectHealthController;
use App\Http\Controllers\ProjectScheduleController;
use App\Http\Controllers\ProjectWbsController;
use App\Http\Controllers\WbsTaskController;
use App\Http\Controllers\WbsTaskReadController;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/', ExecutivePortfolioController::class);

Route::get('/projects/portfolio', ExecutivePortfolioController::class)
    ->name('projects.portfolio');

Route::get('/projects/{project}/executive', [ProjectHealthController::class, 'show'])
    ->name('projects.executive');

Route::get('/projects/{project}/import-preview', [ImportPreviewController::class, 'create'])
    ->middleware('auth')->name('projects.import-preview.create');
Route::post('/projects/{project}/import-preview', [ImportPreviewController::class, 'store'])
    ->middleware('auth')->name('projects.import-preview.store');

Route::get('/projects/{project}/finance/transactions/{transaction}', [FinanceTransactionReadController::class, 'show'])
    ->name('projects.finance.transactions.show');

Route::get('/projects/{project}/audit/{audit}', [ProjectAuditReadController::class, 'show'])
    ->name('projects.audit.show');

Route::get('/projects/{project}/finance', function (Project $project) {
    Gate::authorize('view', $project);

    return view('projects.finance', ['project' => $project]);
})->middleware('auth')->name('projects.finance');

Route::get('/projects/{project}/wbs/tasks/{task}', [WbsTaskReadController::class, 'show'])
    ->name('projects.wbs.tasks.show');
Route::get('/projects/{project}/wbs', ProjectWbsController::class)
    ->name('projects.wbs.show');
Route::get('/projects/{project}/schedule', ProjectScheduleController::class)
    ->name('projects.schedule.show');
Route::patch('/projects/{project}', [ProjectController::class, 'update'])
    ->name('projects.update');
Route::patch('/projects/{project}/lifecycle', [ProjectController::class, 'transition'])
    ->name('projects.lifecycle.update');
Route::post('/projects/{project}/archive', [ProjectController::class, 'archive'])
    ->name('projects.archive');
Route::post('/projects/{project}/wbs-tasks', [WbsTaskController::class, 'store'])
    ->name('projects.wbs-tasks.store');
Route::patch('/projects/{project}/wbs-tasks/{task}', [WbsTaskController::class, 'update'])
    ->name('projects.wbs-tasks.update');
Route::post('/projects/{project}/wbs-tasks/{task}/progress', [ProgressEntryController::class, 'store'])
    ->name('projects.wbs-tasks.progress.store');

Route::post('/projects/{project}/evidence', EvidenceUploadController::class)
    ->middleware('auth')
    ->name('projects.evidence.store');

Route::get('/projects/{project}/evidence/{evidence}', EvidenceDownloadController::class)
    ->name('projects.evidence.download');

Route::get('/projects/{project}/report', ExecutiveReportController::class)
    ->name('projects.report');

Route::get('/projects/{project}/constraints', function (Project $project) {
    abort_unless(request()->user() !== null, 403);
    Gate::authorize('view', $project);

    return view('projects.constraints', ['project' => $project]);
})->name('projects.constraints');
