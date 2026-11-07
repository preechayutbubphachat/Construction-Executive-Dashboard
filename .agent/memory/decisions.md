# Decision Log (ADRs)

## ADR-001: Local Trusted Core MVP Implementation (Phase 1)
- **Date:** 2026-11-06
- **Status:** ACCEPTED
- **Context:** Project required rapid operationalization of local construction executive visibility without touching staging/production environments.
- **Decision:** Implement M1-M7 in Laravel 11 with SQLite, Livewire 3, Alpine.js, and pure deterministic health calculation.
- **Consequences:** Solid foundation with 100% test coverage; external staging deferred until local MVP was verified.

## ADR-002: Lean Last Planner Make-Ready Lookahead Integration (Phase 2 แนวทาง B)
- **Date:** 2026-11-07
- **Status:** ACCEPTED
- **Context:** Construction projects need proactive scheduling to prevent work stoppage before field crews mobilize.
- **Decision:** Build a dynamic 3 to 6-week rolling lookahead schedule (`WeeklyLookaheadService`) cross-referencing leaf WBS tasks with `project_constraints` to calculate Make-Ready Rate % and sound vs constrained tasks.
- **Consequences:** Field teams and executives share an aligned view of work readiness.

## ADR-003: 2D Portfolio Risk Heatmap Matrix (Phase 2 แนวทาง D)
- **Date:** 2026-11-07
- **Status:** ACCEPTED
- **Context:** Executives managing multiple projects needed a high-level comparative tool to spot systemic risks across the portfolio.
- **Decision:** Implement `PortfolioRiskHeatmapService` computing a composite risk index (0-100) and mapping projects into 4 quadrants (Schedule Slippage vs Constraint/Cost Risk) at `/portfolio/heatmap`.
- **Consequences:** Instant executive triage capability with 1-click drill-downs into alerts, constraints, lookahead, and reports.
