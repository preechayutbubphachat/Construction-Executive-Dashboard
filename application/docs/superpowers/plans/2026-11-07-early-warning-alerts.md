# Automated Early-Warning Alerts & Notification Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an automated Early-Warning Alerts Engine for Construction Executive Dashboard (CED-2026) that deterministically evaluates project operations, detects schedule delays, lookahead impediments, overdue constraints, and budget burnouts, and surfaces actionable alerts across the dashboard.

**Architecture:** A domain service (`EarlyWarningAlertEngine`) evaluates operational records against 4 risk detection rules (`SCHEDULE_OVERDUE`, `LOOKAHEAD_BLOCKED`, `CRITICAL_CONSTRAINT_STALE`, `BUDGET_BURNOUT`), assigns standardized severity levels (`CRITICAL`, `WARNING`, `INFO`), and produces structured, actionable alert payloads. An interactive Livewire component (`ProjectAlertsCenter`) at `/projects/{project}/alerts` provides filtering and navigation. The alert signals are integrated into Portfolio, Report, Schedule, Constraints, and Lookahead views.

**Tech Stack:** PHP 8.5+, Laravel 12, Livewire 3, TailwindCSS, Alpine.js, PHPUnit.

**Spec:** CED-2026 Phase 2 — Operations Workflow (Automated Early-Warning Alerts & Notification Engine).

## Global Constraints

- Strict project-scoped authorization default-deny: `Gate::authorize('view', $project)` on all endpoints.
- Pure and deterministic evaluation: the alert engine generates alerts dynamically from source records without mutating state.
- Zero false positives on completed tasks or resolved constraints.
- Code standard: 100% compliant with Laravel Pint (`php vendor/bin/pint --test`).
- Zero regressions across existing 135 tests / 1,766 assertions.

## Review Focus

1. Completed tasks (`status === 'COMPLETED'` or `progress >= 100%`) must never trigger `SCHEDULE_OVERDUE`.
2. Resolved constraints (`status === 'RESOLVED'`) must never trigger `CRITICAL_CONSTRAINT_STALE`.
3. Division by zero in budget utilization must be handled safely when `budget_amount` is 0 or null.
4. Alerts must provide valid and authorized destination URLs.
5. All tests must run cleanly in the Windows sandbox using `tests/.tmp`.

---

### Task 1: Early-Warning Alert Domain Engine (`app/Services/EarlyWarningAlertEngine.php`)

**Files:**
- Create: `application/app/Services/EarlyWarningAlertEngine.php`
- Test: `application/tests/Unit/EarlyWarningAlertEngineTest.php`

- [x] **Step 1: Write Unit Test for EarlyWarningAlertEngine**
  Create `application/tests/Unit/EarlyWarningAlertEngineTest.php`:
  - Test detection of `SCHEDULE_OVERDUE` for uncompleted tasks past `baseline_finish_date`.
  - Test detection of `LOOKAHEAD_BLOCKED` for upcoming tasks with open constraints.
  - Test detection of `CRITICAL_CONSTRAINT_STALE` for critical constraints past `target_resolution_date` or open > 7 days.
  - Test detection of `BUDGET_BURNOUT` when expense utilization exceeds 90%.
  - Test edge cases: 0 budget, completed tasks, resolved constraints returning zero false alerts.

- [x] **Step 2: Implement EarlyWarningAlertEngine**
  Create `application/app/Services/EarlyWarningAlertEngine.php`:
  - Method `forProject(Project $project, ?Carbon $now = null): array`
  - Rule evaluators for Schedule, Lookahead, Constraints, and Budget.
  - Standardized alert structure: `id`, `category`, `severity`, `title`, `message`, `entity_type`, `entity_id`, `action_url`.
  - Summary metrics: `total_alerts`, `critical_count`, `warning_count`, `highest_severity`.

- [x] **Step 3: Run Tests and Pint**
  Run PHPUnit on `EarlyWarningAlertEngineTest.php` and verify Pint style.

---

### Task 2: Interactive Livewire Alerts Center & Blade Views

**Files:**
- Create: `application/app/Livewire/ProjectAlertsCenter.php`
- Create: `application/resources/views/livewire/project-alerts-center.blade.php`
- Create: `application/resources/views/projects/alerts.blade.php`
- Modify: `application/routes/web.php`
- Test: `application/tests/Feature/ProjectAlertsSurfaceTest.php`

- [x] **Step 1: Write Feature Test for Alerts Surface**
  Create `application/tests/Feature/ProjectAlertsSurfaceTest.php`:
  - Test authorized project member can access `/projects/{project}/alerts`.
  - Test outsider receives 403 Forbidden.
  - Test alerts center displays active alerts with correct severity badges.
  - Test filtering by category (`ALL`, `CRITICAL`, `SCHEDULE`, `CONSTRAINT`, `FINANCE`).
  - Test action links direct to relevant resolution pages.

- [x] **Step 2: Implement Livewire Component & Blade Views**
  - Create `application/app/Livewire/ProjectAlertsCenter.php`.
  - Create `application/resources/views/livewire/project-alerts-center.blade.php`:
    - KPI cards: Total Alerts, Critical Alerts, Warning Alerts, Operational Soundness.
    - Filter tabs: All, Critical Only, Schedule, Constraints, Finance.
    - Alert item cards with category badges, time elapsed, entity details, and direct jump button.
  - Create `application/resources/views/projects/alerts.blade.php` container view.

- [x] **Step 3: Register Route in routes/web.php**
  Add `Route::get('/projects/{project}/alerts', ...)->name('projects.alerts');`.

- [x] **Step 4: Run Tests and Pint**
  Run PHPUnit on `ProjectAlertsSurfaceTest.php` and verify Pint style.

---

### Task 3: Executive Cross-View Integration & Alert Linkage

**Files:**
- Modify: `application/app/Http/Controllers/ExecutiveReportController.php`
- Modify: `application/resources/views/projects/report.blade.php`
- Modify: `application/resources/views/projects/portfolio.blade.php`
- Modify: `application/resources/views/projects/schedule.blade.php`
- Modify: `application/resources/views/projects/lookahead.blade.php`
- Modify: `application/resources/views/livewire/project-constraint-manager.blade.php`
- Modify: `application/resources/views/livewire/weekly-lookahead-view.blade.php`
- Test: `application/tests/Feature/ExecutiveAlertsIntegrationTest.php`

- [x] **Step 1: Write Feature Test for Executive Alerts Integration**
  Create `application/tests/Feature/ExecutiveAlertsIntegrationTest.php`:
  - Verify Alerts summary section appears in Executive Briefing Report (`/projects/{project}/report`).
  - Verify Alert indicator/link is present in Portfolio view (`/projects/portfolio`).
  - Verify Alert navigation link is present in Schedule, Constraints, and Lookahead views.

- [x] **Step 2: Update Controllers and Blade Views**
  - Inject `EarlyWarningAlertEngine` in `ExecutiveReportController` and render "Early-Warning Operational Alerts" section in `report.blade.php`.
  - Add alert badge and link on each project card in `portfolio.blade.php`.
  - Add Alerts navigation link in breadcrumbs and headers of `schedule.blade.php`, `project-constraint-manager.blade.php`, and `weekly-lookahead-view.blade.php`.

- [x] **Step 3: Run Full Test Suite & Pint Verification**
  Run complete Unit and Feature tests ensuring 100% pass rate and run Pint format check.

- [x] **Step 4: Update Brain State & Commit to Git**
  Update `AI-Brains/00_HOME/CURRENT_STATE.md` and `AI-Brains/00_HOME/RESUME_POINTER.md`, then commit Phase 2 แนวทาง C to Git.
