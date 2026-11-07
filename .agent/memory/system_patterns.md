# System Patterns & Architecture

## Core Topology
- Framework: Laravel 11.x (PHP 8.2+) with Livewire 3.x and Alpine.js
- Styling: Tailwind CSS (Modern Executive Construction Palette: Slate, Amber, Emerald, Rose)
- Database: Eloquent ORM (SQLite in test/local; MariaDB/MySQL in staging/production)
- Primary Entities: `Project`, `WbsTask`, `CashFlow`, `AuditLog`, `ProjectConstraint`
- Key Services:
  - `CedHealthV100Projection`: Deterministic Executive Health calculation
  - `ConstraintManagement`: Site constraints & issue tracking with audit trail
  - `WeeklyLookaheadService`: 3-6 week rolling lookahead with make-ready soundness analysis
  - `EarlyWarningAlertEngine`: Proactive risk detection across 4 categories
  - `PortfolioRiskHeatmapService`: 2D multi-project comparative risk quadrant matrix

## Verification & Test Commands
- Test Runner:
  ```powershell
  $env:TEMP="c:\2025\web-69\05-Construction-Executive-Dashboard\application\tests\.tmp"
  $env:TMP="c:\2025\web-69\05-Construction-Executive-Dashboard\application\tests\.tmp"
  cd application
  php vendor/phpunit/phpunit/phpunit
  ```
- Code Style Check:
  ```powershell
  cd application
  php vendor/bin/pint --test <files>
  ```

## Critical Invariants
1. Default-Deny Authorization: All project-level routes and Livewire components must enforce `Gate::authorize('view', $project)` or equivalent check. Unauthenticated web routes return HTTP 403.
2. Pure Read Projections: Projection services (Health, Lookahead, Alerts, Heatmap) must be pure and deterministic (Zero mutations during calculation).
3. Financial Maker != Approver: A user cannot approve their own financial cash-flow items.
4. Windows Sandbox Isolation: Temporary file writes (e.g. PhpSpreadsheet) must always point to `tests/.tmp`. Newly created files must be granted ACL permissions for the sandbox app container token via `icacls`.
