---
project: Construction Executive Dashboard
type: proposed-domain-model
status: PROPOSAL_NOT_IMPLEMENTATION
updated: 2026-09-10T10:22:51+07:00
last_verified: 2026-09-10T10:22:51+07:00
confidence: INFERRED
---

# Domain model และ MVP proposal

> เอกสารนี้เป็นข้อเสนอจากหลักฐาน Raw + งานวิจัย ไม่ใช่การอนุมัติ architecture หรือการแก้ source code

## 1. Context ที่ยืนยันได้

- Workbook ปัจจุบันมี 14 ชีตและมีทั้งข้อมูลโครงการ งานย่อย บัญชี สรุป และ Gantt
- มีภาพหลักฐานวัสดุและค่าแรงที่ต้องเชื่อมกับรายการบัญชี
- รูปแบบข้อมูลปัจจุบันใช้ชื่อโครงการเป็นตัวเชื่อมหลักหลายจุด ซึ่งเสี่ยงต่อการสะกดไม่ตรง/เปลี่ยนชื่อแล้ว link ขาด
- มีสูตรจำนวนมากใน workbook; ผลลัพธ์สูตรยังไม่ได้รับการคำนวณใหม่ใน ingestion pass

## 2. Bounded domain model (ข้อเสนอ)

### Core entities

- `Project`: id, code, name, type, client/site, status, planned dates, contract days, owner
- `ProjectPhase`: id, project_id, code, name, sequence, baseline dates
- `Task`: id, project_id, phase_id, parent_id, title, owner, planned/actual dates, progress, status
- `TaskDependency`: predecessor_id, successor_id, relation_type, lag
- `Constraint`: id, task_id, type, description, owner, due_at, resolved_at, status
- `Transaction`: id, project_id nullable, direction, category_id, amount, occurred_at, vendor/payee, payment_status, source_batch_id
- `EvidenceDocument`: id, storage_key, mime, sha256, size, captured_at, document_type, access_class
- `TransactionEvidence`: transaction_id, evidence_id, relation_type
- `Issue`: id, project_id, task_id nullable, severity, status, owner, due_at, resolution
- `Approval`: id, resource_type, resource_id, step, actor, decision, decided_at, comment
- `AuditEvent`: immutable event with actor, action, resource, before/after summary, timestamp, request_id

### Derived/read models

- `ProjectHealthSnapshot`
- `ProjectCostSummary`
- `MonthlyCashSummary`
- `YearlyPortfolioSummary`
- `GanttReadModel`
- `EvidenceReviewQueue`

หลักการ: summary/read model คำนวณใหม่ได้จาก write model + approved evidence; ห้ามให้ dashboard cell เป็นแหล่งความจริงแยกต่างหาก

## 3. Mapping จาก workbook เดิม

| Workbook | Target domain | หมายเหตุ |
|---|---|---|
| `1-ชื่อโครงการ` | Project | แยก project ID ออกจากชื่อแสดงผล |
| `รายละเอียดงาน` | ProjectPhase + Task | รักษา planned/actual/progress/status; เพิ่ม dependency/constraint |
| `2-บันทึกรายรับรายจ่าย` | Transaction + EvidenceDocument | เปลี่ยน evidence link ให้เป็น relation ที่ตรวจสิทธิ์ได้ |
| `3-สรุปตามโครงการ` | ProjectCostSummary | read model จาก transaction ที่ approved |
| `4-สรุปรายเดือน` | MonthlyCashSummary | ระบุ timezone/period และสูตรอย่างชัดเจน |
| `5-สรุปรายปี` | YearlyPortfolioSummary | แยก fiscal year หากธุรกิจต้องใช้ |
| `GANTTChart` | GanttReadModel | ต้องมี dependency และ baseline ไม่ใช่เพียง bar สี |
| `ตั้งค่า` | reference data | version หมวดหมู่และสถานะ; ห้ามเปลี่ยนย้อนหลังแบบทับค่า |
| `dataBaseTodo` | ProjectHealthSnapshot/Task query | ไม่ควรเป็น duplicate source of truth |
| `ชีต16`, `ชีต17` | import staging | ต้องตรวจ lineage และ duplicate ก่อนย้ายเข้าระบบหลัก |

## 4. State model ที่ควรแยกกัน

### Project status

`PROPOSED → READY → ACTIVE → INSPECTION → CORRECTION → COMPLETED` และ `CANCELLED` เป็น terminal ตาม policy ที่เจ้าของยืนยัน

### Task status

`NOT_STARTED → READY → IN_PROGRESS → BLOCKED → DONE` โดย `BLOCKED` ต้องมี Constraint/Issue อ้างอิง ไม่ใช่ข้อความอย่างเดียว

### Transaction status

`DRAFT → PENDING_REVIEW → APPROVED → POSTED` และ `REJECTED/VOIDED` ต้องเก็บเหตุผลและ audit event

### Evidence status

`UPLOADED → SCANNING → EXTRACTED_CANDIDATE → HUMAN_REVIEW → ACCEPTED/REJECTED`

สถานะเหล่านี้เป็นข้อเสนอ `INFERRED`; ต้องให้เจ้าของยืนยันก่อน implement

## 5. Data-quality rules ที่ต้องมีตั้งแต่ import

- project reference ต้องเป็น immutable ID; ชื่อเป็น display field
- amount ต้องเป็น decimal ไม่ใช่ floating-point display string
- income/expense direction ต้องไม่อาศัยเครื่องหมายในชื่อหมวดหมู่เพียงอย่างเดียว
- transaction ที่ project ว่างต้องเข้าคิว `UNALLOCATED_REVIEW` ไม่ใช่กระจายเข้าค่าใช้จ่ายทั่วไปเงียบ ๆ
- วันที่ไทย/พุทธศักราชต้อง normalize ด้วย explicit calendar policy และเก็บ raw display value หากจำเป็น
- สูตร Excel ไม่ใช่หลักฐานยอดล่าสุดจนกว่าจะอ่าน cached value/คำนวณด้วย engine ที่ยืนยันได้
- duplicate evidence ต้องตรวจ SHA-256 และ source reference
- OCR amount/date ต้องมี confidence และ human approval
- เปลี่ยนหมวดหมู่ต้อง version ไม่ rewrite ประวัติเดิม

## 6. หน้าจอ MVP

1. **Executive home** — portfolio health, overdue, cash, unresolved blockers; ทุก card drill-down ได้
2. **Projects** — filter status/type/owner/site และ project health
3. **Project detail** — summary, tasks, Gantt, issues/constraints, transactions, evidence
4. **Task board/lookahead** — ready/blocked/this week/next week และ commitment
5. **Finance** — ledger, filters, project allocation, approval queue, evidence
6. **Evidence review** — preview, metadata, OCR candidate, accept/reject, access log
7. **Reports** — project/month/year with formula/version/refresh timestamp
8. **Admin** — roles, reference data, import batches, audit viewer

## 7. Acceptance gates ก่อน implementation

- [ ] เจ้าของยืนยัน role matrix และ project-level authorization
- [ ] เจ้าของยืนยัน canonical source เมื่อ Excel/ภาพ/ระบบใหม่ขัดกัน
- [ ] เจ้าของยืนยันสูตร `% progress`, remaining, profit/cash และรอบบัญชี
- [ ] เจ้าของยืนยันสถานะและ transition ของ Project/Task/Transaction/Evidence
- [ ] มี sample dataset ที่ลบ/ปกปิด PII สำหรับ test
- [ ] มี import dry-run ที่แสดง errors โดยไม่เขียน production tables
- [ ] มี security design ตาม ASVS + upload controls + negative authorization tests
- [ ] มี retention/backup/export policy สำหรับภาพหลักฐานและบัญชี

## 8. ไม่ควรทำใน MVP

- ไม่ฝัง vendor-specific workflow เป็น domain truth
- ไม่ใช้ OCR เขียนบัญชีอัตโนมัติโดยไม่ review
- ไม่ทำ BIM/IFC ก่อนยืนยัน use case และ file ownership
- ไม่คัดลอก raw financial values ลงใน public knowledge หรือ demo fixture
- ไม่สรุปว่าระบบ production-ready จากการมี schema/documentation เพียงอย่างเดียว
