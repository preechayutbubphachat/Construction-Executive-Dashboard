<?php

use App\Http\Controllers\EvidenceDownloadController;
use App\Http\Controllers\EvidencePreviewController;
use App\Http\Controllers\EvidenceUploadController;
use App\Http\Controllers\ExecutivePortfolioController;
use App\Http\Controllers\ExecutiveReportController;
use App\Http\Controllers\FinanceTransactionReadController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\ImportPreviewController;
use App\Http\Controllers\InterimPaymentController;
use App\Http\Controllers\MfaSecurityController;
use App\Http\Controllers\ProgressEntryController;
use App\Http\Controllers\ProjectAuditReadController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectHealthController;
use App\Http\Controllers\ProjectScheduleController;
use App\Http\Controllers\ProjectWbsController;
use App\Http\Controllers\SiteReportController;
use App\Http\Controllers\SiteSpatial3DController;
use App\Http\Controllers\WbsTaskController;
use App\Http\Controllers\WbsTaskReadController;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/', ExecutivePortfolioController::class);

Route::get('/health/liveness', [HealthCheckController::class, 'liveness'])->name('health.liveness');
Route::get('/health/readiness', [HealthCheckController::class, 'readiness'])->name('health.readiness');

Route::get('/projects/portfolio', ExecutivePortfolioController::class)
    ->name('projects.portfolio');

Route::get('/portfolio/heatmap', function () {
    abort_unless(request()->user() !== null, 403);

    return view('projects.heatmap');
})->name('projects.heatmap');

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

Route::get('/projects/{project}/evidence/{evidence}/preview', EvidencePreviewController::class)
    ->middleware('auth')
    ->name('projects.evidence.preview');

Route::get('/projects/{project}/report', ExecutiveReportController::class)
    ->name('projects.report');

Route::get('/projects/{project}/reports/daily', [SiteReportController::class, 'daily'])
    ->name('projects.reports.daily');

Route::get('/projects/{project}/reports/weekly', [SiteReportController::class, 'weekly'])
    ->name('projects.reports.weekly');

Route::get('/projects/{project}/constraints', function (Project $project) {
    abort_unless(request()->user() !== null, 403);
    Gate::authorize('view', $project);

    return view('projects.constraints', ['project' => $project]);
})->name('projects.constraints');

Route::get('/projects/{project}/lookahead', function (Project $project) {
    abort_unless(request()->user() !== null, 403);
    Gate::authorize('view', $project);

    return view('projects.lookahead', ['project' => $project]);
})->name('projects.lookahead');

Route::get('/projects/{project}/alerts', function (Project $project) {
    abort_unless(request()->user() !== null, 403);
    Gate::authorize('view', $project);

    return view('projects.alerts', ['project' => $project]);
})->name('projects.alerts');

Route::get('/projects/{project}/site', function (Project $project) {
    abort_unless(request()->user() !== null, 403);
    Gate::authorize('view', $project);

    return view('projects.site', ['project' => $project]);
})->name('projects.site');

Route::get('/projects/{project}/3d-twin', [SiteSpatial3DController::class, 'show'])
    ->name('projects.3d-twin');
Route::get('/projects/{project}/3d-twin/data', [SiteSpatial3DController::class, 'data'])
    ->name('projects.3d-twin.data');

Route::get('/projects/{project}/evm', function (Project $project) {
    abort_unless(request()->user() !== null, 403);
    Gate::authorize('view', $project);

    return view('projects.evm', ['project' => $project]);
})->name('projects.evm');

// Commercial Execution & Interim Payment Certificates (IPC Workflow)
Route::get('/projects/{project}/ipc', [InterimPaymentController::class, 'index'])
    ->name('projects.ipc.index');
Route::get('/projects/{project}/ipc/create', [InterimPaymentController::class, 'create'])
    ->name('projects.ipc.create');
Route::post('/projects/{project}/ipc', [InterimPaymentController::class, 'store'])
    ->name('projects.ipc.store');
Route::get('/projects/{project}/ipc/{ipc}', [InterimPaymentController::class, 'show'])
    ->name('projects.ipc.show');
Route::post('/projects/{project}/ipc/{ipc}/verify-site', [InterimPaymentController::class, 'verifySite'])
    ->name('projects.ipc.verify-site');
Route::post('/projects/{project}/ipc/{ipc}/verify-qs', [InterimPaymentController::class, 'verifyQs'])
    ->name('projects.ipc.verify-qs');
Route::post('/projects/{project}/ipc/{ipc}/approve-director', [InterimPaymentController::class, 'approveDirector'])
    ->name('projects.ipc.approve-director');

// Multi-Factor Authentication (MFA / 2FA TOTP)
Route::get('/mfa/setup', [MfaSecurityController::class, 'setup'])->name('mfa.setup');
Route::post('/mfa/enable', [MfaSecurityController::class, 'enable'])->name('mfa.enable');
Route::post('/mfa/disable', [MfaSecurityController::class, 'disable'])->name('mfa.disable');
Route::get('/mfa/challenge', [MfaSecurityController::class, 'challenge'])->name('mfa.challenge');
Route::post('/mfa/verify', [MfaSecurityController::class, 'verify'])->name('mfa.verify');
