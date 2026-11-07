# Mobile / Tablet Site Supervisor Quick-Capture Surface Implementation Plan

> **Goal:** Create an ergonomic, mobile/tablet-optimized field interface (`/projects/{project}/site`) for site supervisors and field engineers to log constraints, update task progress, and clear lookahead blockers with minimum taps.
> **Architecture:** Reactive Livewire 3 component with Alpine.js touch ergonomics, high-contrast Tailwind UI for outdoor visibility, strict project authorization, and direct integration with `WeeklyLookaheadService` and `ConstraintManagement`.
> **Tech Stack:** Laravel 11, Livewire 3, Alpine.js, Tailwind CSS, Eloquent ORM.
> **Execution Strategy:** Test-Driven Development (TDD) with unit & feature tests, ergonomic UI design (Operate mode), zero regression on existing suite.

---

## 1. Global Constraints & Invariants
1. **Authorization Default-Deny:** Gate check `Gate::authorize('view', $project)` must be strictly enforced.
2. **Touch-Target Ergonomics:** All buttons, selectors, and tap targets must be at least 44x44px for thumb accessibility on job site mobile devices.
3. **Pure State Transitions:** Task status transitions must obey `WbsTask::LIFECYCLE_TRANSITIONS`. Progress logging must obey domain leaf task invariants.
4. **Instant Feedback:** Actions must provide immediate visual feedback (success toasts, badge updates) without requiring full page reloads.

---

## 2. Architectural Blueprint & File Map

```
application/
├── app/
│   └── Livewire/
│       └── SiteSupervisorQuickCaptureView.php          # Interactive Mobile/Tablet Field Component
├── resources/
│   └── views/
│       ├── livewire/
│       │   └── site-supervisor-quick-capture-view.blade.php # Ergonomic Mobile/Tablet Template
│       └── projects/
│           └── site.blade.php                          # Full-viewport standalone field layout
├── tests/
│   └── Feature/
│       └── SiteSupervisorQuickCaptureSurfaceTest.php   # TDD Feature Test Suite
└── routes/
    └── web.php                                         # Route /projects/{project}/site
```

---

## 3. Bite-Sized Implementation Tasks

- [ ] **Task 1: Feature Tests (TDD)**
  - Create `tests/Feature/SiteSupervisorQuickCaptureSurfaceTest.php`:
    - Test unauthenticated / unauthorized returns 403.
    - Test site surface displays project info, lookahead tasks, and active constraints.
    - Test logging a site constraint directly from the mobile surface.
    - Test resolving a constraint from the mobile surface.
    - Test recording progress / task transition from the mobile surface.
  - Verify tests fail (Red).

- [ ] **Task 2: Livewire Component Implementation**
  - Implement `App\Livewire\SiteSupervisorQuickCaptureView`:
    - Tab switching: `TASKS` (Today's & Lookahead tasks), `LOG_BLOCKER` (Fast 3-click constraint logger), `CONSTRAINTS` (Active site issues), `SUMMARY` (Shift snapshot).
    - Quick actions:
      - `quickLogConstraint()`
      - `quickResolveConstraint($constraintId)`
      - `quickTransitionTask($taskId, $newStatus)`
      - `quickRecordProgress($taskId, $percent, $note)`

- [ ] **Task 3: Ergonomic Mobile & Tablet Blade View**
  - Create `resources/views/livewire/site-supervisor-quick-capture-view.blade.php`:
    - High-contrast Slate/Amber/Emerald/Rose color scheme for outdoor readability.
    - Sticky top bar with quick project badge and shift timestamp.
    - Sticky bottom mobile action navigation bar with 4 ergonomic thumb buttons.
    - Responsive 2-column layout on tablets ($\ge$ 768px).
  - Create `resources/views/projects/site.blade.php`.
  - Register route `/projects/{project}/site` in `routes/web.php`.

- [ ] **Task 4: Cross-View Navigation Integration**
  - Add "Site Quick-Capture 📱" button to:
    - Executive Portfolio (`/projects/portfolio`)
    - Schedule Timeline (`/projects/{project}/schedule`)
    - Lookahead Schedule (`/projects/{project}/lookahead`)
    - Constraints Register (`/projects/{project}/constraints`)
    - Executive Report (`/projects/{project}/report`)

- [ ] **Task 5: Verification & Styling**
  - Run Pint code style check.
  - Run full PHPUnit test suite to ensure 100% pass rate.
  - Update memory bank (`active_context.md`).
  - Commit changes to Git.
