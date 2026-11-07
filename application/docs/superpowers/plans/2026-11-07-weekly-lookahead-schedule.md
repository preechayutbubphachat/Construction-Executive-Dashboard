# Weekly Lookahead Schedule (WLS 3-6 Weeks) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Lean Construction Weekly Lookahead Schedule (WLS) module that projects WBS tasks across a 3 to 6-week window, evaluates make-ready status against active site constraints, and provides executive lookahead visibility.

**Architecture:** A domain service (`WeeklyLookaheadService`) calculates lookahead date buckets, filters overlapping leaf WBS tasks, evaluates soundness/make-ready readiness against `ProjectConstraint` records, and produces make-ready rate KPIs. An interactive Livewire component (`WeeklyLookaheadView`) renders the weekly lookahead matrix with dynamic filters (3/4/6 weeks, Make-Ready vs Constrained). The feature is integrated into Schedule, Report, and Portfolio executive interfaces.

**Tech Stack:** PHP 8.5+, Laravel 12, Livewire 3, Alpine.js, TailwindCSS, PHPUnit.

**Spec:** Construction Executive Dashboard (CED-2026) Phase 2 — Operations Workflow (Last Planner System / Lookahead Schedule).

## Global Constraints

- Strict project-scoped authorization default-deny: `Gate::authorize('view', $project)` on all endpoints.
- Read-only calculations must be deterministic and pure; lookahead projections do not mutate baseline dates.
- Make-Ready rule: A task is "Make Ready" (Sound) if and only if it has zero open constraints (`openConstraintsCount === 0`).
- Code standard: 100% compliant with Laravel Pint (`php vendor/bin/pint --test`).
- Zero test regressions across existing 124 tests / 1,721 assertions.

## Review Focus

1. Tasks spanning across window boundaries (starting before and ending inside window, or starting inside and ending after) must be correctly captured.
2. Tasks marked `COMPLETED` should not be falsely flagged as blocked even if an archived/open constraint exists.
3. Make-Ready percentage calculation must handle division by zero safely when zero tasks exist in the lookahead window (returns 100.0% or 0.0% cleanly).
4. Outsiders must be strictly rejected with 403 Forbidden.
5. Windows sandbox temp paths must continue using `tests/.tmp` to avoid permissions collisions.

---

### Task 1: Weekly Lookahead Domain Service (`app/Services/WeeklyLookaheadService.php`)

**Files:**
- Create: `application/app/Services/WeeklyLookaheadService.php`
- Test: `application/tests/Unit/WeeklyLookaheadServiceTest.php`

- [x] **Step 1: Write Unit Test for WeeklyLookaheadService**
  Create `application/tests/Unit/WeeklyLookaheadServiceTest.php`:
  - Test lookahead window calculation for 3, 4, and 6 weeks.
  - Test filtering of tasks overlapping the lookahead window.
  - Test make-ready evaluation: task with open constraint is marked `is_make_ready = false`, task without open constraint is `is_make_ready = true`.
  - Test KPI calculation (`total_tasks`, `make_ready_count`, `constrained_count`, `make_ready_rate`).
  - Test edge case of zero tasks in window returning safe defaults.

- [x] **Step 2: Implement WeeklyLookaheadService**
  Create `application/app/Services/WeeklyLookaheadService.php`:
  - Methods:
    - `forProject(Project $project, int $weeks = 3, ?Carbon $anchorDate = null): array`
    - Date window resolution (start of current week to +N weeks)
    - Weekly bucket segmenting (Week 1, Week 2, ... Week N)
    - Task overlap query: `baseline_start_date <= window_end AND baseline_finish_date >= window_start`
    - Make-ready and risk assessment for each task.

- [x] **Step 3: Run Tests and Pint**
  Run PHPUnit on `WeeklyLookaheadServiceTest.php` and verify Pint style.

---

### Task 2: Interactive Livewire Lookahead Component & Blade Views

**Files:**
- Create: `application/app/Livewire/WeeklyLookaheadView.php`
- Create: `application/resources/views/livewire/weekly-lookahead-view.blade.php`
- Create: `application/resources/views/projects/lookahead.blade.php`
- Modify: `application/routes/web.php`
- Test: `application/tests/Feature/WeeklyLookaheadSurfaceTest.php`

- [x] **Step 1: Write Feature Test for Lookahead Surface**
  Create `application/tests/Feature/WeeklyLookaheadSurfaceTest.php`:
  - Test authorized member can access `/projects/{project}/lookahead`.
  - Test outsider receives 403 Forbidden.
  - Test Livewire component toggles 3, 4, 6 weeks window and updates lookahead dataset.
  - Test filtering by `ALL`, `MAKE_READY`, and `CONSTRAINED`.
  - Test displaying active constraint details on constrained tasks.

- [x] **Step 2: Implement Livewire Component & Blade Views**
  - Create `application/app/Livewire/WeeklyLookaheadView.php`.
  - Create `application/resources/views/livewire/weekly-lookahead-view.blade.php`:
    - Summary KPI bar (Make-Ready Rate %, Total Tasks in Window, Make-Ready Ready, Blocked by Constraints).
    - Window duration pills (3 Weeks, 4 Weeks, 6 Weeks).
    - Status filter pills (All Tasks, Make-Ready Only, Constrained Only).
    - Weekly column/grouped view showing task cards with progress bars and constraint badges.
  - Create `application/resources/views/projects/lookahead.blade.php` container view.

- [x] **Step 3: Register Route in routes/web.php**
  Add `Route::get('/projects/{project}/lookahead', ...)->name('projects.lookahead');`.

- [x] **Step 4: Run Tests and Pint**
  Run PHPUnit on `WeeklyLookaheadSurfaceTest.php` and verify Pint style.

---

### Task 3: Executive Cross-View Integration & Reporting Linkage

**Files:**
- Modify: `application/app/Http/Controllers/ExecutiveReportController.php`
- Modify: `application/resources/views/projects/report.blade.php`
- Modify: `application/resources/views/projects/schedule.blade.php`
- Modify: `application/resources/views/projects/portfolio.blade.php`
- Modify: `application/resources/views/livewire/project-constraint-manager.blade.php`
- Test: `application/tests/Feature/ExecutiveLookaheadIntegrationTest.php`

- [x] **Step 1: Write Feature Test for Executive Integration**
  Create `application/tests/Feature/ExecutiveLookaheadIntegrationTest.php`:
  - Verify Lookahead summary (Make-Ready Rate % and upcoming tasks) appears in Executive Briefing Report (`/projects/{project}/report`).
  - Verify Lookahead quick link is present in Schedule view (`/projects/{project}/schedule`).
  - Verify Lookahead quick link is present in Constraints view (`/projects/{project}/constraints`).
  - Verify Lookahead quick link is present in Portfolio view (`/projects/portfolio`).

- [x] **Step 2: Update Controllers and Blade Views**
  - Inject `WeeklyLookaheadService` in `ExecutiveReportController` and pass 3-week lookahead summary to `report.blade.php`.
  - Add Lookahead metrics & link in `report.blade.php`.
  - Add Lookahead navigation button in `schedule.blade.php`, `portfolio.blade.php`, and `project-constraint-manager.blade.php`.

- [x] **Step 3: Run Full Test Suite & Pint Verification**
  Run all Unit and Feature tests (target: 100% pass) and run Pint across all modified files.

- [x] **Step 4: Update Brain State & Commit to Git**
  Update `AI-Brains/00_HOME/CURRENT_STATE.md` and `AI-Brains/00_HOME/RESUME_POINTER.md`, then commit Phase 2 แนวทาง B to Git.
