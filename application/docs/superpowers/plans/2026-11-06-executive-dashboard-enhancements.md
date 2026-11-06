# Executive Dashboard UI and Feature Enhancements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enhance the Construction Executive Dashboard with portfolio-level KPI summaries, interactive instant filtering, a dedicated visual timeline for schedules, finance budget utilization indicators, and an executive printable briefing report.

**Architecture:** Extend existing Blade views and read models additively without altering backend contracts or domain invariants. Use Alpine.js for client-side search and status filtering in the portfolio; update `ProjectScheduleController` to render a timeline Blade view for HTML browser requests while preserving JSON responses for API clients; create a print-optimized executive briefing controller and view with `@media print` styles.

**Tech Stack:** Laravel 13, PHP 8.5, Livewire, Alpine.js, Tailwind CSS, PHPUnit.

**Spec:** Approved Design Proposal in conversation on 2026-11-06 (Portfolio KPI/Filter, Schedule Timeline View, Finance Burn-down, Printable Executive Summary).

## Global Constraints

- Preserve all existing tests, routes, contracts, and canonical domain invariants (Health rule version, Finance Maker != Approver, RM-08 link resolver).
- Keep changes strictly additive so existing feature tests in `ExecutivePortfolioRm08Test`, `ExecutiveRootIntegrationRm13Test`, `ProjectScheduleTest`, and `FinanceWorkflowTest` pass without modification.
- Always run `vendor/bin/pint --format agent` to format modified PHP files.

## Review Focus

1. **Portfolio view preserves data attributes and links:** Existing automated tests rely on `data-project-id` and link resolver URLs in `portfolio.blade.php`.
2. **Dual-format schedule response:** `ProjectScheduleController` must continue returning exact JSON when called with `Accept: application/json` or via AJAX.
3. **Authorization checks on new routes:** The new executive report route must enforce project member authentication and authorization gates identical to existing executive routes.
4. **Zero division safety:** Budget utilization and progress percent calculations must safely handle 0 or null budget/tasks without throwing exceptions.
5. **Print styling integrity:** Executive briefing view must render clean single/two-page layout on standard A4 print output with headers and status badges.

---

### Task 1: Portfolio KPI Header & Instant Filter (Alpine.js)

**Files:**
- Modify: `application/resources/views/projects/portfolio.blade.php`
- Test: `application/tests/Feature/ExecutivePortfolioEnhancementTest.php`

- [x] **Step 1: Write feature test for portfolio KPI aggregates and filter bindings**
  Create `application/tests/Feature/ExecutivePortfolioEnhancementTest.php` testing that `/projects/portfolio` renders portfolio KPI metrics (total projects count, risk counts, financial totals) and Alpine.js search/filter attributes.

- [x] **Step 2: Add portfolio KPI metrics and Alpine.js interactive filter**
  Update `application/resources/views/projects/portfolio.blade.php`:
  - Calculate portfolio-level aggregates from `$cards` in blade or helper (Total Projects, Red/Amber/Green counts, Total Planned Budget, Total Posted Expense).
  - Wrap project list in an Alpine.js component `x-data="{ search: '', filter: 'ALL' }"` with a search box and status filter pills (`ALL`, `RED`, `AMBER`, `GREEN`).
  - Add `x-show` on project cards that matches search query against project code/name and filter against health status.
  - Add quick action jump links (`Executive Health`, `Schedule & Timeline`, `Finance`, `Executive Report`).
  - Preserve all existing markup, `data-project-id`, and existing drilldown links.

- [x] **Step 3: Run tests and verify**
  Run PHPUnit tests for `ExecutivePortfolioEnhancementTest.php` and `ExecutivePortfolioRm08Test.php`.

- [x] **Step 4: Format code**
  Run `vendor/bin/pint --format agent` on modified PHP files.

---

### Task 2: Executive Schedule & Timeline Web View

**Files:**
- Modify: `application/app/Http/Controllers/ProjectScheduleController.php`
- Create: `application/resources/views/projects/schedule.blade.php`
- Test: `application/tests/Feature/ProjectScheduleViewTest.php`

- [x] **Step 1: Write test for dual-response ProjectScheduleController**
  Create `application/tests/Feature/ProjectScheduleViewTest.php` testing:
  - JSON request (`Accept: application/json`) returns original JsonResponse.
  - HTML request returns 200 OK with `projects.schedule` view containing task summary and timeline.
  - Unauthorized user is forbidden (403).

- [x] **Step 2: Update ProjectScheduleController for HTML view rendering**
  In `application/app/Http/Controllers/ProjectScheduleController.php`:
  - Check if `$request->wantsJson()`: return current `response()->json($schedule->forProject($project))`.
  - Otherwise, compute WBS task summary stats (Total tasks, Completed, In-progress, Delayed) and return `view('projects.schedule', ...)`.

- [x] **Step 3: Create schedule.blade.php**
  In `application/resources/views/projects/schedule.blade.php`:
  - Modern header with project code, back link to Portfolio.
  - Summary KPI cards for schedule (Total Tasks, On-track, Overdue, Average Progress).
  - Clean visual timeline bar for each WBS leaf task with planned start/finish dates and progress bar.
  - Highlight overdue tasks contributing to project risk.

- [x] **Step 4: Run tests and verify**
  Run PHPUnit on `ProjectScheduleViewTest.php` and existing `ProjectScheduleTest.php`.

- [x] **Step 5: Format code**
  Run `vendor/bin/pint --format agent` on modified PHP files.

---

### Task 3: Finance Budget Utilization & Visual Balance

**Files:**
- Modify: `application/resources/views/livewire/finance-transaction-workflow.blade.php`
- Test: `application/tests/Feature/FinanceTransactionWorkflowEnhancementTest.php`

- [x] **Step 1: Write test for financial indicators**
  Create `application/tests/Feature/FinanceTransactionWorkflowEnhancementTest.php` verifying that the finance view renders budget utilization indicators and links to the executive report.

- [x] **Step 2: Add Budget Utilization & Balance Visualizer**
  In `application/resources/views/livewire/finance-transaction-workflow.blade.php`:
  - Add visual progress bar for budget utilization (comparing posted expenses vs planned budget if available, or income vs expense ratio).
  - Add net balance indicator card with positive/negative color styling.
  - Add quick action link to the Executive Printable Report.

- [x] **Step 3: Run tests and verify**
  Run PHPUnit tests for `FinanceTransactionWorkflowEnhancementTest.php` and `FinanceWorkflowTest.php`.

- [x] **Step 4: Format code**
  Run `vendor/bin/pint --format agent` on modified PHP files.

---

### Task 4: Printable Executive Briefing Report

**Files:**
- Create: `application/app/Http/Controllers/ExecutiveReportController.php`
- Create: `application/resources/views/projects/report.blade.php`
- Modify: `application/routes/web.php`
- Test: `application/tests/Feature/ExecutiveReportTest.php`

- [x] **Step 1: Write test for ExecutiveReportController**
  Create `application/tests/Feature/ExecutiveReportTest.php` testing:
  - Route `/projects/{project}/report` requires auth and membership (`Gate::authorize('view', $project)`).
  - View renders project overview, health status, finance summary, and key schedule milestones.

- [x] **Step 2: Implement ExecutiveReportController**
  Create `application/app/Http/Controllers/ExecutiveReportController.php` pulling project health, finance totals, WBS summary, and evidence count.

- [x] **Step 3: Create report.blade.php**
  Create `application/resources/views/projects/report.blade.php`:
  - Clean, professional executive layout.
  - Top "Print / Save as PDF" button with `window.print()` (hidden in `@media print`).
  - Section 1: Executive Summary & Health Diagnostics (Status Badge, Reasons, Calculated At).
  - Section 2: Financial Performance Summary (Posted Income, Expense, Net, Rules).
  - Section 3: Key Schedule & Milestones status.
  - Section 4: Verified Evidence & Audit trail preview.
  - `@media print` CSS rules hiding navigation and buttons, optimizing page margins and typography.

- [x] **Step 4: Register route in routes/web.php**
  Add `Route::get('/projects/{project}/report', ExecutiveReportController::class)->name('projects.report');`

- [x] **Step 5: Run full test suite and verify**
  Run all feature tests and verify zero regressions.

- [x] **Step 6: Format code**
  Run `vendor/bin/pint --format agent` on all modified/created PHP files.
