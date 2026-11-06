# CED-2026 — Architecture Baseline

PROJECT_ID: CED-2026
PROJECT_NAME: Construction Executive Dashboard
CHECKPOINT_TASK_ID: TASK-20260910-CED-MIRA-BRAIN-CHECKPOINT-002
PREDECESSOR_TASK_ID: TASK-20260910-CED-MIRA-BRAIN-CHECKPOINT-001
SOURCE_AUTHORITY: Owner decision packet + RIN adjudication + Themis receipt + current CED Project Brain + North Star

## Decision state

DECIDED: Architecture baseline ready.
ARCHITECTURE_TERMINAL: CED_ARCHITECTURE_BASELINE_READY
THEMIS_VERDICT: VERIFIED_ARCHITECTURE_ACCEPTABLE
THEMIS_DESIGN_LEVEL_BLOCKERS: NONE
OWNER_DECISION_PACKET: ACCEPTED
LOCAL_IMPLEMENTATION: AUTHORIZED_AFTER_CHECKPOINT

## Accepted baseline

- Style: Modular Monolith
- Framework: Laravel 13
- PHP target: >= 8.3
- UI: Blade + Livewire + Alpine.js + Tailwind CSS
- Database: MySQL/MariaDB portable relational design via mysql driver
- Canonical source: application database after validated workflow acceptance
- Excel: import-only; preview, mapping, duplicate/error validation and explicit approved commit required
- Queue: database queue for MVP
- Scheduler: Laravel scheduler via Plesk Scheduled Tasks only when availability is confirmed
- Evidence: private-by-default, controlled download, metadata/hash/access class/review state and audit
- Gantt: Frappe Gantt, MIT license, presentation adapter; no vendor-specific domain truth
- Health: deterministic, versioned calculation engine
- Audit: append-only audit design for material changes and sensitive access
- Authorization policy: deny by default; project-scoped, server-side project/action checks
- Financial separation of duties: maker != approver (`created_by != approved_by`)

## Versioned product rules

- `CED_PROGRESS_V1`: actual progress rule
- `CED_PLANNED_PROGRESS_LINEAR_V1`: planned progress by linear baseline interpolation
- `CED_FINANCE_V1`: income/expense and financial-state semantics
- `CED_HEALTH_V1_0_0`: deterministic health/risk rule with explanation and source lineage

Calculation outputs must retain rule version, calculated-at time, source record scope and explanation. No AI/LLM is canonical truth.

## Boundary

Architecture Ready != Staging Ready != Production Ready

HOSTATOM_ACCOUNT_SNAPSHOT: PENDING_PRE_STAGING
STAGING: FORBIDDEN; blocked pending HostAtom account snapshot, PHP/DB/runtime compatibility, document root, scheduled tasks and backup/restore evidence
PRODUCTION: FORBIDDEN; blocked pending runtime security, backup/restore and explicit owner deploy authority

ยังห้ามแก้ HostAtom/Plesk/DNS/database, deploy, ใช้ credentials หรือเปิด evidence เป็น public asset ใน checkpoint นี้

## North Star

> Executive opens the system and immediately understands which project is in trouble, why, and how schedule/money/evidence support that conclusion.

## Durable source references

- Contract: `C:/2025/AI-GOVERNOR/AI-Brains/10_AGENTS/RIN-V2/evidence/CED-2026/contracts/TASK-20260910-CED-MIRA-BRAIN-CHECKPOINT-002.json`
  RAW_SHA256: `55f38ecba2db97b11c29c75c57331096572fe2077e890e15851397179e307cf1`
- Owner directive: `C:/Users/preec/AppData/Local/hermes/profiles/rin-v2/cache/documents/doc_7bce40bf37e0_CED_2026_RIN_MIRA_CAPABILITY_CORRECT_CHECKPOINT_AND_PRODUCT_UNBLOCK.md`
  RAW_SHA256: `bd65b649efdbbef2cae26ed99ddbd62907d61122fdf58a4a9a8de89e9c140511`
- Baseline receipt: `C:/2025/AI-GOVERNOR/AI-Brains/10_AGENTS/RIN-V2/evidence/CED-2026/CED-ARCHITECTURE-BASELINE-RECEIPT-20260910T155500+0700.json`
  RAW_SHA256: `3b2a0d51800709379261119dc7587c20cd899efb193cd157e51c4227857068f3`
- North Star: `C:/2025/web-69/05-Construction-Executive-Dashboard/AI-Brains/01_VISION/NORTH_STAR.md`
  RAW_SHA256: `55d71e8ffef16e3e8ba72beace7a8bfc6e4648bd0e6d17161308c0387adc5c33`

หากพบ conflict ระหว่าง observed/decided/verified state ให้หยุดและ route ไปยัง Rin ไม่แก้ authority เอง
