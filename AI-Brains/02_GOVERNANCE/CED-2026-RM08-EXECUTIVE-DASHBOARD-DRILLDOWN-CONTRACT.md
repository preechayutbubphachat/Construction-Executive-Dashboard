# CED-2026 — RM-08 Executive Dashboard + Drill-down

```yaml
PROJECT_ID: CED-2026
PROJECT_NAME: Construction Executive Dashboard
ROADMAP_ITEM: RM-08
OWNER_AUTHORITY_ID: CED-OWNER-AUTH-RM08-CANONICAL-SCOPE-FREEZE-20260916-1014
STATUS: OWNER_FROZEN_ACTIVE
SCOPE: RM08_ONLY
```

## Mission

Provide an executive portfolio-and-project decision surface that reuses accepted domain truth from schedule/task, deterministic health, finance, private evidence, and audit; prioritizes projects needing attention; explains why; and provides authorized drill-down to the exact source records supporting each signal.

RM-08 is read-model composition, executive presentation, authorized navigation/drill-down, and explanation/traceability. It must not create new health, finance, evidence, accounting, schedule, or permission truth.

## Canonical dependencies and truth sources

- RM-05: accepted deterministic project health/read model, explanation, rule identity, calculated/refreshed time, and source provenance.
- RM-06: accepted CED_FINANCE_V1 income/expense output and calculation provenance.
- RM-07: private evidence, project-scoped authorization, controlled evidence access, and audit behavior.
- Project authorization: canonical project records and server-side membership/scope.
- Task/WBS/Schedule: existing canonical progress, dates, task, and schedule semantics.

The dashboard must preserve unknown, missing, and stale meanings; missing data must not become healthy by default. No new RED/AMBER/GREEN threshold, health formula, cost/delay/evidence threshold, score, forecasting engine, or parallel source of truth is authorized.

## Navigation contract

```text
Executive Portfolio
→ Project Executive Detail
→ Signal / Dimension / Explanation
→ Source Record
→ Authorized Evidence / Audit where applicable
```

Source drill-down classes are health reason to project/task/source scope; schedule/progress to task/WBS/project schedule; finance to contributing transaction(s); evidence to authorized evidence record; and audit/activity to audit record/source entity where those records exist.

## Canonical acceptance criteria

### AC-01 — Authorized portfolio overview

An authorized executive user can access an executive portfolio surface presenting all and only projects within permitted scope, with enough project identity information to navigate to executive detail. Authorized access succeeds, guest/unauthorized access follows current policy, server-side scope is enforced, and cross-project leakage is absent.

### AC-02 — Decision-first project prioritization

The portfolio presents project condition using accepted deterministic health truth and distinguishes attention, healthy, unknown, and completed states only as supported by canonical data. Displayed health comes from the accepted health layer, no duplicate formula exists, unknown/missing/stale is not silently healthy, and attention signals expose an explanation entry point. No new threshold is allowed.

### AC-03 — Explainable health signal

From project executive surface, the user can inspect why current health/status was produced, including available accepted rule/version identity, calculated/refreshed time, dimension/reason/explanation, and source scope/references. Presentation may reformat accepted data but may not replace it with guessed free text.

### AC-04 — Schedule / progress visibility and task drill-down

The executive project surface presents schedule/progress supported by existing Task/WBS/Schedule data and provides drill-down to relevant task/project schedule records. Dates/progress and any late/overdue/blocked facts are traceable to existing semantics, with no independent schedule calculation and no cross-project task access.

### AC-05 — Finance visibility with provenance

The executive project surface presents accepted RM-06 finance summary and drill-down to contributing transaction scope, preserving income/expense/net or accepted values, rule/version, calculated-at, contributing source-record scope, explanation, and provenance. Dashboard code must not recompute finance truth.

### AC-06 — Private evidence drill-down

Where a signal or source record has evidence, an authorized user can reach it through the RM-07 controlled path. Project-scoped authorization, no raw/public storage path exposure, outsider/cross-project denial, and applicable auditability remain effective.

### AC-07 — Audit / recent-activity traceability

The executive/project surface provides audit or recent-activity context sufficient to identify meaningful recent changes where current audit data supports it. Actor, time, action, and source entity remain traceable; activity is not fabricated and executive presentation is read-only.

### AC-08 — Source-linked explanation chain

At least one representative path is demonstrated end-to-end:

```text
portfolio/project signal
→ explanation/reason
→ source record
→ related authorized evidence/audit when applicable
```

The chain must use actual current records/test fixtures, not static demo text.

### AC-09 — Error / empty / unknown truthfulness

The executive surface handles absent/incomplete data without misleading success states, including no projects in scope, missing/unknown health inputs, no finance records, no linked evidence, and unavailable source records. None/unknown/not available must remain distinct from healthy/zero risk where semantics differ.

### AC-10 — No duplicate domain truth / regression preservation

RM-08 preserves RM-05, RM-06, and RM-07 behavior: no duplicate health or finance calculation, no weakened evidence authorization, and no audit mutation. Targeted RM-05/RM-06/RM-07 regressions and full local regression must pass.

### AC-11 — Executive decision path and security closure

A canonical end-to-end feature/security test demonstrates an authorized executive can open portfolio, select a project requiring attention or inspectable status, view its reason, and drill to the relevant underlying record; guest, outsider, and cross-project users cannot use the same path to access unauthorized project/source/evidence data.

## Allowed implementation scope

Only proven gaps may authorize changes in these categories:

- executive/portfolio controller;
- Livewire executive/portfolio component;
- executive Blade views/partials;
- read-only presentation DTO/view model or query composition;
- routes required for executive/drill-down navigation;
- small presentation helpers;
- RM-08 feature/security tests;
- accessibility/responsive presentation directly required by RM-08;
- small read-only adapter/service needed to compose accepted read models without duplicating formulas.

## Forbidden scope

No new health algorithm/threshold, finance formula/workflow, evidence lifecycle, audit semantics, authorization policy model, WBS/schedule engine, project status state machine, OCR, scanning workflow, AI risk scoring, forecasting engine, BIM, accounting integration, mobile/offline workflow, schema migration by default, staging, production, deployment, HostAtom/Plesk/DNS, or production database mutation.

If a schema change or new business rule is required, stop and return the exact missing data contract to Owner.

## Definition of done

All 11 criteria are individually proven by local technical verification, targeted/security and RM-05/RM-06/RM-07 regressions, full local regression, trusted frozen-workspace verification, zero subject mutation and zero post-extra files, coherent de-duplicated semantic evidence, actual model input at or below 140000 tokens, and one fresh independent THEMIS structured verdict. Acceptance requires VERIFIED_PASS or non-blocking VERIFIED_PASS_WITH_LIMITATIONS with no core criterion unproven.

## Baseline and boundaries

```yaml
PRODUCT_BASELINE_FREEZE: CED-2026-SUBJECT-FREEZE-023
RUNTIME_OUTPUT_BOUNDARY_POLICY: CED-2026-SUBJECT-BOUNDARY-POLICY-RUNTIME-OUTPUTS-V1
STAGING: false
PRODUCTION: false
DEPLOYMENT: false
```

This contract materializes Owner Sections 1–9 of the RM-08 canonical scope freeze and does not mark RM-08 started or complete.
