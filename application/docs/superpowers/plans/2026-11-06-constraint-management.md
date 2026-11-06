# Constraint & Issue Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement Phase 2 Constraint & Issue Management allowing field teams and PMs to record, track, and resolve site constraints linked to WBS tasks while exposing root causes directly to executive dashboards.

**Architecture:** Add a canonical `project_constraints` table with Eloquent relations to `projects`, `wbs_tasks`, and `users`. Implement a dedicated domain service `ConstraintManagement` that creates audit trail events on mutation. Build an interactive Livewire manager (`ProjectConstraintManager`) for field/PM interaction, and link critical constraints into existing Executive Portfolio, Schedule, and Briefing Report views.

**Tech Stack:** Laravel 13, PHP 8.5, Livewire, Alpine.js, Tailwind CSS, PHPUnit.

**Spec:** Approved Constraint & Issue Management Design Specification (conversation 2026-11-06).

## Global Constraints

- Strict project-scoped authorization (default-deny, member check).
- Zero breaking changes to existing Phase 1 models, endpoints, or tests.
- Format all modified/created PHP files with `vendor/bin/pint --format agent`.
- Audit every constraint creation and resolution event into `audit_events`.

## Review Focus

1. **Task foreign key integrity:** Constraint may belong to a project with or without a specific WBS task (nullable `task_id`). When task is set, it must belong to the same project.
2. **Status lifecycle transitions:** Status transitions `OPEN` -> `IN_PROGRESS` -> `RESOLVED`. Once resolved, `resolved_at` must be recorded.
3. **Audit event recording:** Creating, changing severity, and resolving constraints must log an `AuditEvent` with actor and occurred timestamp.
4. **Member authorization:** Non-members cannot view, create, or resolve constraints (403 Forbidden).
5. **Executive drill-down visibility:** Projects with open critical constraints must surface them with task links in Executive Report and Schedule.

---

### Task 1: Database Migration & ProjectConstraint Model

**Files:**
- Create: `application/database/migrations/2026_11_06_000100_create_project_constraints_table.php`
- Create: `application/app/Models/ProjectConstraint.php`
- Modify: `application/app/Models/Project.php`
- Modify: `application/app/Models/WbsTask.php`
- Test: `application/tests/Unit/ProjectConstraintModelTest.php`

- [x] **Step 1: Write Unit Test for ProjectConstraint Model**
  Create `application/tests/Unit/ProjectConstraintModelTest.php` testing model instantiation, fillable attributes, relations to Project, WbsTask, Creator, Assignee, and status scopes (`open()`, `critical()`).

- [x] **Step 2: Create Migration for project_constraints**
  Create `application/database/migrations/2026_11_06_000100_create_project_constraints_table.php`:
  - `id` (string 36 primary)
  - `project_id` (string 36, foreign key)
  - `task_id` (string 36 nullable, foreign key)
  - `type` (string 32)
  - `title` (string)
  - `description` (text nullable)
  - `severity` (string 16)
  - `status` (string 16 default 'OPEN')
  - `target_resolution_date` (date nullable)
  - `resolved_at` (timestamp nullable)
  - `resolution_notes` (text nullable)
  - `assigned_to` (foreignId nullable, constrained users)
  - `created_by` (foreignId, constrained users)
  - timestamps

- [x] **Step 3: Create ProjectConstraint Model & Add Relations**
  - Create `application/app/Models/ProjectConstraint.php` with casts, relations, and query scopes.
  - Add `constraints()` HasMany relation in `application/app/Models/Project.php`.
  - Add `constraints()` HasMany relation in `application/app/Models/WbsTask.php`.

- [x] **Step 4: Run Tests and Pint**
  Run PHPUnit on `ProjectConstraintModelTest.php` and format with Pint.

---

### Task 2: Constraint Management Service & Audit Trail

**Files:**
- Create: `application/app/Services/ConstraintManagement.php`
- Test: `application/tests/Feature/ConstraintManagementWorkflowTest.php`

- [x] **Step 1: Write Feature Test for ConstraintManagement Service**
  Create `application/tests/Feature/ConstraintManagementWorkflowTest.php`:
  - Test `create` generates constraint with valid ULID and logs `AuditEvent`.
  - Test cross-project task assignment is rejected with `DomainException`.
  - Test `resolve` records `resolved_at`, resolution notes, and logs audit event.

- [x] **Step 2: Implement ConstraintManagement Service**
  Create `application/app/Services/ConstraintManagement.php`:
  - Implement `create(Project $project, User $creator, array $attributes): ProjectConstraint`
  - Implement `resolve(ProjectConstraint $constraint, User $resolver, string $notes): ProjectConstraint`
  - Implement `updateStatus(ProjectConstraint $constraint, User $actor, string $newStatus): ProjectConstraint`

- [x] **Step 3: Run Tests and Pint**
  Run PHPUnit on `ConstraintManagementWorkflowTest.php` and format with Pint.

---

### Task 3: Interactive Livewire Constraint Manager View

**Files:**
- Create: `application/app/Livewire/ProjectConstraintManager.php`
- Create: `application/resources/views/livewire/project-constraint-manager.blade.php`
- Create: `application/resources/views/projects/constraints.blade.php`
- Modify: `application/routes/web.php`
- Test: `application/tests/Feature/ProjectConstraintSurfaceTest.php`

- [x] **Step 1: Write Feature Test for Constraint Livewire Surface**
  Create `application/tests/Feature/ProjectConstraintSurfaceTest.php`:
  - Test authorized project member can view `/projects/{project}/constraints`.
  - Test unauthorized outsider gets 403 Forbidden.
  - Test creating constraint via Livewire form creates record in database.
  - Test resolving constraint updates status and renders success message.

- [x] **Step 2: Implement Livewire Component & Blade Views**
  - Create `application/app/Livewire/ProjectConstraintManager.php`.
  - Create `application/resources/views/livewire/project-constraint-manager.blade.php`:
    - Summary KPI statistics (Total, Open, Critical, Resolved).
    - Quick create form (Type, Title, WBS Task dropdown, Severity, Target Date, Assignee).
    - List of constraints with filter buttons (All, Open, Critical, Resolved).
    - Modal or inline form to resolve a constraint with notes.
  - Create `application/resources/views/projects/constraints.blade.php` hosting the Livewire component.

- [x] **Step 3: Register Route in routes/web.php**
  Add `Route::get('/projects/{project}/constraints', ...)->name('projects.constraints');`.

- [x] **Step 4: Run Tests and Pint**
  Run PHPUnit on `ProjectConstraintSurfaceTest.php` and format with Pint.

---

### Task 4: Executive Integration & Briefing Report Linkage

**Files:**
- Modify: `application/resources/views/projects/report.blade.php`
- Modify: `application/resources/views/projects/schedule.blade.php`
- Modify: `application/resources/views/projects/portfolio.blade.php`
- Modify: `application/app/Http/Controllers/ExecutiveReportController.php`
- Test: `application/tests/Feature/ExecutiveConstraintIntegrationTest.php`

- [x] **Step 1: Write Test for Executive Constraint Visibility**
  Create `application/tests/Feature/ExecutiveConstraintIntegrationTest.php` verifying that open critical constraints are surfaced in the Executive Report and Schedule views with links to `/projects/{project}/constraints`.

- [x] **Step 2: Update Controllers and Views**
  - Pass constraints to `ExecutiveReportController` and render an "Active Constraints & Impediments" section in `report.blade.php`.
  - Add "Constraints" quick link in `portfolio.blade.php` and `schedule.blade.php`.
  - Display constraint indicator badges on WBS tasks in `schedule.blade.php` if an open constraint is attached.

- [x] **Step 3: Run Full Test Suite and Verification**
  Run all Phase 1 and Phase 2 tests ensuring 100% pass rate.

- [x] **Step 4: Format Code and Commit to Git**
  Run `vendor/bin/pint --format agent` and create Git commit for Phase 2 Task A completion.
