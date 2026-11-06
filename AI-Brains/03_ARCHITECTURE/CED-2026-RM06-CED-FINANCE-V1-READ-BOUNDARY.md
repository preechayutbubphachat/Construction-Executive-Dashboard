# CED-2026 — CED_FINANCE_V1 Read-only Finance Boundary

```yaml
BOUNDARY_ID: CED_FINANCE_V1_READ_BOUNDARY_V1
PROJECT_ID: CED-2026
DEPENDENCY: RM-06
OWNER_AUTHORITY_ID: CED-OWNER-AUTH-RM08-RM06-FINANCE-READMODEL-BOUNDARY-20260916-1126
IMPLEMENTATION: App\\Services\\CedFinanceV1ReadModel
CALLABLE: forProject(Project $project): array
CANONICAL_RULE: CED_FINANCE_V1
MUTATION: none
```

## Purpose

Expose the already accepted RM-06 `CED_FINANCE_V1` result through one public, read-only application boundary for RM-06 and RM-08 consumers. This document is a boundary contract, not a new finance calculation or workflow definition.

## Output contract

`App\\Services\\CedFinanceV1ReadModel::forProject(Project $project): array` returns:

- `project_id`
- `rule_version`
- `calculated_at`
- `income`
- `expense`
- `net`
- `contributing_source_record_scope`
- `explanation`

The source scope preserves accepted RM-06 provenance: the supplied project identity, posted status, and deterministically sorted contributing transaction IDs. Amounts and net semantics remain the accepted persisted `DECIMAL(15,2)` / `decimal:2` representation and `CED_FINANCE_V1` behavior.

## Authorization and scope

Public callers must pass the existing server-side project/finance authorization boundary. The service query is always scoped through the supplied `Project` relationship and validates each returned transaction's project identity. Guest, outsider, and cross-project access remain denied by existing policy and caller gates. No role, permission, policy model, or authorization rule is added.

## Read-only invariant

The service performs no transaction creation or update, workflow transition, audit write, project mutation, schema mutation, or database state mutation. A read produces no audit event merely because it was read.

## Consumer invariant

`FinanceTransactionWorkflow` delegates its posted totals to this service and contains no independent finance aggregation. RM-08 consumers must delegate to this boundary and must not aggregate `income`, `expense`, or `net` directly, in Blade, JavaScript, or a second service.

## Verification binding

Independent THEMIS verification: `VERIFIED_PASS`, 12 pass, 0 unproven, 0 fail.

- Bundle SHA-256: `3d86fcab1501bc7378a52032ec09ebbc35c61e366207d2622e816b67e27f82d1`
- Model-input manifest SHA-256: `7f2d3df32439dbe0677b4861ccefee96a59090b6808e20f4948c4d5557f6ea24`
- Service SHA-256: `f565533dd091ceb5dd8678df9d6db1a10136b6495267d2e9f26255f9190248b2`
- Workflow SHA-256: `a353521dc917c42b2336352542b78a4b399925e290a183c244acdd494d5d5ac7`
- Boundary test SHA-256: `932f0881eaa0daad4def73eca69a571d97ea62e59bdbbaf90b062c9878ab6758`

Technical evidence: targeted `11 tests / 66 assertions / exit 0`; preservation `24 tests / 148 assertions / exit 0`; full local `45 tests / 328 assertions / exit 0`. Runtime cache and compiled Blade files remain classified as ephemeral outputs outside the product allowlist.
