---
name: remember
description: Persistent episodic and semantic memory management inspired by claude-mem. Use when instructed to remember, recall, store knowledge across sessions, track architectural decisions (ADRs), save lessons learned, or prevent context loss across resets.
---

# Remember Superpower (claude-mem Persistent Memory)

## Overview

The **Remember Superpower** solves the AI "forgetting problem" across sessions, context resets, and project handoffs. Inspired by **`claude-mem`**, it maintains a persistent, structured, and auditable memory bank that records:
1. **Active Context & State**: What was recently built, running servers, ports, and next immediate goals.
2. **System Patterns**: Discovered architectural patterns, directory structures, API routes, and conventions.
3. **Architectural Decisions (ADRs)**: Why key choices were made, alternatives rejected, and tradeoffs accepted.
4. **Learnings & Gotchas**: Lessons from bugs, test failures, user feedback, and edge cases discovered during development.

```
┌───────────────────────────────────────────────────────────────────────────┐
│                    The claude-mem Memory Architecture                     │
├───────────────────────────────────────────────────────────────────────────┤
│  1. Active Context       (.agent/memory/active_context.md)                │
│     - Current sprint focus, active servers/ports, pending tasks           │
│                                                                           │
│  2. System Patterns      (.agent/memory/system_patterns.md)               │
│     - Architecture topology, key contracts, state flows, test runners     │
│                                                                           │
│  3. Decision Log (ADRs)  (.agent/memory/decisions.md)                     │
│     - Why decisions were made, rejected options, technical debt           │
│                                                                           │
│  4. Learnings & Gotchas  (.agent/memory/learnings.md)                     │
│     - Bug fixes, fragile patterns, user preferences, pitfalls to avoid    │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## Storage Locations

### 1. Project Memory Bank (Versioned with Project)
Stored inside the repository at `.agent/memory/` (or `.agents/memory/`):
- `.agent/memory/active_context.md`
- `.agent/memory/system_patterns.md`
- `.agent/memory/decisions.md`
- `.agent/memory/learnings.md`

### 2. Global Agent Memory (Cross-Project User Preferences)
Stored at `~/.gemini/config/memory/` (or `C:/Users/<User>/.gemini/config/memory/`):
- `user_preferences.md`: Personal coding style, language preferences, preferred frameworks, OS conventions.
- `global_tooling.md`: Tool shortcuts, local environment notes, system-wide ports.

---

## Core Operations

### 1. `remember` (Capture & Consolidate)
Triggered when:
- The user gives an explicit directive or correction ("Always use...", "Never do...", "Remember that...").
- An architectural decision is finalized.
- A tricky bug is diagnosed and solved.
- A new server stack or port convention is configured.

**Action**: Append or update the relevant section in `.agent/memory/` immediately.

### 2. `recall` (Context Rehydration)
Triggered when:
- Starting a fresh conversation turn or after a context truncation/reset.
- Switching between modules or revisiting an area worked on previously.
- Before answering architectural or setup questions.

**Action**: Read `.agent/memory/` files to restore full context before proposing changes or asking questions.

### 3. `checkpoint` (Context Compression & Session Close)
Triggered when:
- The user types `/compact` or ends a major feature milestone.
- Background tasks change state.

**Action**: Summarize current progress into `active_context.md` and link open tasks so the next session can resume seamlessly with zero warm-up time.

### 4. `forget` / `deprecate` (Memory Hygiene)
Triggered when:
- Old architectural assumptions or temporary workarounds become obsolete.

**Action**: Mark the item as `[SUPERSEDED: YYYY-MM-DD]` in `decisions.md` or remove it from `active_context.md`. Never silently delete historical ADRs; keep them as deprecated lessons.

---

## Memory Bank File Formats

### 1. `active_context.md`
```markdown
# Active Context

Last Updated: YYYY-MM-DDTHH:mm:ssZ

## Current Objective
[Short description of the current feature or refactor]

## Active Server Stack & Daemons
- Port XXXX: Service A (e.g. Fastify API)
- Port YYYY: Service B (e.g. Vite Dev Server)
- Background Task ID: [taskId or None]

## Recent Milestones
- [x] Completed item 1
- [x] Completed item 2

## Next Steps
- [ ] Immediate next task
- [ ] Follow-up verification
```

### 2. `system_patterns.md`
```markdown
# System Patterns & Architecture

## Core Topology
- Frontend: [Framework, routing mechanism, styling framework]
- Backend: [Framework, database connectors, authentication]
- State Management: [Store library or reactive state structure]

## Verification & Test Commands
- Unit Tests: `npm test`
- Build Verification: `npm run build`
- Type Check: `npm run typecheck`

## Critical Invariants
- 1. Zero layout-shift across views.
- 2. Strict separation of presentation and domain logic.
```

### 3. `decisions.md` (ADR Format)
```markdown
# Architecture Decision Records (ADR)

## ADR-001: [Title of Decision]
- **Date:** YYYY-MM-DD
- **Status:** Accepted | Superseded
- **Context:** [Problem statement or trade-off faced]
- **Decision:** [Chosen solution and rationale]
- **Alternatives Considered:** [Alternative A, Alternative B and why they were rejected]
- **Consequences:** [Benefits gained and technical debt or constraints accepted]
```

### 4. `learnings.md`
```markdown
# Learnings & Gotchas

## [Domain or Bug Name] (YYYY-MM-DD)
- **Symptom:** [What failed or produced unexpected behavior]
- **Root Cause:** [Underlying mechanism causing the issue]
- **Solution:** [How it was solved]
- **Rule for the Future:** [Exact invariant or test to enforce going forward]
```

---

## Privacy & Safety Boundaries
- **NEVER** write plaintext passwords, private keys, API secrets, or OAuth tokens into memory files.
- Store secrets in `.env` or system environment variables, referencing only the variable names (e.g., `$DB_PASSWORD`).
- Use `<private>...</private>` tags or omit confidential business data from memory summaries.
