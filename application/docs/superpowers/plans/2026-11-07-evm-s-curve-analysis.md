# Phase 4 — เสาหลัก 2: Earned Value Management (EVM) & S-Curve Forecasting Implementation Plan

> **Goal:** Build an engineering-grade Earned Value Management (EVM) & interactive S-Curve engine at `/projects/{project}/evm` delivering international standard construction metrics (PV, EV, AC, CV, SV, CPI, SPI, EAC, VAC, TCPI) and weekly time-series S-Curve forecasting.
> **Architecture:** Pure deterministic domain projection service (`EarnedValueManagementService` `CED_EVM_SCURVE_V1`) integrating `ProgressCalculator` and `CedFinanceV1ReadModel` with a responsive Livewire 3 / Alpine.js SVG S-Curve visualizer.
> **Tech Stack:** Laravel 11, Livewire 3, Alpine.js, Tailwind CSS, SVG Charting, PHPUnit.
> **Execution Strategy:** Test-Driven Development (TDD) for financial/variance mathematical formulas, strict project authorization, and cross-executive integration.

---

## 1. Global Constraints & Invariants
1. **Financial Precision:** All EVM money calculations must maintain exact 2-decimal point precision.
2. **Deterministic & Pure:** Zero database mutations during calculation.
3. **Authorization Default-Deny:** `Gate::authorize('view', $project)` strictly enforced on web page and API routes.
4. **Division-by-Zero Safety:** All indices (CPI, SPI, TCPI, EAC) must gracefully handle zero denominators (e.g. $AC = 0$, $PV = 0$, or $BAC = AC$).

---

## 2. Architectural Blueprint & File Map

```
application/
├── app/
│   ├── Livewire/
│   │   └── ProjectEvmSCurveView.php                    # Livewire EVM & S-Curve Surface
│   └── Services/
│       └── EarnedValueManagementService.php            # Pure EVM & S-Curve calculation engine
├── resources/
│   └── views/
│       ├── livewire/
│       │   └── project-evm-s-curve-view.blade.php      # S-Curve SVG Chart, KPI Cards, Health gauges
│       └── projects/
│           └── evm.blade.php                           # Standalone page layout
├── tests/
│   ├── Unit/
│   │   └── EarnedValueManagementServiceTest.php        # Unit tests for EVM formulas & S-curve series
│   └── Feature/
│       └── ProjectEvmSurfaceTest.php                   # Feature tests for authorization & UI rendering
└── routes/
    └── web.php                                         # Route /projects/{project}/evm
```

---

## 3. Bite-Sized Implementation Tasks

- [ ] **Task 1: EVM Service & Mathematical Formulas (TDD)**
  - Write `tests/Unit/EarnedValueManagementServiceTest.php`:
    - Test BAC, PV, EV, AC calculation.
    - Test Cost Variance (CV) and Schedule Variance (SV).
    - Test Performance Indices: CPI, SPI, TCPI.
    - Test Forecasts: EAC, VAC, ETC, Estimated Completion Date.
    - Test Weekly S-Curve Time Series projection.
  - Implement `App\Services\EarnedValueManagementService` (`CED_EVM_SCURVE_V1`).
  - Verify unit tests pass.

- [ ] **Task 2: Livewire EVM Surface & Blade View**
  - Implement `App\Livewire\ProjectEvmSCurveView`.
  - Create `resources/views/livewire/project-evm-s-curve-view.blade.php`:
    - KPI grid (BAC, Planned Value, Earned Value, Actual Cost).
    - Performance Index Health Gauges (CPI dial, SPI dial with color tiers: Green $\ge 1.0$, Amber $0.9-0.99$, Red $< 0.9$).
    - Forecast At Completion Cards (EAC, Over/Under Budget VAC, Required TCPI).
    - Interactive SVG S-Curve Chart (Planned S-Curve, Actual EV curve, Actual Cost curve, Forecast path).
  - Create `resources/views/projects/evm.blade.php`.
  - Register `/projects/{project}/evm` in `routes/web.php`.
  - Write `tests/Feature/ProjectEvmSurfaceTest.php`.
  - Verify feature tests pass.

- [ ] **Task 3: Cross-View Integration & Navigation**
  - Add "EVM & S-Curve 📈" navigation button & breadcrumb links across:
    - Executive Portfolio (`/projects/portfolio`)
    - Schedule Timeline (`/projects/{project}/schedule`)
    - Lookahead (`/projects/{project}/lookahead`)
    - Constraints (`/projects/{project}/constraints`)
    - 3D Twin (`/projects/{project}/3d-twin`)
    - Executive Report (`/projects/{project}/report`)

- [ ] **Task 4: Verification, Linting & Memory Bank**
  - Run Laravel Pint.
  - Run entire PHPUnit test suite.
  - Update memory bank (`active_context.md`).
  - Commit and push to Git.
