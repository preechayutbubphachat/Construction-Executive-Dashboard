---
project: Construction Executive Dashboard
type: north-star
status: OWNER_PROPOSED_NORTH_STAR_PENDING_FREEZE
project_id: CED-2026
updated: 2026-09-10T10:58:08+07:00
last_verified: 2026-09-10T10:58:08+07:00
confidence: OWNER_GOAL_PLUS_VERIFIED_RESEARCH
authority: OWNER_PROPOSAL_NOT_IMPLEMENTATION_AUTHORIZATION
---

# Construction Executive Dashboard — North Star

> เอกสารวิสัยทัศน์และทิศทางสูงสุดของโปรเจคสำหรับใช้สร้าง Project Brain, Product Specification และ Master Roadmap
>
> **สถานะเอกสาร:** `OWNER_PROPOSED_NORTH_STAR_PENDING_FREEZE`
>
> ข้อความในเอกสารนี้แบ่งเป็น 3 ชั้นอย่างชัดเจน:
>
> - **OWNER-PROVIDED:** เป้าหมาย ขอบเขต และทิศทางที่เจ้าของให้มาในคำสั่งนี้
> - **VERIFIED:** หลักฐานที่ตรวจพบจาก Raw corpus และงานวิจัยที่มี provenance
> - **INFERRED / PROPOSED:** ข้อเสนอด้าน product, domain model, architecture, KPI และ roadmap ที่ยังต้องผ่านการยืนยันและ freeze

---

## 0. บทบาทของ North Star

North Star ไม่ใช่ README, ไม่ใช่รายการ task และไม่ใช่แผน implementation รายละเอียด แต่เป็นเอกสารที่ตอบคำถามว่า:

1. โปรเจคนี้มีไว้เพื่อแก้ปัญหาอะไร
2. ผู้ใช้กลุ่มใดควรได้คุณค่าอะไร
3. ระบบควรทำให้การตัดสินใจแบบใดดีขึ้น
4. ข้อมูลและหลักฐานต้องไหลเชื่อมกันอย่างไร
5. อะไรอยู่ในขอบเขต และอะไรยังไม่ควรทำ
6. เมื่อมีทางเลือกหลายแบบ เราจะเลือกทางที่พาเข้าใกล้ Goal อย่างไร
7. เงื่อนไขใดต้องหยุดและรอ Owner Gate

North Star ฉบับนี้จึงเป็นเข็มทิศสำหรับ Product, Architecture, Implementation, Verification และ Project Brain แต่ไม่ยกเลิกอำนาจของ repository rules, authoritative Roadmap, tests, runtime evidence หรือ Owner approval.

### ลำดับอำนาจ

1. คำสั่ง/การตัดสินใจล่าสุดของ Owner ที่ระบุขอบเขตชัดเจน
2. Project Definition และ North Star ที่ Owner freeze แล้ว
3. Master Roadmap ที่ผ่าน governance gate
4. Repository contract, architecture baseline และ acceptance criteria ที่อนุมัติแล้ว
5. Verified source records, tests และ runtime evidence
6. Research และข้อเสนอเชิงออกแบบ
7. Chat memory, ตัวอย่าง UI, ชื่อไฟล์ และสมมติฐาน

ในรอบนี้ North Star เป็น **Owner proposal ที่พร้อมนำไป review/freeze** ยังไม่ใช่ implementation authorization.

---

# 1. Project Identity

## 1.1 ชื่อโปรเจค

**PROJECT NAME:** `Construction Executive Dashboard`

## 1.2 Project ID

**PROJECT_ID:** `CED-2026`

Project ID นี้เป็นค่าที่ Owner เสนอให้ใช้เป็น identity หลักของโปรเจค ต้องไม่เปลี่ยนตามชื่อโครงการก่อสร้างรายย่อย ชื่อลูกค้า หรือชื่อโฟลเดอร์

## 1.3 แนวคิดหลัก

**ระบบบริหารงานก่อสร้างหลายโครงการสำหรับผู้บริหารและทีมปฏิบัติงาน**

## 1.4 Product category

- Multi-project construction management platform
- Executive project-control dashboard
- Construction operations and evidence management
- Project financial visibility system
- Schedule, cost, issue และ decision-support workspace

## 1.5 One-sentence identity

> ศูนย์กลางข้อมูลที่ทำให้ผู้บริหารและทีมงานเห็นสถานะจริงของทุกโครงการก่อสร้าง ตั้งแต่งานตามแผน งานที่ติดขัด ต้นทุน รายรับรายจ่าย ไปจนถึงหลักฐานที่ใช้ยืนยันการตัดสินใจได้จากจุดเดียว

---

# 2. Owner Goal

## 2.1 Owner Goal ฉบับเต็ม

> สร้างเว็บแอปสำหรับบริหารโครงการก่อสร้างหลายโครงการจากศูนย์กลางเดียว โดยผู้บริหารสามารถเปิดระบบแล้วเห็นได้ทันทีว่าแต่ละโครงการอยู่ในสถานะใด มีงานล่าช้าหรือมีความเสี่ยงตรงไหน ใช้งบประมาณไปเท่าไร มีรายรับรายจ่ายอย่างไร งานตามแผนหรือไม่ และสามารถเจาะลงไปดูงาน รายการเงิน และรูปหลักฐานที่เกี่ยวข้องได้

## 2.2 Product Goal แบบ Canonical ที่เสนอ

> สร้าง Construction Executive Dashboard ที่เป็นศูนย์กลางข้อมูลสำหรับบริหารหลายโครงการก่อสร้าง สามารถติดตามความคืบหน้า Schedule/Gantt รายรับ รายจ่าย ต้นทุน ปัญหา และหลักฐานของแต่ละโครงการ พร้อม Executive Dashboard ที่ตรวจจับและอธิบายโครงการที่เสี่ยงหรือต้องการการตัดสินใจได้อย่างรวดเร็ว โดยข้อมูลทั้งหมดต้องสามารถตรวจสอบย้อนกลับถึง source record และหลักฐานได้ มี permission และ audit trail ที่เหมาะสม และรองรับการขยายไปสู่ mobile, OCR, forecasting และ AI-assisted project management ในอนาคต

## 2.3 Goal lock

Goal นี้ต้องทำหน้าที่เป็นตัวล็อกทิศทางของ Project Brain:

- Dashboard ไม่ใช่ปลายทางเพียงอย่างเดียว
- การเห็นสถานะต้องเชื่อมกับข้อมูลที่ตรวจสอบได้
- การตรวจจับความเสี่ยงต้องอธิบายเหตุผลได้
- การเงินต้องโยงกลับถึงโครงการและหลักฐาน
- Schedule ต้องเชื่อมกับงานจริง ไม่ใช่กราฟแยกจาก operation
- ระบบต้องช่วยผู้บริหาร “ตัดสินใจ” ไม่ใช่เพียง “ดูข้อมูล”
- การขยายด้วย AI ต้องเกิดหลังจาก source data, permission และ evidence เชื่อถือได้

---

# 3. Problem Statement

## 3.1 ปัญหาหลักของผู้บริหาร

ผู้บริหารที่ดูแลหลายโครงการต้องตอบคำถามที่เกิดขึ้นพร้อมกัน:

- โครงการใดกำลังดำเนินการอยู่
- โครงการใดใกล้ส่งมอบ
- โครงการใดช้ากว่าแผน
- งานใดกำลัง block งานถัดไป
- ต้นทุนจริงเทียบกับงบประมาณและ forecast อย่างไร
- เงินเข้า/เงินออกของแต่ละโครงการเป็นอย่างไร
- รายการใดไม่มีหลักฐานหรือยังไม่ผ่านการอนุมัติ
- ความเสี่ยงใดต้องตัดสินใจวันนี้
- ตัวเลขที่เห็นมาจาก record ใด และมีหลักฐานอะไรยืนยัน

## 3.2 ปัญหาของข้อมูลปัจจุบัน

จาก workbook ที่ตรวจพบ ระบบต้นแบบปัจจุบันมีข้อมูลโครงการ งานย่อย รายรับรายจ่าย สรุปตามโครงการ/เดือน/ปี Gantt และการตั้งค่าอยู่แล้ว แต่โครงสร้างยังเป็น spreadsheet-centric และมีสูตรจำนวนมาก.[RAW-XLSX]

ภาพรวมนี้มีคุณค่าสำหรับการเริ่มต้น เพราะสะท้อนภาษางานและรายงานที่ผู้ใช้คุ้นเคย แต่ยังมีความเสี่ยงที่ต้องแก้เมื่อย้ายไปเว็บ:

- ใช้ชื่อโครงการเป็นตัวเชื่อมหลายจุดแทน immutable ID
- หลักฐานบางส่วนอยู่ใน URL หรือช่องรายละเอียดแทน document entity
- ข้อมูลรายรับ/รายจ่ายอาจผูกโครงการไม่ครบ
- ค่าความคืบหน้าอาจถูกแก้ทับโดยไม่เห็นประวัติ
- สูตรใน workbook ไม่ใช่หลักฐานว่าค่าปัจจุบันได้รับการคำนวณและตรวจสอบแล้ว
- งานที่ล่าช้าอาจยังไม่บอก constraint, owner หรือผลกระทบต่อ dependency
- รูปบิลและสลิปมีข้อมูลการเงิน/PII จึงไม่ควรนำเข้าระบบโดยไร้ access policy

## 3.3 ปัญหาที่ระบบนี้ต้องไม่สร้างเพิ่ม

ระบบใหม่ต้องไม่เปลี่ยน spreadsheet หลายชุดให้กลายเป็น dashboard ที่ดูดีขึ้นแต่ยัง:

- ไม่มี source of truth
- ไม่มี audit trail
- ไม่มี project-level permissions
- ไม่รู้ว่ายอดเงินใด approved แล้ว
- ไม่รู้ว่างานช้าเพราะอะไร
- ไม่แยก planned, actual, committed และ forecast
- ไม่สามารถ drill-down จาก KPI ไปยัง record จริง
- ใช้สีแดง/เขียวจากการเลือกด้วยมือโดยไม่มี rule
- ให้ AI หรือ OCR เขียนข้อมูลการเงินเข้าบัญชีโดยไม่ผ่าน review

---

# 4. Vision

## 4.1 Vision statement

> เมื่อผู้บริหารเปิด Construction Executive Dashboard เขาจะเห็นความจริงที่สำคัญของทุกโครงการภายในไม่กี่วินาที รู้ว่าโครงการใดปกติ โครงการใดต้องเฝ้าระวัง โครงการใดกำลังมีความเสี่ยง และสามารถลงไปถึงงาน ต้นทุน ปัญหา และหลักฐานที่อธิบายเหตุผลได้ทันที โดยทุกข้อมูลที่มีผลต่อการตัดสินใจมีที่มา เจ้าของข้อมูล สิทธิ์การเข้าถึง และประวัติการเปลี่ยนแปลงที่ตรวจสอบได้

## 4.2 Future state ที่ต้องการ

ใน Future State:

1. ทุกโครงการมี identity เดียวที่ไม่เปลี่ยนตามชื่อแสดงผล
2. ทุกงานมีแผน วันที่จริง สถานะ เจ้าของ dependency และ constraint
3. ทุกต้นทุนมี project allocation, category, vendor/payee, approval state และ evidence relation
4. ทุกหลักฐานมี metadata, hash, access class, review state และ audit event
5. ทุก KPI อธิบายสูตร ช่วงเวลา record ที่รวม/ไม่รวม และเวลาปรับปรุงล่าสุดได้
6. ทุก red/amber signal เปิดดูเหตุผลและรายการที่ทำให้เกิด signal ได้
7. ผู้บริหารเห็น portfolio view แต่ทีมปฏิบัติงานยังเข้าถึงข้อมูลตาม role/project scope
8. Import จาก Excel เป็น dry-run ที่ตรวจ mapping และ error ก่อนเขียนข้อมูลจริง
9. ระบบรองรับการเติบโตจากทีมเล็กไปหลายโครงการโดยไม่ต้องสร้าง spreadsheet แยกตามคน
10. AI ในอนาคตตอบจากข้อมูลจริงและ evidence ที่ผู้ใช้มีสิทธิ์เห็นเท่านั้น

## 4.3 Promise ต่อผู้ใช้

### ต่อผู้บริหาร

- เปิดแล้วรู้ว่าอะไรต้องจัดการก่อน
- ไม่ต้องรวมตัวเลขจากหลายไฟล์ด้วยตัวเอง
- เห็นทั้งสถานะ โครงการ งาน เงิน และหลักฐานในสายเดียวกัน
- ตัดสินใจโดยมีเหตุผลและข้อมูลรองรับ

### ต่อ Project Manager

- เห็นงานที่ค้าง งานที่ block และงานที่จะมาถึง
- รู้ว่า delay กระทบ milestone ใด
- เชื่อม issue, constraint, cost และ evidence กับ task ได้
- ลดการตามข้อมูลจากแชตและไฟล์แยกกัน

### ต่อทีมหน้างาน

- บันทึกความคืบหน้าและหลักฐานจากหน้างานได้ง่าย
- เห็นงานที่ต้องทำและ dependency ที่เกี่ยวข้อง
- ใช้งานได้บนหน้าจอมือถือในระยะที่ scope อนุมัติ
- ไม่ต้องกรอกข้อมูลซ้ำในหลายระบบ

### ต่อฝ่ายการเงิน

- แยกรายรับ/รายจ่ายโครงการกับไม่เกี่ยวโครงการได้
- ตรวจ transaction กับหลักฐานและผู้อนุมัติได้
- เห็น budget, committed, actual, forecast และ variance
- ลดรายการเงินที่ลอยหรือผูกผิดโครงการ

---

# 5. The Core Loop

แกนกลางของผลิตภัณฑ์คือ:

```text
PLAN
  ↓
SCHEDULE / WBS
  ↓
FIELD EXECUTION
  ↓
PROGRESS + ISSUE + CONSTRAINT
  ↓
EVIDENCE
  ↓
COST / INCOME / EXPENSE
  ↓
RECONCILIATION + APPROVAL
  ↓
EXECUTIVE SIGNAL
  ↓
DECISION / ACTION
  ↓
UPDATED PLAN
```

หรือเรียกเป็นภาษาไทย:

```text
แผนงาน → งานจริง → หลักฐาน → ต้นทุน → การตัดสินใจ → แผนงานที่ปรับปรุง
```

แพลตฟอร์มก่อสร้างสมัยใหม่มักเชื่อม field productivity, daily logs, RFI/submittal, document control, cost และ schedule ไว้ด้วยกัน แทนการแยกเป็นเครื่องมือที่ไม่รู้บริบทซึ่งกันและกัน.[1][2][16]

**North Star test:** ถ้าฟีเจอร์ใดไม่ช่วยให้ loop นี้สั้นลง ชัดขึ้น ตรวจสอบได้ขึ้น หรือช่วยตัดสินใจได้ดีขึ้น ฟีเจอร์นั้นต้องถูกจัดเป็น `DEFER`, `OPTIONAL` หรือ `OUT_OF_SCOPE` จนกว่าจะมีเหตุผลใหม่.

---

# 6. Executive Questions

หน้าแรกต้องตอบ 5 คำถามภายในไม่กี่วินาที

| คำถามของผู้บริหาร | ระบบต้องแสดง | Drill-down ขั้นต่ำ |
|---|---|---|
| มีโครงการกี่โครงการ | Active / Completed / Delayed / Critical count | รายการโครงการตาม status |
| โครงการไหนมีปัญหา | Health / Risk indicator พร้อมเหตุผล | Issue, constraint, overdue task |
| งานช้าหรือไม่ | Schedule variance, overdue tasks, milestone risk | Gantt, dependency, actual vs plan |
| เงินเป็นอย่างไร | Contract, budget, committed, actual, forecast, variance, cash position | Transaction, approval, evidence |
| ปัญหาเกิดจากอะไร | Explanation chain | Project → task → issue/expense → evidence |

## 6.1 Executive information hierarchy

ลำดับการนำเสนอในหน้าแรกควรเป็น:

1. **Decision-required signals** — สิ่งที่ต้องจัดการทันที
2. **Portfolio health** — จำนวนโครงการและการกระจายตามสถานะ
3. **Schedule health** — delay, overdue, milestone, critical path
4. **Financial health** — budget, actual, forecast, cash flow, variance
5. **Evidence/data quality** — รายการไม่มีหลักฐานหรือรออนุมัติ
6. **Recent activity** — สิ่งที่เปลี่ยนล่าสุดพร้อม actor/time
7. **Drill-down links** — ไปยัง source records ทุกชั้น

## 6.2 ตัวอย่างหน้าหลัก

```text
┌─────────────────────────────────────────────────────────────┐
│ CONSTRUCTION EXECUTIVE DASHBOARD                            │
├─────────────────────────────────────────────────────────────┤
│ 12 Projects │ 7 Healthy │ 3 Attention │ 2 Critical          │
├─────────────────────────────────────────────────────────────┤
│ PROJECT HEALTH                                              │
│                                                             │
│ 🔴 Hospital Building     54%   -18 days   4 overdue tasks  │
│ 🟠 Office Renovation     76%    -3 days   cost +8.4%        │
│ 🟢 Warehouse             82%    on track  0 critical issue  │
│ ⚪ Residence A            100%   completed  closeout pending │
├─────────────────────────────────────────────────────────────┤
│ FINANCIAL                                                   │
│ Contract       [value with permission]                      │
│ Budget         [value]                                      │
│ Committed      [value]                                      │
│ Actual         [value]                                      │
│ Forecast       [value]  variance [value/percent]            │
├─────────────────────────────────────────────────────────────┤
│ TOP RISKS                                                   │
│ 🔴 Project A — structure delay blocks roof and electrical   │
│ 🔴 Project B — forecast exceeds approved budget             │
│ 🟡 Project C — expenses awaiting evidence review             │
└─────────────────────────────────────────────────────────────┘
```

ตัวเลขในตัวอย่างเป็น layout example เท่านั้น ไม่ใช่ข้อมูลจริงและไม่ใช่ threshold ที่อนุมัติแล้ว.

---

# 7. Health and Risk Model

## 7.1 สถานะที่เสนอ

ระบบควรใช้ status เชิงความเสี่ยงดังนี้:

- 🟢 `HEALTHY` — ภาพรวมอยู่ในเกณฑ์ที่ยืนยันไว้ ไม่มี trigger ระดับสูง
- 🟡 `ATTENTION` — มีสัญญาณเตือนหรือ data gap ที่ต้องติดตาม
- 🟠 `AT_RISK` — มีความเบี่ยงเบนหรือ constraint ที่อาจกระทบเป้าหมาย
- 🔴 `CRITICAL` — มีผลกระทบสำคัญหรือจำเป็นต้องมีการตัดสินใจ/การแก้ไขเร่งด่วน
- ⚪ `COMPLETED` — งานส่งมอบตาม completion criteria แล้ว แต่ closeout อาจยังค้าง

Status เหล่านี้ต้องคำนวณจากข้อมูลจริงและ configuration ที่ versioned ไม่ใช่ให้ผู้ใช้เลือกสีเอง. แนวทางนี้สอดคล้องกับหลักการที่ KPI และข้อมูลต้นทุนควร trace กลับถึง source records และข้อมูลที่อธิบายได้.[3][4]

## 7.2 มิติที่ใช้คำนวณ health

### Schedule health

- planned finish เทียบ actual/forecast finish
- schedule variance เป็นวันและเปอร์เซ็นต์
- overdue task count
- milestone risk
- critical/near-critical dependency
- blocked task และ unresolved constraint
- progress เทียบ baseline ณ วันที่ตรวจ

### Cost health

- approved budget
- committed cost
- actual posted cost
- forecast at completion
- cost variance เป็นจำนวนเงินและเปอร์เซ็นต์
- approved/unapproved change order
- unallocated transaction
- expense ที่ไม่มี evidence หรือยังไม่ review

### Cash health

- income received
- expected income/milestone payment
- actual expense
- committed upcoming expense
- forecast cash position ตามช่วงเวลา
- payment due/overdue
- cash-flow warning window

### Evidence health

- transaction ที่มีหลักฐานครบ/ไม่ครบ
- document ที่รอ review
- OCR candidate ที่ยังไม่ยืนยัน
- evidence duplicate/hash conflict
- broken or unauthorized reference

### Issue health

- open issue severity
- issue overdue
- issue owner missing
- constraint unresolved
- decision required
- recurrence of same failure reason

## 7.3 Health score boundary

ยังไม่กำหนดคะแนนรวมตายตัวใน North Star เพราะต้องให้ Owner ยืนยัน:

- น้ำหนัก schedule, cost, cash, evidence และ issue
- threshold ของวันล่าช้าและ cost variance
- กรณีที่ Critical override มิติอื่น
- การจัดการข้อมูลไม่ครบ/ข้อมูล stale
- การแยก `data_quality_risk` ออกจาก `project_execution_risk`

หลักที่ควร freeze ก่อน implementation:

```text
HEALTH_STATUS = f(schedule, cost, cash, evidence, issue, data_freshness, override_rules)
```

ทุกผลลัพธ์ต้องเก็บ:

- `health_rule_version`
- `calculated_at`
- `source_record_count`
- `included_record_ids` หรือ query fingerprint
- `excluded_record_count`
- `explanation_codes`
- `manual_override` ถ้ามี พร้อมผู้อนุมัติและเหตุผล

## 7.4 Explanation chain

ตัวอย่างที่ระบบควรอธิบายได้:

```text
Project A: AT_RISK
  → schedule variance: -8 days
  → cause: TASK-STRUCTURE-001 overdue
  → constraint: special material not received
  → impact: blocks TASK-ROOF-001 and TASK-ELECTRICAL-001
  → decision required: approve alternate material or revise sequence
  → evidence: linked issue, purchase record, delivery record
```

นี่คือความแตกต่างหลักระหว่าง executive control system กับ dashboard ที่แสดงค่า progress อย่างเดียว.

---

# 8. Product Principles

## P1 — Decision-first, not chart-first

หน้าจอแรกต้องนำเสนอสิ่งที่ต้องตัดสินใจก่อนรายละเอียดที่สวยงาม.

## P2 — One project identity

ใช้ immutable `project_id`; ชื่อโครงการเป็น display field ที่เปลี่ยนได้.

## P3 — Every KPI has a trail

KPI ทุกตัวต้องเปิดดูสูตร ช่วงเวลา source records และ evidence ที่เกี่ยวข้องได้.

## P4 — Plan and actual are different facts

ห้ามเขียนทับ planned date ด้วย actual date หรือเขียนทับ baseline ด้วย current state.

## P5 — Evidence is a first-class entity

รูป ใบเสร็จ invoice slip quotation PDF และรูปหน้างานไม่ควรเป็น URL ใน text field เท่านั้น.

## P6 — No silent financial allocation

รายการเงินที่ไม่รู้ว่าเป็นของโครงการใดต้องเข้า review queue ไม่กระจายเข้าหมวดทั่วไปโดยเงียบ ๆ.

## P7 — Human approval before accounting truth

Import และ OCR สร้าง candidate ได้ แต่การ post เป็น transaction ที่อนุมัติแล้วต้องมีคนและ audit event.

## P8 — Data quality is visible

ข้อมูลไม่ครบ/เก่า/ขัดกันต้องแสดงเป็น signal ไม่ซ่อนให้ dashboard ดูดี.

## P9 — Field reality matters

ระบบต้องออกแบบให้รองรับการบันทึกจากหน้างาน สัญญาณไม่เสถียร และการแนบภาพในขอบเขตที่อนุมัติ. Field/mobile และ offline workflow เป็น pattern ที่พบในแพลตฟอร์มก่อสร้างเชิงพาณิชย์.[1]

## P10 — Least privilege by default

Authenticated user ไม่ได้แปลว่าเห็นทุกโครงการ ทุก transaction หรือทุก evidence. Authorization ต้องแยกตาม role, project, resource และ action.[8]

## P11 — Research informs; project evidence decides

Vendor feature และบทความอุตสาหกรรมใช้เป็น pattern ไม่ใช่คำสั่งเลือก vendor หรือแทนการยืนยันกับผู้ใช้จริง.

## P12 — Build the smallest trustworthy core

เริ่มจาก MVP ที่ทำให้ข้อมูลโครงการ งาน เงิน และหลักฐานเชื่อมกันได้จริง ไม่เริ่มจาก ERP ก่อสร้างขนาดใหญ่จนไม่มีผู้ใช้.

---

# 9. Users and Roles

## 9.1 Personas

### Executive / Owner

ต้องการ portfolio overview, risk prioritization, cash visibility และ decision context.

### Project Manager

ต้องการจัดการ project, WBS, schedule, progress, issue, constraint และ owner ของงาน.

### Site / Field Operator

ต้องการเห็นงานที่ต้องทำ อัปเดต progress และแนบหลักฐานจากหน้างาน.

### Finance / Accountant

ต้องการบันทึก ตรวจสอบ อนุมัติ และ reconcile รายรับรายจ่ายกับ project/evidence.

### Document / Evidence Reviewer

ต้องการตรวจรูป บิล สลิป และ metadata โดยไม่ต้องเห็นข้อมูลเกินสิทธิ์.

### Administrator

ต้องการจัดการ users, roles, reference data, import batch และ policy.

### Verifier / Auditor

ต้องการตรวจย้อนกลับว่า record, evidence, approval และ calculation ที่มีอยู่ตรงตามระบบหรือไม่.

## 9.2 Proposed role matrix

| Role | Portfolio | Project | Task | Finance | Evidence | Admin | Audit |
|---|---|---|---|---|---|---|---|
| Executive | read all permitted | read | read | read summary | read masked | no | read |
| Project Manager | read assigned | manage assigned | manage assigned | propose/view assigned | upload/review assigned | no | read assigned |
| Site Operator | limited read | limited read | update assigned | propose only | upload assigned | no | own activity |
| Finance | read financial | read permitted | read | manage/review | link/view permitted | no | read financial |
| Evidence Reviewer | limited | limited | limited | review linked | review/manage | no | read evidence |
| Administrator | configured | configured | configured | configured | configured | manage | read |
| Verifier | read | read | read | read | read | no | read all permitted |

ตารางนี้เป็น `PROPOSED` ต้องยืนยันเรื่อง separation of duties, project boundary, sensitive fields และ approval authority ก่อน implementation.

---

# 10. Canonical Domain Model

## 10.1 Entity map

```text
Project
 ├── ProjectPhase
 │    └── Task / WBS
 │          ├── TaskDependency
 │          ├── Constraint
 │          ├── Issue
 │          └── EvidenceDocument
 ├── Budget / CostBaseline
 ├── Transaction
 │    ├── TransactionCategory
 │    ├── Approval
 │    └── EvidenceDocument
 ├── ChangeOrder
 ├── Meeting / Decision
 └── ProjectHealthSnapshot

User / Role / ProjectMembership
EvidenceDocument → AuditEvent
ทุก entity ที่มีผลต่อการตัดสินใจ → AuditEvent
```

## 10.2 Project

ขั้นต่ำ:

- `project_id` — immutable primary identity
- `project_code` — human/business reference
- `project_name` — display name
- `project_type`
- `customer_id` หรือ customer reference ตาม privacy scope
- `location` ตาม access policy
- `project_manager_id`
- `planned_start`
- `planned_finish`
- `contract_value`
- `approved_budget`
- `status`
- `created_at`, `updated_at`
- `version`

## 10.3 ProjectPhase

- `phase_id`
- `project_id`
- `phase_code`
- `phase_name`
- `sequence`
- `baseline_start`
- `baseline_finish`
- `status`
- `completion_rule_version`

## 10.4 Task / WBS

- `task_id`
- `project_id`
- `phase_id`
- `parent_task_id`
- `wbs_code`
- `title`
- `description`
- `owner_id`
- `planned_start`
- `planned_finish`
- `actual_start`
- `actual_finish`
- `forecast_finish`
- `percent_complete`
- `status`
- `milestone`
- `baseline_version`
- `constraint_count`
- `evidence_count`
- `created_at`, `updated_at`

โครงสร้างแบบ parent-child, start/finish, dependency, milestone, filter และ multi-project timeline เป็นองค์ประกอบสำคัญของ Gantt ที่ใช้งานได้จริง.[12]

## 10.5 TaskDependency

- `dependency_id`
- `predecessor_task_id`
- `successor_task_id`
- `relation_type`: `FS`, `SS`, `FF`, `SF` ตามที่ Owner อนุมัติ
- `lag_days`
- `source`
- `created_by`
- `created_at`

## 10.6 Constraint

- `constraint_id`
- `task_id`
- `project_id`
- `constraint_type`
- `description`
- `owner_id`
- `required_by`
- `status`
- `resolved_at`
- `resolution`
- `evidence_ids`

Last Planner System แบ่ง planning เป็น should/can/will/did/learn และเน้น make-ready, weekly promises, daily commitment และการเรียนรู้จาก breakdown.[5] แนวคิดนี้เป็นฐานที่ดีสำหรับการขยายไปสู่ lookahead และ constraint management.

## 10.7 Transaction

- `transaction_id`
- `project_id` nullable แต่ต้องมีเหตุผลเมื่อว่าง
- `direction`: `INCOME` หรือ `EXPENSE`
- `transaction_date`
- `description`
- `category_id`
- `amount` เป็น decimal
- `currency`
- `vendor_or_payee`
- `payment_status`
- `approval_status`
- `entered_by`
- `entered_at`
- `posted_at`
- `source_batch_id`
- `external_reference`
- `evidence_completeness`

## 10.8 Budget and cost layers

ต้องแยกข้อเท็จจริงดังนี้:

- `budget` — งบที่อนุมัติ/ baseline
- `committed` — ภาระผูกพันที่อนุมัติหรือมี commitment
- `actual` — รายการที่ post แล้ว
- `forecast` — ประมาณการปลายทาง
- `variance` — ความต่างจาก baseline/approved budget
- `change_order` — การเปลี่ยนขอบเขต/มูลค่าที่มี workflow

การผูก cost code/CBS กับ WBS และ schedule ช่วยให้เห็น progress, forecast, cash flow และผลกระทบจาก change ได้ดีขึ้น.[4]

## 10.9 EvidenceDocument

- `evidence_id`
- `storage_key`
- `original_filename` เก็บเพื่อแสดงผล แต่ไม่ใช้เป็น identity หลัก
- `generated_filename`
- `mime_type`
- `size_bytes`
- `sha256`
- `document_type`
- `access_class`
- `captured_at`
- `uploaded_by`
- `review_status`
- `ocr_status`
- `ocr_confidence`
- `retention_class`
- `created_at`

Evidence relation ต้องผูกได้กับ:

- Project
- Task
- Transaction
- Income
- Expense
- Issue
- Constraint
- Approval
- Change order

## 10.10 Approval

- `approval_id`
- `resource_type`
- `resource_id`
- `step`
- `required_role`
- `actor_id`
- `decision`
- `decision_reason`
- `decided_at`
- `request_id`
- `previous_state`
- `new_state`

## 10.11 AuditEvent

Audit ต้องเป็นข้อมูล append-only อย่างน้อยสำหรับ:

- create/update/delete/void
- status transition
- amount/category/project allocation change
- evidence upload/download/view/delete request
- approval/rejection
- permission change
- import/export
- OCR candidate accepted/rejected
- health rule/version change

ฟิลด์ขั้นต่ำ:

- `audit_event_id`
- `actor_id`
- `action`
- `resource_type`
- `resource_id`
- `before_summary`
- `after_summary`
- `timestamp`
- `request_id`
- `ip/device_context` ตาม privacy policy

---

# 11. State Machines

สถานะเป็น domain state ที่ตรวจสอบได้ ไม่ใช่สีหรือข้อความอิสระ.

## 11.1 Project status — proposed

```text
PROPOSED
  → READY
  → ACTIVE
  → INSPECTION
  → CORRECTION
  → COMPLETED

ทุกสถานะที่ยังไม่ terminal → CANCELLED ได้ตาม authority policy
```

เงื่อนไข `COMPLETED` ต้องนิยามให้ชัดว่าเป็น:

- งานก่อสร้างเสร็จ
- ตรวจรับเสร็จ
- เอกสารส่งมอบครบ
- การเงินปิดงวด
- หรือเพียง progress 100%

ห้ามใช้คำว่า completed แทนทุกความหมาย.

## 11.2 Task status — proposed

```text
NOT_STARTED → READY → IN_PROGRESS → DONE
                    ↘ BLOCKED ↗
```

`BLOCKED` ต้องมี Constraint หรือ Issue ที่ยังเปิดอยู่.

## 11.3 Transaction status — proposed

```text
DRAFT → PENDING_REVIEW → APPROVED → POSTED
                  ↘ REJECTED
POSTED → VOIDED  (ต้องมี reason + audit)
```

## 11.4 Evidence status — proposed

```text
UPLOADED → SCANNING → EXTRACTED_CANDIDATE → HUMAN_REVIEW
                                                ├→ ACCEPTED
                                                └→ REJECTED
```

OCR เป็นตัวช่วยเสนอข้อมูล ไม่ใช่สถานะการอนุมัติทางบัญชี.

## 11.5 Health status

Health status เป็น derived state จาก rule versioned และไม่ควรให้ผู้ใช้เขียนทับโดยตรง. ถ้าต้อง override ต้องบันทึก:

- ใคร override
- เมื่อไร
- เหตุผล
- หมดอายุเมื่อไร
- rule result เดิม
- approval ที่เกี่ยวข้อง

---

# 12. MVP Definition

## 12.1 MVP mission

สร้างแกนระบบที่ทำให้ผู้บริหารเห็น portfolio health และเปิดดูที่มาของ health ได้จริง โดยมี project, task, schedule, finance, evidence, permissions และ audit เป็นเส้นทางเดียวกัน.

## 12.2 MVP modules

### M1 — Portfolio Dashboard

ต้องมี:

- project count
- Active / Completed / Delayed / At Risk / Critical
- project health cards
- progress
- planned start/finish
- days remaining
- contract value/budget ตามสิทธิ์
- committed/actual/forecast
- cost variance
- schedule health
- open issue/constraint count
- evidence gap count
- last refreshed timestamp
- drill-down ไป project dashboard

### M2 — Project Management

ต้องมี:

- Project Code
- Project Name
- Customer
- Location
- Project Manager
- Start Date
- Due Date
- Contract Value
- Budget
- Status
- Progress
- project ID แบบ immutable
- project membership
- status transition validation

### M3 — Task / WBS

ตัวอย่าง hierarchy:

```text
Project A
├── งานเตรียมพื้นที่
│   ├── สำรวจพื้นที่
│   └── เตรียมวัสดุ
├── งานฐานราก
│   ├── ขุดดิน
│   ├── ลงเสาเข็ม
│   └── เทฐาน
├── งานโครงสร้าง
│   ├── เสา
│   ├── คาน
│   └── พื้น
└── งานระบบ
    ├── ไฟฟ้า
    └── ประปา
```

Task ขั้นต่ำ:

- planned_start
- planned_finish
- actual_start
- actual_finish
- forecast_finish
- percent_complete
- status
- owner
- predecessor/dependency
- constraint
- evidence
- issue

### M4 — Gantt Chart

ต้องรองรับ:

- task hierarchy
- start / finish
- duration
- dependency
- milestone
- progress
- late task
- blocked work
- filters
- project-level view
- portfolio-level viewในขอบเขตที่ข้อมูลรองรับ
- baseline เทียบ current plan
- link กลับ task detail

ห้ามทำเป็นกราฟที่ไม่มี dependency semantics. Gantt ที่ใช้งานจริงควรจัดการ hierarchy, predecessor/successor, multi-project view และการปรับ timeline อย่างมีความหมาย.[12]

### M5 — Income / Expense และ Cost Control

Transaction ต้องรู้:

- project ไหน
- income หรือ expense
- category อะไร
- วันที่
- amount
- vendor/payee
- ผู้บันทึก
- ผู้อนุมัติ
- payment status
- evidence ใด
- source/import batch ใด

รายงานขั้นต่ำ:

- income
- expense
- budget
- committed
- actual
- forecast
- variance
- project/non-project split
- monthly summary
- yearly summary

แนวทาง cost control ที่ดีควรเห็น budget, revisions, committed, forecasted และ actual costs พร้อม change/order context.[3][15]

### M6 — Evidence / Document Upload

รองรับ document type ขั้นต้น:

- site photo
- receipt
- invoice
- payment slip
- quotation
- material photo
- approval document
- PDF

หลักฐานต้องผูกกับ Project, Task, Transaction, Issue หรือ Approval ได้ และต้องมี metadata/lifecycle ของตัวเอง ไม่ใช่ URL ในช่อง text.

File upload ต้องวาง defense-in-depth: allowlist extension, ตรวจ file type จริง, สร้างชื่อไฟล์ใหม่, จำกัดขนาด, เก็บนอก webroot/พื้นที่สาธารณะ, ตรวจสิทธิ์, scan ตามความเหมาะสม และป้องกัน CSRF.[7]

### M7 — Executive Problem Detection

ระบบต้องบอกได้มากกว่า progress:

```text
Project A — AT_RISK
- schedule variance: [value] days
- cost variance: [value] percent
- overdue tasks: [count]
- missing evidence: [count]
- top cause: [issue/constraint]
- blocked successors: [task list]
- decision required: [action]
```

ตัวอย่าง signal:

- งานช้ากว่าแผน
- dependency ถูก block
- forecast เกิน budget
- expense ไม่มี evidence
- cash flow มีความเสี่ยงในช่วงเวลาที่กำหนด
- issue ไม่มี owner
- transaction รอ approval นาน
- data freshness เกิน policy

## 12.3 MVP support modules

แม้ MVP จะมี 7 โมดูลหลัก แต่ต้องมีระบบสนับสนุนขั้นต่ำ:

- authentication
- role/project authorization
- project membership
- audit trail
- reference data versioning
- import batch and validation report
- notification/event hooks ที่ไม่ผูก vendor
- search/filter
- export รายงานตามสิทธิ์
- error state และ empty state ที่อธิบายได้

## 12.4 MVP Definition of Done

MVP จะถือว่า “พร้อมเข้าสู่ verification” เมื่อ:

- [ ] สร้าง project ด้วย immutable ID ได้
- [ ] สร้าง task hierarchy และ dependency ได้
- [ ] เก็บ planned/actual/forecast dates แยกกันได้
- [ ] คำนวณ overdue และ schedule variance จาก rule ที่ระบุได้
- [ ] สร้าง transaction แยก income/expense และ project/non-project ได้
- [ ] เก็บ budget/committed/actual/forecast/variance แยก semantics ได้
- [ ] ผูก evidence กับ transaction/task/project ได้
- [ ] มี approval state และ audit event สำหรับการเปลี่ยนสำคัญ
- [ ] dashboard drill-down ไป source records ได้
- [ ] role ที่ไม่มีสิทธิ์ไม่สามารถอ่าน/แก้ resource ที่ไม่อยู่ใน scope ได้
- [ ] upload policy และ negative tests ผ่าน
- [ ] Excel import ทำ dry-run และรายงาน mapping/error ได้โดยไม่เขียน production records
- [ ] มี test data แบบปกปิด PII/financial values
- [ ] มีสูตร KPI ที่มี version และ timestamp
- [ ] มี current-state, test evidence และ deployment boundary แยกกัน

MVP DoD ไม่เท่ากับ production readiness, Git integration, deployment หรือ project completion.

---

# 13. Phased Product Boundary

## Phase 0 — Project Bootstrap / Requirements and Architecture Baseline

**เป้าหมาย:** ทำให้ Owner Goal, domain vocabulary, role matrix, KPI rules, data model และ architecture boundary อยู่ในเอกสารเดียวที่ review ได้

ผลลัพธ์:

- North Star
- MVP Product Specification
- canonical domain model
- role/permission matrix
- KPI dictionary
- import mapping specification
- evidence/privacy policy
- architecture proposal
- Master Roadmap
- Owner Directive

## Phase 1 — MVP Trusted Core

ประกอบด้วย M1–M7 และ support modules ที่ระบุข้างต้น.

## Phase 2 — Operations Workflow

- Weekly Lookahead
- Make-ready / Constraint Management
- Daily Huddles / field updates
- Approval Workflow ที่ละเอียดขึ้น
- Change Order
- Overdue Notifications
- meeting/decision records

Last Planner System เสนอ five conversations และ key elements เช่น milestone planning, pull planning, make-ready, weekly work planning, constraint management และ daily huddles; จึงเหมาะเป็นฐานของ Phase 2 ไม่จำเป็นต้องยัดทั้งหมดลง MVP.[5]

## Phase 3 — Field and Mobile

- mobile-first views
- offline draft
- sync/conflict policy
- photo capture metadata
- field checklist
- GPS/time metadata เมื่อมีเหตุผลและ policy รองรับ

## Phase 4 — Intelligent Evidence and Finance

- OCR receipt/invoice/slip
- AI expense extraction
- classification and document splitting
- confidence review queue
- accounting integration
- richer reconciliation

Document AI patterns แยก OCR, classification, splitting และ structured extraction และมีแนวทางวัด parser quality; ระบบของเราควรนำแนวคิด “candidate + confidence + human review” มาใช้ ไม่ใช่ให้ OCR เป็นบัญชีจริงโดยอัตโนมัติ.[13][14]

## Phase 5 — Forecasting and Decision Support

- portfolio forecasting
- cash-flow projection
- scenario analysis
- variance trend
- risk explanation enhancement
- executive natural-language queries

## Phase 6 — BIM / Open Data Integration

- IFC import/export boundary
- model-linked task/evidence
- BCF issue linkage
- 4D/5D use case ตามความจำเป็น

IFC เป็นมาตรฐานเปิดแบบ vendor-neutral และออกแบบให้เครื่องอ่านได้เพื่อช่วย interoperability/automation แต่การรองรับ IFC ต้องมี use case, version/view และ compatibility boundary ที่ชัดเจนก่อน.[10][11]

## Future, not MVP

- AI Project Risk Detection
- autonomous financial posting
- automatic contractual/legal decision
- full ERP replacement
- BIM platform replacement
- uncontrolled public evidence sharing

---

# 14. AI Posture

## 14.1 หลักการ

AI ต้องตอบจาก:

```text
Authorized User
+ Permitted Project Scope
+ Current Source Records
+ Approved Evidence
+ Versioned Calculation Rules
+ Explicit Uncertainty
```

AI ต้องไม่ตอบจาก:

- เดาจากชื่อโครงการ
- ข้อมูลที่ผู้ใช้ไม่มีสิทธิ์เห็น
- OCR ที่ยังไม่ผ่าน human review
- ค่า KPI ที่ไม่รู้สูตร
- snapshot เก่าโดยไม่แสดง freshness
- chat context ที่ไม่มี source reference

## 14.2 คำถามในอนาคต

- “มีโครงการไหนเสี่ยงส่งไม่ทัน?”
- “เดือนนี้ค่าใช้จ่ายอะไรผิดปกติ?”
- “ทำไมโครงการโรงพยาบาลช้า?”
- “ค่าแรงโครงการไหนสูงกว่าแผน?”
- “งานใดกำลัง block critical path?”
- “ค่าใช้จ่ายใดรอหลักฐานหรืออนุมัติ?”
- “ถ้าเลื่อน task นี้ 7 วัน จะกระทบ milestone ใด?”

## 14.3 รูปแบบคำตอบที่ต้องการ

```text
ANSWER
- conclusion
- confidence
- data as-of
- affected projects/tasks
- calculation rule version
- source records
- evidence records
- assumptions
- unknowns
- recommended next decision
```

ถ้าข้อมูลไม่พอ AI ต้องตอบ `INSUFFICIENT_EVIDENCE` หรือ `DATA_SCOPE_LIMITED` แทนการเติมคำตอบ.

## 14.4 AI safety boundary

- AI ไม่อนุมัติเงินเอง
- AI ไม่เปลี่ยน budget/baseline เอง
- AI ไม่เปลี่ยน project status เป็น Completed เอง
- AI ไม่ลบ/แก้ทับ evidence
- AI ไม่ข้าม permission
- AI ไม่ส่งข้อมูลการเงิน/PII ไป external provider โดยไม่มี policy/consent
- AI recommendation ต้องแยกจาก system fact

---

# 15. Security, Privacy and Accessibility North Star

## 15.1 Authorization

Authentication คือการพิสูจน์ตัวตน ส่วน authorization คือการตรวจว่าตัวตนนั้นมีสิทธิ์ทำ action ต่อ resource ใด. ผู้ที่ login แล้วจึงไม่ควรเข้าถึงทุก project หรือทุก financial evidence โดยอัตโนมัติ.[8]

ต้องออกแบบ:

- deny by default
- least privilege
- project membership
- role + resource + action checks
- separation of duties ระหว่างผู้บันทึกและผู้อนุมัติ
- negative authorization tests
- periodic privilege review
- audit access to sensitive evidence

## 15.2 Application security baseline

OWASP ASVS ใช้เป็น baseline สำหรับทดสอบ technical security controls และ secure-development requirements; เอกสาร implementation ควร pin version ของ ASVS ที่ใช้ ไม่อ้าง “OWASP ผ่านแล้ว” แบบไม่มี version/ข้อกำหนด.[6]

## 15.3 File and evidence security

- allowlist เฉพาะ file types ที่ business ต้องใช้
- ตรวจ extension หลัง decode filename
- ตรวจ MIME และ file signature โดยไม่เชื่อ Content-Type จาก client อย่างเดียว
- เปลี่ยน filename เป็น generated storage key
- จำกัด file size และ decompressed size
- เก็บนอก webroot หรือ private object storage
- serve ผ่าน authorization-aware handler
- scan/sandbox/CDR ตามชนิดไฟล์และ threat model
- ป้องกัน overwrite/path traversal/zip bomb
- log upload/download/view/delete
- ทำ retention และ backup policy

OWASP แนะนำแนวทางหลายชั้นสำหรับ file upload และย้ำว่าการ block extension อย่างเดียวไม่เพียงพอ.[7]

## 15.4 Financial/PII containment

Raw ปัจจุบันมีภาพสลิป/หลักฐานค่าแรงและ workbook รายรับรายจ่าย จึงต้องจัดเป็น sensitive financial evidence:

- ห้ามใส่ข้อมูลจริงใน public fixture
- list view ต้อง mask field สำคัญ
- download ต้องตรวจสิทธิ์ทุกครั้ง
- export ต้องมี scope และ audit
- OCR/external AI ต้องเป็น opt-in ตาม policy
- log ผู้เปิดดูหลักฐาน
- กำหนด retention/deletion อย่างเป็นทางการ
- แยก evidence storage จาก public assets

## 15.5 Accessibility

UI ควรตั้งเป้า WCAG 2.2 โดยใช้ success criteria ที่ทดสอบได้ รองรับ desktop, laptop, mobile และอุปกรณ์อื่น.[9]

ขั้นต่ำ:

- keyboard navigation
- visible focus
- color ไม่ใช่ตัวบอกสถานะเพียงอย่างเดียว
- text label สำหรับสี/ไอคอน
- contrast
- responsive tables/Gantt
- accessible chart summary
- form error ที่อ่านได้
- Thai language metadata และ date/number format ที่สม่ำเสมอ
- reduced motion option

---

# 16. Architecture North Star

ส่วนนี้เป็น `INFERRED / PROPOSED` สำหรับให้ Athena วิเคราะห์ต่อ ไม่ใช่การเลือก stack แล้ว.

## 16.1 Logical architecture

```text
Web / Mobile UI
      │
      ▼
API / Application Layer
      │
      ├── Identity & Authorization
      ├── Project / Task / Schedule Service
      ├── Finance / Approval Service
      ├── Evidence Service
      ├── Issue / Constraint Service
      ├── Import / Validation Service
      └── Reporting / Health Calculation Service
      │
      ├── Relational operational database
      ├── Private object storage
      ├── Append-only audit store/table
      ├── Read models / materialized summaries
      └── Background workers for scan/OCR/import/notifications
```

## 16.2 Architectural principles

- modular monolith เป็น candidate สำหรับ MVP ถ้าทีม/scale ยังไม่บังคับ microservices
- relational source of truth สำหรับ project/task/transaction/approval
- object storage สำหรับ evidence binary
- read model สำหรับ dashboard แต่สร้างซ้ำจาก write model ได้
- background jobs สำหรับ import, virus scan, OCR และ notification
- idempotent import batch มี mapping/error report
- explicit versioning ของ calculation rules และ reference data
- API ต้อง enforce authorization ที่ server ไม่พึ่ง UI
- external integrations อยู่หลัง adapter boundary
- time zone/calendar policy เป็น explicit configuration
- Thai/Buddhist date display แยกจาก canonical date storage

## 16.3 Data flow ที่ต้องพิสูจน์

### Progress flow

```text
Task update
 → validate actor/project scope
 → create versioned progress record
 → recalculate schedule read model
 → evaluate health rules
 → emit audit event
 → refresh executive dashboard
```

### Expense flow

```text
Create draft transaction
 → attach evidence
 → validate project/category/amount
 → submit review
 → approve/reject
 → post
 → update cost read model
 → evaluate budget/forecast signal
 → audit
```

### Excel import flow

```text
Upload/select workbook
 → create import batch
 → inspect sheets/headers
 → preview mapping
 → detect duplicates/invalid refs
 → user review
 → commit approved rows only
 → produce reconciliation report
 → preserve source batch and lineage
```

### Evidence flow

```text
Upload
 → authenticate + authorize
 → validate type/size/signature
 → generate storage key
 → scan
 → store metadata/hash
 → optional OCR candidate
 → human review
 → link to permitted records
 → audit every sensitive access
```

---

# 17. Research Traceability

## 17.1 Industry patterns adopted as design references

- Procore emphasizes field/mobile connection, daily reports/photos, RFI/submittal, cost visibility, document control and integrated scheduling.[1]
- Procore scheduling references import/sync with P6 or MS Project, lookaheads, dependencies, accountability and filters.[2]
- Procore cost management distinguishes real-time budgets, revisions, committed/forecasted/actual costs, invoice/change workflows and financial visibility.[3]
- Oracle Primavera links cost codes, CBS, WBS, schedule, progress, change impact, cash flow and forecast.[4]
- LCI provides the five-conversation model and constraint/make-ready/weekly commitment concepts.[5]
- OWASP ASVS, authorization guidance and file-upload guidance define security baselines and least-privilege/file-handling controls.[6][7][8]
- W3C WCAG 2.2 provides testable accessibility criteria across devices.[9]
- buildingSMART provides openBIM/IFC interoperability boundaries for later phases.[10][11]
- OpenProject Gantt documents hierarchy, start/finish, dependencies, multi-project and configurable views.[12]
- Google Document AI illustrates OCR, classifier, splitter, structured extraction and quality evaluation patterns.[13][14]
- Autodesk positions integrated schedule, cost, correspondence, RFI, submittal, meeting, quality and safety workflows as connected construction operations.[15][16]

## 17.2 Local project evidence

- Raw inventory: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\manifest.json`
- Workbook schema: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\raw-extracted\c7a829f5da8901ba-workbook-schema.md`
- Media review: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\raw-extracted\media-domain-review.md`
- Raw intake: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\10_INBOX\RAW-INTAKE-2026-09-10.md`
- Previous domain model: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\03_ARCHITECTURE\Construction-Dashboard-Domain-Model.md`
- Research note: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\08_RESEARCH\Construction-Management-Web-Research.md`

---

# 18. MVP Boundary

## 18.1 Must-have

- Portfolio Dashboard
- Project Management
- Task / WBS
- Progress Tracking
- Gantt
- Income / Expense
- Evidence / Document Upload
- Project / Monthly / Yearly Summary
- Roles & Permissions
- Audit Trail
- Excel Import
- KPI explanation/drill-down
- data quality indicators

## 18.2 Phase ต่อไป

- Weekly Lookahead
- Constraint Management
- Approval Workflow ที่ละเอียดขึ้น
- Change Order
- Overdue Notifications
- Mobile-first
- Offline Draft
- Daily Huddles / meeting records
- Field checklist

## 18.3 อนาคต

- OCR ใบเสร็จ
- AI Expense Extraction
- Accounting Integration
- BIM / IFC
- Forecasting
- Scenario Analysis
- AI Project Risk Detection
- Natural-language executive query

## 18.4 Explicit non-goals for MVP

- ไม่เป็น full ERP/accounting replacement
- ไม่เป็น BIM authoring tool
- ไม่รองรับทุกมาตรฐานก่อสร้างทั่วโลกตั้งแต่วันแรก
- ไม่ทำ AI autonomous decision maker
- ไม่ post financial transaction จาก OCR โดยตรง
- ไม่เปิด Raw evidence เป็น public assets
- ไม่ทำ offline sync ซับซ้อนก่อนมี field use case ยืนยัน
- ไม่ทำ microservices/multi-tenant enterprise architecture โดยยังไม่มี scale requirement
- ไม่เพิ่ม vendor integration เพียงเพราะ research กล่าวถึง

---

# 19. Success Measures

ตัวชี้วัดต่อไปนี้เป็น outcome candidates ที่ต้องกำหนด baseline และ target ก่อน freeze.

## 19.1 Executive outcome metrics

- เวลาจากเปิดระบบถึงพบรายการที่ต้องตัดสินใจ
- สัดส่วนโครงการที่มี health status พร้อม explanation
- สัดส่วน KPI ที่ drill-down ถึง source record ได้
- สัดส่วน critical risk ที่ถูกพบก่อน deadline/milestone
- เวลาที่ใช้ตอบคำถาม “ทำไมโครงการนี้ช้า/เกินงบ”
- ความถูกต้องของ project/month/year summary เทียบ canonical records

## 19.2 Operations metrics

- task overdue detection latency
- constraint resolution lead time
- planned-to-actual update completeness
- weekly commitment completion rate ใน Phase 2
- duplicate/manual re-entry reduction
- evidence attachment completeness

## 19.3 Financial data metrics

- unallocated transaction count
- transaction awaiting approval age
- transaction without evidence count
- budget vs actual reconciliation difference
- forecast refresh latency
- project/non-project classification completeness

## 19.4 Product quality metrics

- authorization negative-test pass rate
- upload security test pass rate
- audit coverage of sensitive actions
- import dry-run error precision
- dashboard query latencyตาม target ที่ Owner กำหนด
- accessibility test coverage
- uptime/backup/restore objectiveตาม deployment policy

## 19.5 North Star metric candidate

> **Decision-ready project coverage:** สัดส่วนโครงการที่ผู้บริหารสามารถเห็นสถานะปัจจุบัน พร้อม schedule/cost/evidence explanation ที่มี freshness และ permission ถูกต้อง ภายในเวลาที่กำหนด

สูตรเบื้องต้น:

```text
Decision-ready project coverage
= projects with valid health + explanation + freshness + drill-down
  / projects in executive scope
```

สูตรและ target ต้องผ่าน Owner Gate ก่อนใช้เป็น official KPI.

---

# 20. Governance and Five-role Operating Model

โปรเจคนี้ใช้บริษัท/บทบาทเดิม ไม่สร้าง Agent ใหม่ตาม Company Identity Lock.

```text
                    RIN
            Global Project Governor
                     │
          ┌──────────┼──────────┐
          ▼          ▼          ▼
       ATHENA      HERMES     THEMIS
      Architect   Engineer    Verifier
          │                     │
          └──────────┬──────────┘
                     ▼
                    MIRA
              Project Brain
                     │
                     ▼
                    RIN
```

## 20.1 Responsibilities

### RIN — Global Project Governor

- รักษา Owner Goal และ project identity
- ตรวจ phase/gate/authority
- เลือก next bounded action
- route งานให้ role ที่ถูกต้อง
- adjudicate evidence และ conflicts
- หยุดเมื่อเจอ Owner Gate, security, financial หรือ identity blocker
- แยก completion, verification, Brain update, phase advancement และ Git gates

### ATHENA — Architect

- แปลง North Star เป็น Product Specification
- วิเคราะห์ personas, workflows, domain model, architecture และ trade-offs
- ระบุ unknowns, assumptions, acceptance criteria
- ไม่ implementation และไม่สร้าง authority จากการคาดเดา

### HERMES — Engineer

- implement เฉพาะ slice ที่ได้รับ authorization
- ยึด frozen contract และ exact scope
- เพิ่ม tests และ evidence ตาม task contract
- ไม่เปลี่ยน Goal/architecture/schema policy เอง

### THEMIS — Independent Verifier

- ตรวจ implementation จาก frozen contract
- ตรวจ security, data integrity, permissions, boundary และ runtime ตาม evidence ที่จำเป็น
- ไม่ถือ self-report ของ maker เป็นหลักฐานเพียงอย่างเดียว
- รายงาน PASS/BLOCKED พร้อม critical issues และ warnings

### MIRA — Project Brain

- เก็บ durable project state, decisions, evidence references, handoff และ chronology
- แยก VERIFIED / INFERRED / UNKNOWN / STALE
- ไม่ทำให้ Brain กลายเป็น source code authority หรือแทน live evidence

## 20.2 Operating workflow

```text
OWNER GOAL
     ↓
RIN — identity / gate / alignment
     ↓
ATHENA — Product + Architecture
     ↓
RIN — design adjudication / freeze decision
     ↓
HERMES — bounded implementation
     ↓
THEMIS — independent verification
     ↓
RIN — gate adjudication
     ↓
MIRA — durable Project Brain checkpoint
     ↓
RIN — next bounded action or Owner Gate
     ↓
...
     ↓
PROJECT GOAL
```

## 20.3 Autonomous continuation boundary

Owner-proposed setting:

```yaml
OWNER_REQUIRED: false
AUTONOMOUS_CONTINUATION: true
```

การตีความที่ปลอดภัยของค่านี้คือ RIN สามารถเดิน workflow ต่อเนื่องผ่านงานที่ได้รับอนุญาตและ bounded gates ได้ โดยไม่ต้องถาม Owner ในทุก task ย่อย แต่ **ไม่ใช่** อำนาจให้:

- ข้าม Owner Gate
- เปลี่ยน North Star/Goal
- เปลี่ยน scope/architecture/database policy เอง
- deploy หรือทำ external side effect ที่ไม่ได้รับอนุญาต
- commit/push/merge โดยอัตโนมัติ
- post เงินหรืออนุมัติหลักฐานแทนผู้มีอำนาจ
- เพิ่ม Agent, tool, skill หรือ authority เอง

เมื่อเจอความกำกวมด้าน Goal, role, financial authority, privacy, security, irreversible change หรือ evidence contradiction ต้องหยุดเป็น `OWNER_GATE_REQUIRED`, `REQUIREMENTS_UNRESOLVED` หรือ `AUTHORITY_CONFLICT` ตามกรณี.

---

# 21. Initial Project State

ค่าตั้งต้นตาม Owner proposal:

```yaml
PROJECT: Construction Executive Dashboard

PROJECT_ID: CED-2026

GOAL: Multi-project construction executive management platform

CURRENT_PHASE: PROJECT_BOOTSTRAP

CURRENT_GATE: REQUIREMENTS_AND_ARCHITECTURE_BASELINE

TOP_BLOCKER: Product requirements และ canonical data model ยังไม่ถูก freeze เป็น Project Brain

NEXT_BOUNDED_ACTION: ให้ ATHENA วิเคราะห์ Owner Goal + Raw data + Research แล้วออก MVP Product Specification + Domain Model + Architecture Proposal

OWNER_REQUIRED: false

AUTONOMOUS_CONTINUATION: true
```

## 21.1 Current state qualification

ค่าข้างต้นเป็น **Owner-proposed initial state** ที่พร้อมนำไปสร้าง Project Brain/Roadmap แต่ยังต้องตรวจและ freeze ใน governance record โดยเฉพาะ:

- `CURRENT_PHASE`
- `CURRENT_GATE`
- `TOP_BLOCKER`
- `OWNER_REQUIRED`
- `AUTONOMOUS_CONTINUATION`
- role/profile identity ของแต่ละ worker
- definition ของ “freeze” และผู้มีสิทธิ์ freeze

## 21.2 Known repository boundary

- Local project/Brain root: `C:\2025\web-69\05-Construction-Executive-Dashboard`
- Brain root: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains`
- Raw root: `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\Raw`
- Git remote: `git@github.com:preechayutbubphachat/Construction-Executive-Dashboard.git`
- Remote observation at last check: public repository แต่ยัง empty.[17]
- Local observation at last check: ยังไม่มี `.git` และยังไม่มี application source ใน project root ที่ตรวจ

North Star นี้จึงเป็น knowledge/vision artifact นอก application source และยังไม่มี Git integration claim.

---

# 22. Requirements and Architecture Freeze Gate

ก่อนเลื่อนจาก `PROJECT_BOOTSTRAP` ไป implementation ต้องมีผลลัพธ์ต่อไปนี้:

## 22.1 Product freeze

- [ ] Owner Goal approved as canonical
- [ ] personas and role boundaries approved
- [ ] MVP module list approved
- [ ] explicit non-goals approved
- [ ] executive questions approved
- [ ] health statuses and override policy approved
- [ ] definition of completed/active/delayed/critical approved

## 22.2 Data freeze

- [ ] Project ID/name policy approved
- [ ] WBS/task/dependency semantics approved
- [ ] progress calculation approved
- [ ] planned/actual/forecast semantics approved
- [ ] financial category and direction semantics approved
- [ ] project/non-project policy approved
- [ ] budget/committed/actual/forecast/variance definitions approved
- [ ] evidence metadata and relationship model approved
- [ ] calendar/timezone/Buddhist date policy approved
- [ ] import mapping and duplicate policy approved

## 22.3 Security freeze

- [ ] user/role/project authorization matrix approved
- [ ] sensitive field classification approved
- [ ] upload/file handling policy approved
- [ ] evidence retention/backup/deletion approved
- [ ] audit event minimum set approved
- [ ] external OCR/AI data-sharing policy approved
- [ ] security verification baseline/version approved

## 22.4 Architecture freeze

- [ ] stack decision documented
- [ ] hosting/deployment boundary documented
- [ ] database/storage decision documented
- [ ] background job strategy documented
- [ ] read model/report strategy documented
- [ ] integration boundary documented
- [ ] test strategy documented
- [ ] observability/backup strategy documented
- [ ] rollback and migration policy documented

## 22.5 Execution freeze

- [ ] local repository root available and verified
- [ ] repository baseline/branch/HEAD/status recorded
- [ ] project Brain path bound
- [ ] Master Roadmap created
- [ ] first implementation task has bounded scope
- [ ] maker/verifier/Brain roles assigned
- [ ] acceptance criteria and evidence contract written
- [ ] Owner authorization for implementation recorded

---

# 23. Decision Log Seed

รายการต่อไปนี้เป็น decision debt ที่ต้องปิด ไม่ควรปล่อยให้ implementation ตัดสินใจแทน:

1. ระบบใช้คนเดียว/ทีมเดียว หรือหลายบริษัท/หลายสาขา
2. Project membership มีระดับใดบ้าง
3. ใครมีอำนาจ approve expense และ change order
4. `contract value` กับ `budget` ต่างกันอย่างไรในธุรกิจจริง
5. committed cost หมายถึง purchase order, quotation accepted, หรือค่าใช้จ่ายชนิดใด
6. forecast คำนวณจากอะไร
7. progress เป็น weighted task progress, manual percent, quantity-based หรือผสม
8. วัน/เดือน/ปีใช้ Gregorian/Buddhist display และเก็บ canonical อย่างไร
9. `COMPLETED` หมายถึงก่อสร้างเสร็จ ตรวจรับเสร็จ หรือปิดการเงินด้วย
10. จะใช้ threshold health แบบ fixed config หรือ dynamic baseline
11. ค่าใช้จ่ายสำรองจ่ายต้องเป็น reimbursement workflow หรือเพียง category
12. ต้องเก็บรูปบุคคล/พิกัด/GPS หรือไม่
13. OCR provider ใด/region ใด/ข้อมูลใดส่งออกนอกระบบได้
14. ต้องมี accounting integration ใน Phase 2/3 หรือไม่
15. ต้องรองรับ IFC จริงหรือเป็นเพียง future boundary
16. source data ที่ canonical เมื่อ Excel/ภาพ/รายการที่ผู้ใช้กรอกขัดกันคืออะไร
17. retention ของ financial evidence และ audit records นานเท่าไร
18. initial repository source จะถูกนำเข้า remote เมื่อใดและผ่าน Git gate ใด

---

# 24. Anti-goals and Stop Conditions

## 24.1 Anti-goals

โปรเจคนี้ไม่ควรกลายเป็น:

- ERP ที่พยายามครอบคลุมทุกฟังก์ชันก่อนพิสูจน์ core loop
- dashboard ที่ไม่มี operational workflow
- ระบบบัญชีที่ไม่มี source evidence
- ระบบติดตามงานที่ไม่มี dependency/constraint
- ระบบ AI ที่สรุปจากข้อมูลเดา
- document dump ที่ค้นหาไม่ได้และไม่มีสิทธิ์
- clone ของ Procore/Autodesk/Primavera โดยไม่ปรับกับทีมจริง
- spreadsheet viewer ที่ย้ายปัญหาเดิมมาไว้บนเว็บ

## 24.2 Stop conditions

หยุด progression เมื่อพบ:

- project identity mismatch
- North Star/Owner Goal ขัดกัน
- canonical data model ยังไม่ชัดแต่มีการขอ implementation
- permission boundary ยังไม่กำหนดแต่มีการเปิด financial/evidence access
- formula/KPI ให้ผลต่างกันแต่ไม่มี authority ตัดสิน
- evidence ขัดแย้งและยังไม่มี adjudication
- OCR/AI ถูกขอให้ post financial fact โดยไม่มี human approval
- security blocker ระดับ substantive
- external side effect/commit/push/deploy อยู่นอก authorization
- local repository กับ remote identity ไม่ตรง
- test/evidence ไม่ผูกกับ exact candidate/state

---

# 25. Next Bounded Action

## Action

**ให้ ATHENA วิเคราะห์ Owner Goal + Raw data + Research แล้วออก:**

1. MVP Product Specification
2. Canonical Domain Model
3. Architecture Proposal
4. KPI/Health Rule Dictionary
5. Role and Permission Matrix
6. Import Mapping Specification
7. Evidence and Privacy Policy Draft
8. Master Roadmap Proposal

## Inputs ที่ต้องใช้

- North Star ฉบับนี้
- `manifest.json`
- `raw-extracted/c7a829f5da8901ba-workbook-schema.md`
- `raw-extracted/media-domain-review.md`
- `08_RESEARCH/Construction-Management-Web-Research.md`
- `03_ARCHITECTURE/Construction-Dashboard-Domain-Model.md`
- Owner decisions จาก Decision Log Seed

## Forbidden scope ของ action นี้

- ห้ามเขียน application source
- ห้ามสร้าง database/migration
- ห้าม import financial values เข้า production
- ห้าม OCR/post transaction จริง
- ห้าม clone/init/commit/push/merge/deploy
- ห้ามถือ proposal เป็น frozen contract โดยไม่มี decision record

## Expected output

เอกสาร Product/Architecture ที่มี:

- requirements พร้อม priority
- user journey
- data model พร้อม field semantics
- workflow/state transitions
- health formulas
- permissions
- security controls
- acceptance criteria
- open questions
- assumptions
- P0/P1/P2 gaps
- roadmap phases
- explicit Owner Gate

## Pass condition

ข้อเสนอจาก ATHENA ผ่าน RIN adjudication และถูกบันทึกใน Project Brain โดยแยก:

```text
PROPOSED
vs
OWNER_APPROVED
vs
IMPLEMENTATION_AUTHORIZED
```

การผ่านของเอกสารออกแบบไม่เท่ากับการอนุญาตให้ HERMES เขียนโค้ด เว้นแต่มี authorization gate แยกต่างหาก.

---

# 26. North Star Integrity Rules

1. Goal นี้ต้องไม่ถูกลดรูปเหลือเพียง “ทำ Dashboard ให้เสร็จ”
2. ทุกการเปลี่ยนแปลง North Star ต้องรักษา history และเหตุผล
3. Product metric ต้องไม่แทนที่ Owner outcome
4. Health status ต้องคำนวณจาก source data ไม่ใช่สีที่เลือกเอง
5. KPI ต้องมี formula, time range, freshness และ drill-down
6. Project ID ต้อง immutable
7. Planned, actual, forecast และ baseline ต้องแยกกัน
8. Evidence ต้องเป็น entity ที่มี metadata/lifecycle/access control
9. OCR/AI ต้องสร้าง candidate และ uncertainty ไม่ใช่ fact อัตโนมัติ
10. Permission ต้องตรวจที่ server และมี negative tests
11. Completion, verification, Brain update, phase advancement, Git และ deployment เป็นคนละ gate
12. Unknown ต้องแสดงเป็น Unknown ไม่แปลงเป็น fact
13. Research เป็น supporting evidence ไม่ใช่ authority แทน Owner
14. Future-phase capability ต้องไม่แอบเข้ามาเป็น MVP scope
15. Autonomous continuation ต้องหยุดที่ Owner Gate และ irreversible/high-risk boundary
16. หากข้อมูลไม่พอ ระบบต้อง fail closed ไม่สร้างคำตอบที่ดูแน่นอนเกินหลักฐาน

---

# 27. Definition of North Star Success

North Star นี้ประสบความสำเร็จเมื่อทีมสามารถใช้เอกสารนี้เพื่อ:

- อธิบายโปรเจคด้วยประโยคเดียวกัน
- ตัดสินว่า feature ใดอยู่ใน/นอก MVP
- ออกแบบ domain model โดยไม่ย้อนกลับไปแก้ความหมายพื้นฐานซ้ำ ๆ
- อธิบายว่าข้อมูลจาก Raw ถูกแปลงเป็น product requirement อย่างไร
- วาง architecture ที่ไม่ผูกกับ vendor ก่อนพิสูจน์ need
- สร้าง KPI ที่ผู้บริหารเชื่อถือและตรวจสอบได้
- กำหนดสิทธิ์ก่อนเปิดข้อมูลการเงินและหลักฐาน
- ให้ Athena, Hermes, Themis, Mira และ Rin ทำงานคนละชั้นโดยไม่กลืน authority กัน
- เดินจาก Goal ไป Roadmap และ bounded action ได้โดยไม่ข้าม gate
- ขยายไป mobile, OCR, forecasting และ AI ได้โดยไม่ทำลาย source/evidence integrity

## Final North Star sentence

> **Construction Executive Dashboard คือระบบศูนย์กลางที่ทำให้ผู้บริหารและทีมก่อสร้างรู้ความจริงของหลายโครงการได้เร็ว อธิบายได้ และตรวจสอบได้ — จากแผนงานสู่งานจริง จากหลักฐานสู่ต้นทุน และจากสัญญาณความเสี่ยงสู่การตัดสินใจ — โดยไม่เสียความถูกต้องของข้อมูล สิทธิ์การเข้าถึง และประวัติการเปลี่ยนแปลง**

---

# 28. Sources and Evidence

แหล่งเว็บภายนอกถูกใช้เป็น design/reference pattern ไม่ใช่ implementation authority. แหล่ง local เป็น project evidence ที่ยังมีข้อจำกัดตามที่ระบุไว้ใน Raw intake และ validation report.

## Local evidence

- `[RAW-XLSX]` `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains/raw-extracted/c7a829f5da8901ba-workbook-schema.md`
- `[RAW-MEDIA]` `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains/raw-extracted/media-domain-review.md`
- `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\10_INBOX\RAW-ARRIVAL-2026-09-10.md` — additional 14 media files; inventory-only, not used as semantic requirements
- `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\manifest.json`
- `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains/reports/validation.md`

Google Drive MCP integration เป็นข้อเสนอ platform แยกจาก North Star โดยยึด official endpoint, setup และ tool reference ของ Google.[18][19][20]

ข้อเสนอ integration ต้องเคารพ file eligibility, release boundary และ untrusted-content controls ของ Google.[21][22][23]

ส่วน Hermes OAuth/config, DCR behavior และ release baseline อ้างอิงเอกสาร Hermes แยกต่างหาก.[24][25][26]

## Sources

[1] https://www.procore.com/project-management
[2] https://www.procore.com/project-management/schedule
[3] https://www.procore.com/cost-management
[4] https://www.oracle.com/construction-engineering/primavera-p6
[5] https://leanconstruction.org/lean-topics/last-planner-system
[6] https://owasp.org/www-project-application-security-verification-standard
[7] https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html
[8] https://cheatsheetseries.owasp.org/cheatsheets/Authorization_Cheat_Sheet.html
[9] https://www.w3.org/TR/WCAG22
[10] https://www.buildingsmart.org/about/openbim
[11] https://www.buildingsmart.org/standards/bsi-standards/industry-foundation-classes
[12] https://www.openproject.org/docs/user-guide/gantt-chart
[13] https://cloud.google.com/document-ai
[14] https://docs.cloud.google.com/document-ai/docs/processors-list
[15] https://construction.autodesk.com/resources/cost-management
[16] https://construction.autodesk.com/workflows/construction-project-management
[17] https://github.com/preechayutbubphachat/Construction-Executive-Dashboard — Construction-Executive-Dashboard repository
[18] https://developers.google.com/workspace/drive/api/guides/configure-mcp-server — Google Drive MCP configuration guide
[19] https://developers.google.com/workspace/drive/api/reference/mcp — Google Drive MCP reference
[20] https://developers.google.com/workspace/guides/configure-mcp-servers — Google Workspace MCP servers guide
[21] https://developers.google.com/workspace/drive/api/guides/drive-mcp-server-file-eligibility — Google Drive MCP file eligibility
[22] https://developers.google.com/workspace/drive/release-notes — Google Drive API release notes
[23] https://hermes-agent.nousresearch.com/docs/user-guide/features/mcp — Hermes MCP user guide
[24] https://hermes-agent.nousresearch.com/docs/reference/mcp-config-reference — Hermes MCP config reference
[25] https://github.com/NousResearch/hermes-agent/blob/main/website/docs/user-guide/features/mcp.md — Hermes Agent MCP source documentation
[26] https://github.com/NousResearch/hermes-agent/releases — Hermes Agent releases
