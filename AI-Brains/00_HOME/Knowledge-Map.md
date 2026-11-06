---
project: Construction Executive Dashboard
type: project-knowledge-map
status: BOOTSTRAPPED_KNOWLEDGE_ONLY
updated: 2026-09-10T13:52:51+07:00
last_verified: 2026-09-10T13:52:51+07:00
confidence: VERIFIED
---

# Knowledge Map

## VERIFIED — สิ่งที่พบจากไฟล์ผู้ใช้

- `Raw` มี 18 ไฟล์ รวม 4,334,359 bytes และมี SHA-256 ไม่ซ้ำกัน 18 ค่า
- มี workbook 1 ไฟล์ 14 ชีต; schema extraction สำเร็จโดยไม่รันสูตร
- มีภาพ 17 ไฟล์: 3 ไฟล์ผ่าน domain review เดิม และ 14 ไฟล์เข้ามาเพิ่มระหว่าง session จึงยังเป็น inventory-only
- Workbook มี project master, task detail, income/expense ledger, project/month/year summaries, Gantt, settings และ todo projection
- ฟิลด์สำคัญที่มีอยู่แล้ว: project, status, start/due date, progress, task, planned/actual date, duration, amount, category, project allocation, evidence link, issue/remark
- มีการแยกรายการเงินโครงการกับรายการไม่เกี่ยวโครงการ
- มี GitHub remote ตามที่เจ้าของแจ้ง แต่ remote ยัง empty และเครื่องนี้ยังไม่มี local `.git`

แหล่งหลัก: `manifest.json`, `stats.json`, `raw-extracted/c7a829f5da8901ba-workbook-schema.md`, `raw-extracted/media-domain-review.md`

## INFERRED — แกนเว็บที่ควรออกแบบ

- [[01_VISION/NORTH_STAR]] — Owner Goal, product vision, MVP boundary และ freeze gates
- [[04_INTEGRATIONS/Google-Drive-Remote-MCP-Proposal]] — Drive MCP platform proposal; `PROPOSED_NOT_ACTIVATED`

1. Project portfolio/dashboard
2. Project detail และ phase/task tracking
3. Gantt + dependency + lookahead/weekly plan
4. Cost ledger แยกรับ/จ่าย/โครงการ/ไม่เกี่ยวโครงการ
5. Evidence/document vault สำหรับบิล สลิป รูป และเอกสาร
6. Issue/constraint/RFI-like workflow
7. Executive summaries ตามโครงการ เดือน ปี
8. Audit trail, role-based authorization และ privacy masking

## UNKNOWN / ต้องตัดสินใจก่อน implementation

- ผู้ใช้จริงและ role matrix
- stack, hosting, database และ storage
- นิยามทางธุรกิจของ `% ความคืบหน้า`, `status`, `คงเหลือ`, `สำรองจ่าย`
- แหล่งข้อมูลปัจจุบันที่ถือเป็น canonical เมื่อ Excel กับภาพขัดกัน
- ต้องรองรับหลายบริษัท/หลายสาขาหรือไม่
- ต้องรองรับ BIM/IFC หรือไม่ในระยะ MVP

## การนำทาง

- [[08_RESEARCH/Construction-Management-Web-Research]]
- [[03_ARCHITECTURE/Construction-Dashboard-Domain-Model]]
- [[projects/construction-executive-dashboard-map]]
- [[10_INBOX/RAW-INTAKE-2026-09-10]]
- [[10_INBOX/RAW-ARRIVAL-2026-09-10]]
