# Multi-Project Comparative Dashboard & Risk Heatmap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a Multi-Project Comparative Dashboard and 2D Risk Heatmap Matrix for C-Level executives that benchmarks permitted projects, scores cross-project risk, and provides instant comparative sorting and quadrant analysis.

**Architecture:** A domain service (`PortfolioRiskHeatmapService`) aggregates metrics across permitted projects using existing read models (`CedProjectHealthV1ReadModel`, `EarlyWarningAlertEngine`, `WeeklyLookaheadService`, `CedFinanceV1ReadModel`), calculates a composite risk index (0-100), and assigns risk tiers (`CRITICAL`, `HIGH`, `MEDIUM`, `LOW`). An interactive Livewire component (`PortfolioRiskHeatmapView`) at `/portfolio/heatmap` presents the visual Heatmap Matrix, comparative ranking table, and instant column sorting.

**Tech Stack:** PHP 8.5+, Laravel 12, Livewire 3, TailwindCSS, Alpine.js, PHPUnit.

**Spec:** CED-2026 Phase 2 — Multi-project Governance & Executive Operations (Comparative Dashboard & Risk Heatmap Matrix).

## Global Constraints

- Strict project-scoped authorization default-deny: users can only see projects they are members of.
- The evaluation must be deterministic and pure; read-only projection with zero mutations.
- Code standard: 100% compliant with Laravel Pint (`php vendor/bin/pint --test`).
- Zero regressions across existing 149 tests / 1,807 assertions.
- Safe division by zero for projects with zero tasks or zero budget.

## Review Focus

1. Users with multiple projects must only see their own permitted projects, never unauthorized projects.
2. Users with 0 projects must see a clean, informative empty state.
3. Sorting by risk score, make-ready rate, and budget utilization must work reliably.
4. Heatmap coordinates ($X, Y$) must stay strictly within bounded quadrant bounds (0-100).
5. All test runs in the Windows sandbox must use `tests/.tmp`.

---

### Task 1: Portfolio Risk Heatmap Domain Service (`app/Services/PortfolioRiskHeatmapService.php`)

**Files:**
- Create: `application/app/Services/PortfolioRiskHeatmapService.php`
- Test: `application/tests/Unit/PortfolioRiskHeatmapServiceTest.php`

- [x] **Step 1: Write Unit Test for PortfolioRiskHeatmapService**
  Create `application/tests/Unit/PortfolioRiskHeatmapServiceTest.php`:
  - Test calculation of composite risk score and risk levels (`CRITICAL`, `HIGH`, `MEDIUM`, `LOW`).
  - Test aggregation of portfolio totals (total projects, critical count, portfolio budget, average make-ready rate).
  - Test heatmap quadrant coordinates generation.
  - Test empty portfolio handling.

- [x] **Step 2: Implement PortfolioRiskHeatmapService**
  Create `application/app/Services/PortfolioRiskHeatmapService.php`:
  - Method `forProjects(Collection $projects): array`
  - Integration with Health, Finance, Lookahead, Alerts, and Constraints read models.
  - Deterministic score weighting and quadrant assignment.

- [x] **Step 3: Run Tests and Pint**
  Run PHPUnit on `PortfolioRiskHeatmapServiceTest.php` and verify Pint style.

---

### Task 2: Interactive Livewire Heatmap & Comparative View

**Files:**
- Create: `application/app/Livewire/PortfolioRiskHeatmapView.php`
- Create: `application/resources/views/livewire/portfolio-risk-heatmap-view.blade.php`
- Create: `application/resources/views/projects/heatmap.blade.php`
- Modify: `application/routes/web.php`
- Test: `application/tests/Feature/PortfolioRiskHeatmapSurfaceTest.php`

- [x] **Step 1: Write Feature Test for Heatmap Surface**
  Create `application/tests/Feature/PortfolioRiskHeatmapSurfaceTest.php`:
  - Test authenticated user can access `/portfolio/heatmap`.
  - Test unauthenticated user receives 403 Forbidden.
  - Test only permitted projects are displayed in comparative table and matrix.
  - Test Livewire sorting by risk score, make-ready rate, and budget.
  - Test filtering by risk tier (`ALL`, `CRITICAL`, `HIGH`, `MEDIUM`, `LOW`).

- [x] **Step 2: Implement Livewire Component & Blade Views**
  - Create `application/app/Livewire/PortfolioRiskHeatmapView.php`.
  - Create `application/resources/views/livewire/portfolio-risk-heatmap-view.blade.php`:
    - Portfolio Risk Overview KPIs.
    - 2D Visual Heatmap Matrix (Schedule Risk vs Financial/Constraint Risk).
    - Sortable Comparative Matrix Table.
  - Create `application/resources/views/projects/heatmap.blade.php`.

- [x] **Step 3: Register Route in routes/web.php**
  Add `Route::get('/portfolio/heatmap', ...)->name('projects.heatmap');`.

- [x] **Step 4: Run Tests and Pint**
  Run PHPUnit on `PortfolioRiskHeatmapSurfaceTest.php` and verify Pint style.

---

### Task 3: Executive Cross-Navigation & Integration

**Files:**
- Modify: `application/resources/views/projects/portfolio.blade.php`
- Modify: `application/resources/views/projects/report.blade.php`
- Modify: `application/resources/views/projects/schedule.blade.php`
- Modify: `application/resources/views/livewire/project-alerts-center.blade.php`
- Test: `application/tests/Feature/PortfolioRiskHeatmapIntegrationTest.php`

- [x] **Step 1: Write Feature Test for Heatmap Integration**
  Create `application/tests/Feature/PortfolioRiskHeatmapIntegrationTest.php`:
  - Verify link to `/portfolio/heatmap` in Portfolio header and cards.
  - Verify link to `/portfolio/heatmap` in Executive Briefing Report.
  - Verify link to `/portfolio/heatmap` in Alerts Center.

- [x] **Step 2: Update Blade Views**
  - Add prominent "Executive Risk Heatmap" button in Portfolio view header.
  - Add breadcrumb navigation in Report, Schedule, and Alerts views.

- [x] **Step 3: Run Full Test Suite & Pint Verification**
  Run complete Unit and Feature tests ensuring 100% pass rate.

- [x] **Step 4: Update Brain State & Commit to Git**
  Update `AI-Brains/00_HOME/CURRENT_STATE.md` and `AI-Brains/00_HOME/RESUME_POINTER.md`, then commit Phase 2 แนวทาง D to Git.
