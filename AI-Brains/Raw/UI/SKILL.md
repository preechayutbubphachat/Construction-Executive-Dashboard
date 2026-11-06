---
name: ced-r3f-executive-dashboard
description: Design and implement a desktop-first 1920x1080 executive dashboard for CED-2026, using React Three Fiber for meaningful 3D project views without replacing canonical application architecture.
---

# CED-2026 — React Three Fiber Executive Dashboard Skill

## Scope and identity
- Project: `CED-2026`, Construction Executive Dashboard.
- Target: internal executive homepage, not a public marketing landing page.
- Follow the project's canonical North Star, verified Roadmap, repository state, authority and project identity lock. Never import decisions or evidence from other projects.
- Initial visual target: **1920 × 1080 desktop-first**, filling the available browser viewport. Responsive layouts are a subsequent design stage, but do not introduce intrinsic sizing that prevents them.
- This is a **skill specification**, not automatic authorization to install packages, refactor the frontend, push Git changes, or deploy.

## Core information architecture
1. Left persistent navigation: Overview, Projects, Schedule/Gantt, Finance, Evidence, Reports, Settings.
2. Top bar: screen title, project/period selectors, search, user/access state, data last-refreshed time.
3. First-priority decision panel: actionable risks with project, reason, owner, urgency and drill-down.
4. Portfolio health: active/at-risk/completed counts and trustworthy status categories.
5. Schedule: overdue tasks, blocked dependency, milestone variance and links to Gantt.
6. Finance: approved budget, committed, actual, forecast, variance and access-aware summaries.
7. Evidence: missing or pending review counts, source links and audit visibility.
8. Contextual 3D project viewport: selected project's spatial/progress context, clearly annotated as illustrative when no verified model exists.

Every KPI must offer its formula/definition, as-of date, scope and source-record path when connected to real data. Never fabricate project quantities, money amounts, status rules or evidence.

## 3D rules — pmndrs/react-three-fiber
- React Three Fiber is a React renderer for Three.js. Check the installed React major before selecting R3F: React 19 -> R3F v9; React 18 -> R3F v8. As of October 2026, v10 is alpha; do not select an alpha in production without explicit approval.
- Typical dependencies, subject to repository review: `react`, `react-dom`, `three`, `@react-three/fiber`, `@react-three/drei`, `@types/three` (for TypeScript).
- Use R3F for a **single contextual viewport** where depth communicates project structure, floor/stage selection or location of issues. Avoid putting KPI text, main tables, forms or all navigation into WebGL.
- Do not present a stylized conceptual model as certified BIM/IFC, structural engineering information, or as-built reality.
- Prefer orthographic/isometric overview; provide named camera views (Overview, Focus, Reset), selection state, object legend, selected floor and external DOM detail panel.
- Keep exact quantities and decisions in accessible DOM; use high-contrast semantic text labels for statuses.
- Pause rendering while hidden where practical; consider `frameloop="demand"`, capped device pixel ratio, lazy scene/assets, bounded model complexity and reduced-motion support.
- Provide meaningful 2D fallback when WebGL is disabled, unsupported or unavailable; do not conceal core data behind the scene.
- Avoid auto-playing motion that distracts from decisions. Scene interactions never mutate official project progress or financial state.

## Architectural boundary
- Existing CED planning baseline identifies Laravel/Blade/Livewire/Alpine/Tailwind with Vite and server-side permissions. **Verify actual repository and frozen architecture first.**
- Preferred integration to evaluate: mount a small React + R3F island into a Blade/Livewire area, with scoped JSON/read-model inputs and explicit bridge events.
- Do not migrate the whole frontend to React, replace Laravel, or add Next.js by implication.
- Server authorization remains authoritative; client filtering and WebGL visibility do not confer permission.
- Use versioned read-model data, separate estimated from actual values and preserve evidence traceability.

## Desktop-first layout guidance
- Responsive width: `width: 100%`, with viewport-height dashboard shell (`min-height: 100dvh`).
- Design reference: 1920x1080, not an unconditional CSS width of 1920px; account for browser chrome and zoom.
- Suggested initial layout tokens: sidebar 240px; header 76px; main inner gap 18px; main surface padding 24px.
- Prioritize actionable exceptions above decorative 3D. Scene can occupy approximately 50–60% of the central content panel, with adjacent decision queue and lower project/finance data.
- Keep Thai UI typography legible at 100% zoom; critical values must not truncate or overlap.

## Design → implementation → verification workflow
1. ATHENA designs one complete desktop homepage, tokens, component inventory, interaction states, 3D scene data contract and 2D fallback.
2. RIN resolves current Roadmap/authority, authorizes bounded implementation, and forbids unnecessary stack changes.
3. HERMES implements a separate candidate branch or workspace according to actual project rules. No live changes without explicit authority.
4. THEMIS independently checks layout, semantics, performance, data permissions, fallbacks and regression.
5. MIRA records the accepted design, decisions, evidence and resume point in the CED Project Brain.

## Acceptance checklist
- [ ] Screenshot at 1920x1080: main overview fills viewport with no unwanted horizontal scrolling.
- [ ] At 100% browser zoom: Thai labels, numbers, nav, decision alerts and action buttons remain legible.
- [ ] Decision-required signals visible before needing to scroll.
- [ ] Project/status and finance mock data are conspicuously labeled `DEMO` unless verified.
- [ ] 3D scene can select a floor/stage and updates the adjacent DOM inspector.
- [ ] Core KPIs remain usable with WebGL off.
- [ ] Keyboard focus and status labels do not rely on color alone.
- [ ] Reduced-motion and tab-hidden behavior verified.
- [ ] Access control checked at backend; 3D scene receives only authorized data.
- [ ] At smaller widths, content degrades safely; dedicated responsive acceptance comes in phase two.
- [ ] No production deployment, source mutation or dependency installation beyond actual authority.

## References
- https://github.com/pmndrs/react-three-fiber
- https://r3f.docs.pmnd.rs/getting-started/installation
- https://github.com/pmndrs/drei
- CED-2026 project `NORTH_STAR.md`
