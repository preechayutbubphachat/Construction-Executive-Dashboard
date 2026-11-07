---
name: plan
description: End-to-end engineering planning superpower. Use when asked to plan, create an implementation plan, design a multi-step feature, break down complex refactoring, architect changes, or structure execution steps before touching code.
---

# Plan Superpower (Engineering Implementation Planning)

## Overview

The **Plan Superpower** establishes a disciplined, rigorous engineering planning framework before writing any implementation code. An engineer plans not by writing vague todo lists, but by decomposing software into verifiable, bite-sized tasks that follow **Test-Driven Development (TDD)**, strict interface boundaries, and explicit verification criteria.

```
┌───────────────────────────────────────────────────────────────────────────┐
│                       The 5-Phase Planning Cycle                          │
├───────────────────────────────────────────────────────────────────────────┤
│ Phase 1: Scope & Invariant Checking (YAGNI, DRY, Breaking Changes)        │
│ Phase 2: Architectural Mapping (Files, Types, Signatures, Data Flow)      │
│ Phase 3: Bite-Sized TDD Task Decomposition (Red -> Green -> Refactor)     │
│ Phase 4: Risk, Edge Cases & Self-Review (Input Boundaries, Failure Modes) │
│ Phase 5: Execution Handoff (Native Session vs Subagent Orchestration)     │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## Core Principles

1. **No Code Before the Plan**: For multi-step tasks or architectural changes, write and verify the plan first.
2. **Bite-Sized & Independently Verifiable**: Every task must have its own test cycle and produce an independently reviewable, working increment.
3. **Exact Names & Signatures**: Do not leave names, types, or paths ambiguous. If a function is called `calculateScore()`, specify parameter types and return contracts in the plan.
4. **Assume an Unfamiliar Implementer**: Write the plan so clearly that an engineer unfamiliar with the codebase can execute each step with zero guesswork.
5. **YAGNI & DRY**: Eliminate speculative features. Build only what the requirements demand today.

---

## The Standard Implementation Plan Document Template

Save plans to: `docs/plans/YYYY-MM-DD-<feature-name>.md` or in the conversation artifact directory.

```markdown
# [Feature Name] Implementation Plan

> **Goal:** [One concise sentence describing what this builds or solves]
> **Architecture:** [2-3 sentences explaining the structural approach and data flow]
> **Tech Stack / Libraries:** [Key technologies, frameworks, and packages involved]
> **Execution Strategy:** [Native Session (executing-plans) or Subagent-Driven (subagent-driven-development)]

---

## 1. Global Constraints & Invariants
- [ ] Platform requirements (e.g., Node version, OS, browser compatibility)
- [ ] Dependency constraints (no unnecessary dependencies, lockfile hygiene)
- [ ] Architectural boundaries (e.g., presentation must not own domain state; read-only guarantees)

---

## 2. File & Component Topology

| Action | File Path | Responsibility |
| :--- | :--- | :--- |
| **Create** | `src/models/userSession.ts` | Session entity contract and validation schema |
| **Modify** | `src/controllers/auth.ts:45-80` | Route handler for login verification |
| **Test** | `tests/unit/userSession.test.ts` | Unit tests for session validation |

---

## 3. High-Risk Edge Cases & Failure Modes (Review Focus)
Identify the 3-5 inputs or edge cases most likely to break the system:
1. **Empty / Null Input**: Behavior when required fields are missing or undefined.
2. **Network / Socket Interruption**: Handling disconnects, timeouts, or reconnections.
3. **Concurrency / Race Conditions**: Simultaneous requests or sequence gaps.
4. **Boundary Numbers / Dates**: Zero, negative values, leap years, timezones.

---

## 4. Step-by-Step Task Breakdown

### Task 1: [Component / Entity Name]

**Files:**
- Create: `exact/path/to/file.ts`
- Test: `tests/exact/path/to/test.ts`

**Interfaces:**
- *Consumes*: None / Existing types
- *Produces*: `function validateSession(token: string): SessionResult`

- [ ] **Step 1: Write the failing test (RED)**
  ```typescript
  test("rejects expired token with EXPIRED_SESSION code", () => {
    const result = validateSession(expiredToken);
    expect(result.valid).toBe(false);
    expect(result.code).toBe("EXPIRED_SESSION");
  });
  ```

- [ ] **Step 2: Run test to verify failure**
  - Command: `npm test tests/unit/userSession.test.ts`
  - Expected Failure: `validateSession is not defined` or assertion failure.

- [ ] **Step 3: Implement minimal code to pass (GREEN)**
  - Implement `validateSession` in `src/models/userSession.ts`.

- [ ] **Step 4: Run test to verify success**
  - Command: `npm test tests/unit/userSession.test.ts`
  - Expected: PASS

- [ ] **Step 5: Refactor & Commit**
  - Clean up formatting and commit: `git commit -m "feat(auth): add session validation"`

---

### Task 2: [Next Component / Integration]
[Follow the exact same Red -> Green -> Refactor structure]
```

---

## Execution Modes & Handoff

Once the plan is drafted and reviewed, select the optimal execution method:

| Execution Mode | When to Use | Recommended Sub-Skill |
| :--- | :--- | :--- |
| **Native Execution** | Linear tasks, fast iterative changes, small-to-medium features | `executing-plans` |
| **Subagent-Driven** | Independent tasks, large multi-module projects, isolated work | `subagent-driven-development` |
| **Git Worktrees** | Experimental features needing isolated workspace branches | `using-git-worktrees` |

---

## Self-Review Checklist Before Presenting Plan

Before presenting the plan to your human partner, check:
1. [ ] **Spec Completeness**: Does every requirement from the user or spec map to at least one task?
2. [ ] **Step Precision**: Can an implementer execute each step without having to invent interface signatures?
3. [ ] **Verification Proof**: Does every task end with a concrete command that produces a verifiable output?
4. [ ] **No Guesswork / No Placeholders**: Are there any vague placeholders like "TBD", "add validation", or "handle errors"? (Replace them with exact rules!)
