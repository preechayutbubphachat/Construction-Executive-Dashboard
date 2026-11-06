---
project: Construction Executive Dashboard
type: external-research
status: CURATED_RESEARCH
updated: 2026-09-10T10:22:51+07:00
last_verified: 2026-09-10T10:16:35+07:00
confidence: VERIFIED_WITH_INFERENCES
---

# ความรู้จากเว็บ: เว็บจัดการงานก่อสร้าง

## Executive synthesis

ระบบจัดการงานก่อสร้างที่มีประโยชน์ไม่ควรเป็นเพียง dashboard แสดงตัวเลข แต่ควรเชื่อม `แผนงาน → งานภาคสนาม → หลักฐาน → ต้นทุน → การตัดสินใจ` ไว้ในสายข้อมูลเดียวกัน. แพลตฟอร์มเชิงพาณิชย์มักรวม schedule, task, RFI/submittal, document control, cost และ field/mobile workflows เพื่อให้สำนักงานกับหน้างานเห็นข้อมูลชุดเดียวกัน.[1][2][16]

สำหรับโปรเจคนี้ ข้อมูล Raw สอดคล้องกับแกนดังกล่าวอยู่แล้ว: มี project master, task detail, income/expense, project/month/year summary, Gantt และภาพหลักฐานวัสดุ/ค่าแรง. ดังนั้น MVP ควรเริ่มจากการทำให้ข้อมูลเดิมมี identity, provenance, permission และ workflow ที่ตรวจสอบย้อนกลับได้ มากกว่าการเริ่มจากกราฟสวยงามเพียงอย่างเดียว.[RAW-XLSX]

## 1. โมดูลโดเมนที่ควรมี

### 1.1 Portfolio และ project control

- project code, ชื่อโครงการ, ประเภท, ลูกค้า/สถานที่, ผู้รับผิดชอบ, สถานะ, วันเริ่ม, วันกำหนดส่ง, contract duration
- health indicators: schedule variance, cost variance, unresolved issues, evidence completeness
- executive view ต้อง drill down ไปถึง task, transaction และหลักฐานได้

แพลตฟอร์มตัวอย่างเน้นการเชื่อมข้อมูล field production กับ budget, commitment และ change order เพื่อเห็นความเสี่ยงก่อนกลายเป็น overrun.[1][3]

### 1.2 Schedule, Gantt และงานย่อย

Gantt ที่ใช้งานจริงต้องมี start/finish/duration, parent-child hierarchy, dependency, predecessor/successor, milestone, filter และหลายระดับการดูตั้งแต่โครงการเดียวถึงหลายโครงการ.[12] ระบบระดับ enterprise ยังผูก cost code, cost breakdown structure และ work breakdown structure เพื่อวัด progress, forecast และผลกระทบของการเปลี่ยนแปลง.[4]

ข้อเสนอสำหรับโปรเจคนี้คือเก็บ `planned_start`, `planned_finish`, `actual_start`, `actual_finish`, `percent_complete`, `status`, `predecessors`, `owner`, `constraint`, `evidence_ids` แยกจากสูตรแสดงผล ไม่ควรเก็บเพียงค่าความคืบหน้าที่แก้ทับได้.[12]

### 1.3 Lookahead และ weekly commitment

Last Planner System อธิบาย planning เป็น 5 บทสนทนา: `should` จาก pull planning, `can` จาก make-ready/การเอา constraint ออก, `will` จาก weekly promises, `did` จาก daily commitment และ `learn` จากการทบทวนผล.[5] นี่เป็นแนวคิดที่เหมาะกับการต่อยอดจากชีต `รายละเอียดงาน` และ `GANTTChart` ไปเป็น workflow ที่รู้ว่า “งานล่าช้าเพราะอะไร” ไม่ใช่เพียงเปลี่ยนสี progress bar.

ฟิลด์ที่ควรเพิ่มในอนาคต: `constraint_type`, `constraint_owner`, `required_by`, `resolved_at`, `weekly_commitment`, `did_complete`, `variance_reason`, `learning_note`.

### 1.4 Cost control และ cash visibility

ข้อมูลต้นทุนควรแยกอย่างน้อย `budget`, `committed`, `actual`, `forecast`, `variance`, `change_order` และ `evidence`. แหล่งอุตสาหกรรมเน้น real-time project budget, revisions, committed/forecast/actual costs และ invoice/change-order workflow.[3] Oracle ระบุว่าการผูก schedule กับ cost ช่วยทำ time-phased cash-flow, progress measurement และ early warning จากการเปลี่ยนแปลง.[4]

สำหรับ Raw ปัจจุบันต้องคงมิติ `project vs non-project` ไว้ และเพิ่ม `payment_status`, `approved_by`, `cost_code`, `vendor`, `source_document_id`, `advance_or_reimbursement`, `entered_by`, `entered_at` เพื่อป้องกันยอดเงินลอยหรือผูกผิดโครงการ.

### 1.5 เอกสาร ภาพ และ OCR

รูปบิล/สลิปควรเป็น `document/evidence` ที่มี metadata และ lifecycle ไม่ใช่เพียง URL ในช่องรายละเอียด. Google Document AI แยกงาน OCR, classification, splitting และ extraction เป็น processor และมีเครื่องมือประเมินคุณภาพ/ประสิทธิภาพของ parser.[13][14] แนวทางที่เหมาะคือ OCR ช่วยเสนอค่า `vendor/date/amount/category/project`, แสดง confidence และบังคับ human review ก่อน post เป็น transaction.

### 1.6 Document control และการประสานงาน

แพลตฟอร์มก่อสร้างเชื่อม RFI, submittal, inspection, meeting minutes, drawing/sheet และ issue เข้าด้วยกัน เพื่อให้คำถาม/การตัดสินใจโยงกลับไปยังงานและเอกสารได้.[1][16] MVP ไม่จำเป็นต้องทำทุกโมดูลพร้อมกัน แต่ควรมี generic `issue` และ `linkable_reference` ตั้งแต่ต้นเพื่อไม่ล็อก schema แคบเกินไป.

## 2. สถาปัตยกรรมข้อมูลที่ได้จากการเปรียบเทียบ

| ชั้น | หน้าที่ | ข้อเสนอสำหรับโปรเจค |
|---|---|---|
| Identity | project/task/transaction/document IDs | ใช้ immutable IDs และ external reference แยกจากชื่อที่แก้ได้ |
| Operational write model | ข้อมูลที่ผู้ใช้บันทึก | relational schema + validation + transaction boundary |
| Evidence/object storage | รูปบิล สลิป รูปหน้างาน PDF | private object storage, metadata, hash, access log |
| Workflow | approval, issue, constraint, change | state machine ไม่ใช้สี/ข้อความอิสระเป็น state หลัก |
| Read models | dashboard, monthly/yearly summaries, Gantt | query/read model ที่คำนวณจาก source of truth |
| Audit | who/when/what/before-after | append-only audit event; แยกจาก activity feed ที่แก้ได้ |
| Integration | Excel import/export, accounting, BIM/API | explicit import batch + mapping + validation report |

การใช้ openBIM/IFC ควรเป็น future integration boundary ไม่ใช่เงื่อนไข MVP เว้นแต่เจ้าของยืนยันว่าต้องรับโมเดล BIM. buildingSMART ระบุว่า IFC เป็นมาตรฐานเปิดแบบ vendor-neutral และออกแบบให้เครื่องอ่านได้เพื่อช่วย automation.[10][11]

## 3. Security และ privacy ที่ต้องถือเป็น requirement

- Authentication ไม่เท่ากับ authorization; ผู้ login แล้วไม่ควรเห็นทุกโครงการหรือทุกเอกสาร.[8]
- ออกแบบ deny-by-default และ least privilege แยกตาม role, project, resource และ action; ต้องมี negative authorization tests.[8]
- ใช้ OWASP ASVS เป็น baseline สำหรับการทดสอบ security controls และ secure-development requirements โดย pin version ในเอกสาร/issue.[6]
- File upload ต้อง allowlist extension, ตรวจชนิดไฟล์จริง, สร้างชื่อไฟล์ใหม่, จำกัดขนาด, เก็บนอก webroot/คนละ storage, scan เมื่อเหมาะสม และตรวจสิทธิ์ก่อน upload/download.[7]
- Financial evidence และ PII ต้อง masked ใน list view, จำกัด download, log การเปิดดู และไม่ส่งเข้า AI/OCR ภายนอกโดยไม่มี policy/consent ที่ชัดเจน.
- UI ควรยึด WCAG 2.2; W3C ระบุ success criteria เป็นข้อความที่ทดสอบได้และใช้ได้กับ desktop/mobile/kiosk.[9]

## 4. Product lessons ที่ควรนำมาใช้กับโปรเจคนี้

1. **Single source of truth:** ทุก dashboard cell ต้อง trace กลับไปยัง source record และ evidence ได้
2. **Field-first:** หน้างานอาจ offline/สัญญาณไม่ดี จึงควรมี draft/offline queue และ conflict policy ในระยะที่เหมาะสม; แพลตฟอร์มตัวอย่างวาง mobile/offline field workflow เป็นแกนหลัก.[1]
3. **Schedule-to-cost linkage:** งานที่เสร็จ/ล่าช้าควรอธิบายผลต่อ cash-flow และ forecast ไม่ใช่แยก Gantt ออกจากบัญชี.[4][16]
4. **Approval before posting:** OCR/import ต้องสร้าง candidate ไม่ใช่เขียน ledger ตรง
5. **Explainable executive metrics:** ทุก KPI ต้องแสดง period, formula version, included/excluded records และ last refreshed time
6. **Historical integrity:** ห้ามแก้ทับหลักฐานหรือยอดเดิมโดยไร้ revision/audit

## 5. ขอบเขต MVP ที่แนะนำ

### Must-have

- project master + status + start/due date
- task/WBS + progress + planned/actual dates + owner + issue/constraint
- Gantt แบบ dependency พื้นฐาน
- transaction ledger แยก project/non-project และ income/expense
- document/evidence metadata + upload security boundary
- project/month/year summaries ที่ drill down ได้
- role/permission + audit trail
- import preview จาก Excel พร้อม validation/error report

### Should-have

- weekly lookahead และ commitment
- change order
- approval/reimbursement workflow
- notifications/overdue alerts
- mobile responsive และ offline draft

### Later

- OCR confidence/review queue
- accounting integration
- BIM/IFC/BCF integration
- portfolio forecasting และ scenario analysis

## 6. Research และ project boundary

Autodesk จัด cost management เป็นชุดทรัพยากรแยกต่างหากที่เชื่อมงาน field/finance, ERP integration, budget snapshots และ cost-control workflow; จึงใช้เป็น reference ของ integration seam ไม่ใช่เหตุผลให้เลือก vendor.[15]

การตรวจ repository แบบอ่านอย่างเดียวพบว่า GitHub repository ของโปรเจคมีอยู่จริง แต่ยังเป็น empty repository ณ เวลาตรวจ และยังไม่มี local Git state ให้ผูกหลักฐาน implementation.[17]

## 7. ข้อควรระวังในการใช้แหล่งเว็บ

แหล่ง Procore/Autodesk/Oracle เป็น vendor material จึงใช้บอก feature pattern และ workflow pattern ไม่ใช่หลักฐานว่า feature นั้นเหมาะกับทีมนี้หรือให้ผลลัพธ์ตามตัวเลขการตลาด. LCI, OWASP, W3C และ buildingSMART ใช้เป็น standards/guidance boundary แต่ต้องแปลงเป็น acceptance tests ของโปรเจคจริงก่อน implementation.

[RAW-XLSX] = `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\raw-extracted\c7a829f5da8901ba-workbook-schema.md`

[RAW-MEDIA] = `C:\2025\web-69\05-Construction-Executive-Dashboard\AI-Brains\raw-extracted\media-domain-review.md`

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
