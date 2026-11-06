# CED-2026 — UI Flow / Sitemap / Screen Inventory

> **เอกสาร:** สรุป UI Flow และรายการหน้าเพื่อใช้วางแผนออกแบบ  
> **Project ID:** `CED-2026`  
> **Project:** Construction Executive Dashboard  
> **จัดทำ:** 2026-10-27  
> **สถานะ:** `PROPOSED_SCREEN_INVENTORY_NOT_FROZEN`  
> **ขอบเขต:** Desktop-first 1920 × 1080; Responsive เป็นลำดับถัดไป  
> **ไม่ใช่:** รายการ Route ที่ยืนยันว่ามีอยู่จริง, แผนหน้าเว็บที่ Owner freeze แล้ว หรือ authorization ให้แก้ Production

## 1. สรุปจำนวนที่ตรวจยืนยันได้ / ไม่ได้

- **7 โมดูลหลักของ MVP:** ยืนยันจาก `NORTH_STAR.md` หัวข้อ 12.2 ว่าเป็นจำนวนโมดูล ไม่ใช่จำนวนหน้า
  - M1 Portfolio Dashboard
  - M2 Project Management
  - M3 Task / WBS
  - M4 Gantt Chart
  - M5 Income / Expense & Cost Control
  - M6 Evidence / Document Upload
  - M7 Executive Problem Detection
- **4 พื้นที่ UI ที่มีร่องรอยในงาน P2:** Authentication UI, Construction Evidence, Executive Dashboard, Gantt / Schedule; ข้อมูลนี้ไม่ยืนยันว่าทุกส่วนเป็น Route เดี่ยวหรือว่าผ่าน UAT แล้ว
- **จำนวนหน้าเว็บที่เคยอนุมัติอย่างเป็นทางการ:** **ยืนยันไม่ได้** จากเอกสารและประวัติที่ตรวจพบ
- **จำนวนหน้าที่เอกสารฉบับนี้เสนอให้ใช้เป็น Sitemap สำหรับการออกแบบ:** **20 หน้า** (เป็นข้อเสนอ ไม่ใช่รายการหน้าที่ตรวจจาก Repository)
- **20 หน้าแบ่งเป็น:** Authentication 1 + Portfolio 4 + Execution 5 + Finance 3 + Evidence 2 + Reporting/Import 2 + Administration 3

## 2. รายการหน้าเว็บ 20 หน้า (Proposed)

| # | Screen ID | ชื่อหน้า | กลุ่ม | ที่มาของขอบเขต | หน้าที่หลัก |
|---:|---|---|---|---|---|
| 01 | `AUTH-01` | เข้าสู่ระบบและบัญชีผู้ใช้ | Authentication | มีระบุใน P2 UI | Login, Logout, Session, สิทธิ์เข้าถึง |
| 02 | `EXE-01` | ภาพรวมผู้บริหาร / Portfolio Dashboard | Portfolio | มีระบุใน P2 UI | สุขภาพทุกโครงการ, ความเสี่ยง, การตัดสินใจ, KPI พร้อม drill-down |
| 03 | `PRJ-01` | รายการโครงการ | Portfolio | เสนอแยกหน้า | ค้นหา กรองสถานะ เลือกโครงการ |
| 04 | `PRJ-02` | ภาพรวมโครงการรายตัว | Portfolio | เสนอแยกหน้า | ความคืบหน้า แผน เงิน ปัญหา และหลักฐานของโครงการ |
| 05 | `PRJ-03` | สร้าง/แก้ไขข้อมูลโครงการ | Portfolio | เสนอแยกหน้า | รหัส ชื่อโครงการ วันเริ่ม-สิ้นสุด ผู้รับผิดชอบ งบและสิทธิ์ |
| 06 | `WBS-01` | รายการงาน / Work Breakdown Structure | Execution | เสนอแยกหน้า | งานหลัก-งานย่อย ลำดับงาน สถานะ ผู้รับผิดชอบ |
| 07 | `WBS-02` | รายละเอียดงาน (Task Detail) | Execution | เสนอแยกหน้า | วันที่แผน/จริง/คาดการณ์ Predecessor ข้อจำกัด หลักฐาน |
| 08 | `SCH-01` | แผนงาน / Gantt + Critical Path | Execution | มีระบุใน P2 UI | Timeline, dependency, milestone, baseline เทียบแผนปัจจุบัน |
| 09 | `PRG-01` | บันทึกและติดตามความคืบหน้า | Execution | เสนอแยกหน้า | อัปเดต progress ของงานตามสิทธิ์และดูประวัติ |
| 10 | `ISS-01` | ปัญหาและข้อจำกัด (Issue / Constraint) | Execution | เสนอแยกหน้า | ติดตาม blocker, ผู้รับผิดชอบ, วันครบกำหนดและผลกระทบ |
| 11 | `FIN-01` | ภาพรวมการเงินโครงการ | Finance | เสนอแยกหน้า | Budget, Committed, Actual, Forecast, Variance, Cash visibility |
| 12 | `FIN-02` | รายการรายรับ–รายจ่าย | Finance | เสนอแยกหน้า | ธุรกรรมตามโครงการ/ไม่ผูกโครงการ หมวดหมู่ และสถานะ |
| 13 | `FIN-03` | รายละเอียดรายการเงินและการอนุมัติ | Finance | เสนอแยกหน้า | แนบหลักฐาน ตรวจสอบ อนุมัติ/ปฏิเสธ และบันทึก audit |
| 14 | `EVD-01` | คลังเอกสารและหลักฐานก่อสร้าง | Evidence | มีระบุใน P2 UI | รูปหน้างาน ใบเสร็จ สลิป ใบเสนอราคา PDF |
| 15 | `EVD-02` | รายละเอียดและตรวจทานหลักฐาน | Evidence | เสนอแยกหน้า | Metadata, การเชื่อมงาน/เงิน, review state, สิทธิ์การดู |
| 16 | `REP-01` | รายงานโครงการ / รายเดือน / รายปี | Reporting | เสนอแยกหน้า | Summary, ความเบี่ยงเบน, ที่มาของ KPI และ export ตามสิทธิ์ |
| 17 | `IMP-01` | นำเข้า Excel และตรวจตัวอย่างก่อนยืนยัน | Reporting | เสนอแยกหน้า | Mapping, validation, error report, duplicate check, confirm |
| 18 | `ADM-01` | ผู้ใช้ บทบาท และสิทธิ์รายโครงการ | Administration | เสนอแยกหน้า | Role, project membership, least privilege |
| 19 | `AUD-01` | ประวัติการเปลี่ยนแปลง / Audit Trail | Administration | เสนอแยกหน้า | ใครทำอะไร เมื่อไร ก่อน/หลัง และ reference ที่ตรวจสอบได้ |
| 20 | `SET-01` | ตั้งค่าระบบและข้อมูลอ้างอิง | Administration | เสนอแยกหน้า | หมวดข้อมูล Calendar/Timezone, rule version และค่าพื้นฐาน |

**วิธีอ่านคอลัมน์ 'ที่มาของขอบเขต':**
- `มีระบุใน P2 UI` = มีหลักฐานว่าพื้นที่งาน UI นี้เคยอยู่ใน P2; **ไม่ใช่** การยืนยัน Route หรือความเสร็จสมบูรณ์
- `เสนอแยกหน้า` = ฟีเจอร์มาจากความต้องการใน North Star แต่การเลือกแยกเป็นหน้าเองเป็น **ข้อเสนอการออกแบบในไฟล์นี้**

### 2.1 จำนวนหน้าแยกตามหมวด

| หมวด | Screen ID | จำนวน |
|---|---|---:|
| Authentication | AUTH-01 | 1 |
| Portfolio / Projects | EXE-01, PRJ-01, PRJ-02, PRJ-03 | 4 |
| Execution / Schedule / Field | WBS-01, WBS-02, SCH-01, PRG-01, ISS-01 | 5 |
| Finance | FIN-01, FIN-02, FIN-03 | 3 |
| Evidence | EVD-01, EVD-02 | 2 |
| Reporting / Import | REP-01, IMP-01 | 2 |
| Administration | ADM-01, AUD-01, SET-01 | 3 |
| **รวม** | | **20** |

## 3. โปรแกรมใช้งานอย่างไร (Primary UI Flows)

### Flow A — ผู้บริหารเปิดระบบแล้วเจอโครงการที่ต้องตัดสินใจ

`AUTH-01` → `EXE-01` → `PRJ-02` → `SCH-01` / `ISS-01` / `FIN-01` → `WBS-02` / `FIN-03` → หลักฐานที่มีสิทธิ์ดู (`EVD-02`) → กลับ Dashboard

เป้าหมาย: **ไม่ใช่เพียงแสดงกราฟ** แต่เชื่อมสัญญาณความเสี่ยง → สาเหตุ → record → evidence → สิ่งที่ต้องตัดสินใจ

### Flow B — สร้างโครงการและวางแผนงาน

`PRJ-01` → `PRJ-03` → `PRJ-02` → `WBS-01` → `WBS-02` → `SCH-01`

- Project ใช้ immutable ID
- Task / WBS มี planned / actual / forecast แยกความหมาย
- Gantt มี predecessor/successor, milestones และ baseline; ไม่ใช้แท่งเวลาที่ไม่มี dependency semantics
- **การแก้ baseline ต้องอยู่ภายใต้สิทธิ์และกติกาที่ได้รับอนุมัติ ไม่ใช่เพียงลากแท่งบนหน้าจอ**

### Flow C — หน้างานรายงานความคืบหน้าและปัญหา

`PRJ-02` → `WBS-01` → `WBS-02` → `PRG-01` → `ISS-01` และ/หรือ `EVD-01` → กลับ `EXE-01`

- บันทึกความคืบหน้าพร้อม timestamp และ actor
- อ้างอิงภาพหน้างานตามสิทธิ์
- Recalculate read models/health จากข้อมูลจริงและ rule version ไม่ใช่ปรับสถานะสีเอง

### Flow D — รายรับ รายจ่าย และการตรวจอนุมัติ

`PRJ-02` → `FIN-01` → `FIN-02` → `FIN-03` → `EVD-02` → อนุมัติ/ปฏิเสธตาม role → `AUD-01`

- แยก project/non-project
- แยก Budget / Committed / Actual / Forecast
- OCR และ Import เป็น candidate ก่อน human approval
- ห้ามถือธุรกรรมที่ยังไม่ post เป็นยอดจริงโดยไม่ระบุสถานะ

### Flow E — นำเข้าข้อมูลจาก Excel

`IMP-01` → upload/import batch → mapping → preview → validation errors → human review → commit รายการที่อนุมัติ → ตรวจรายงานผล

การ import ต้องมี dry-run และ error report ก่อนเปลี่ยนข้อมูลจริง

### Flow F — ตรวจสอบย้อนหลัง

`EXE-01` / `PRJ-02` / `FIN-03` / `EVD-02` → `AUD-01`

ผู้ใช้ต้องเห็นที่มาของ KPI, ผู้แก้ไข, เวลา, approval และ evidence ภายใต้สิทธิ์ของตน

## 4. Navigation Structure — ข้อเสนอ

```text
CED-2026
├── เข้าสู่ระบบ (AUTH-01)
└── พื้นที่ผู้ใช้ที่เข้าสู่ระบบ
    ├── ภาพรวม (EXE-01)
    ├── โครงการ (PRJ-01)
    │   ├── รายละเอียดโครงการ (PRJ-02)
    │   ├── สร้าง/แก้ไขโครงการ (PRJ-03)
    │   ├── WBS / งาน (WBS-01)
    │   │   ├── รายละเอียดงาน (WBS-02)
    │   │   ├── ความคืบหน้า (PRG-01)
    │   │   └── ปัญหา / ข้อจำกัด (ISS-01)
    │   ├── แผนงาน / Gantt (SCH-01)
    │   ├── การเงิน (FIN-01)
    │   │   ├── รายรับ–รายจ่าย (FIN-02)
    │   │   └── ตรวจรายการเงิน (FIN-03)
    │   └── หลักฐาน (EVD-01)
    │       └── ตรวจทานหลักฐาน (EVD-02)
    ├── รายงาน (REP-01)
    ├── นำเข้า Excel (IMP-01)
    └── จัดการระบบ
        ├── ผู้ใช้และสิทธิ์ (ADM-01)
        ├── Audit Log (AUD-01)
        └── ตั้งค่า (SET-01)
```

รายการนี้เป็น **Information Architecture Proposal**; ไม่ใช่รายการ URL ในแอปจริงและไม่ได้ยืนยันว่าหน้าใดเป็น modal, drawer หรือ route แยก

## 5. การออกแบบ Desktop-first

- Design target **1920 × 1080**, ใช้ความกว้าง `100%` และ full browser viewport แทนการล็อก CSS เป็น 1920px
- หน้า **Executive Dashboard** แสดง decision-required signals, health, schedule, finance และ evidence ก่อนส่วนตกแต่ง
- หน้า **Gantt** จัดโครงสร้างเป็นตาราง WBS ทางซ้าย + Timeline ทางกลาง + Context/Inspector ทางขวา พร้อม scroll ที่สัมพันธ์กัน
- UI ภาษาไทย, แสดงวันที่และตัวเลขอย่างสม่ำเสมอ, keyboard navigation และสถานะด้วยตัวอักษรประกอบสี
- ระยะถัดไป: 1440p / laptop / tablet / mobile หลังตรวจ layout 1080p และ flows สำคัญ

### React Three Fiber (R3F) อยู่ตรงไหน?

- `SCH-01`: พื้นที่เสริม **3D Progress / floor or zone inspector** เชื่อมกับ task ที่เลือก หากมี model และ mapping ที่พิสูจน์ได้
- `PRJ-02`: มุมมอง 3D โครงการเป็น optional visualization ไม่ใช่แหล่งความจริงของ schedule
- `WBS-02`: เลือก task แล้ว focus ไปยัง geometry/zone ได้เมื่อมีข้อมูล mapping ที่ถูกต้อง
- ไม่มีการเพิ่มหน้าแยกชื่อ “3D” ลงใน 20 หน้า เพราะ 3D เป็น component ที่อยู่ใน flow เดิม
- ถ้าไม่มีโมเดลจริง ใช้ demo model และป้ายกำกับชัดเจน ห้ามอ้างเป็น BIM/as-built
- Core controls, finance, Gantt, alerts, approval และ evidence อยู่ใน DOM/HTML พร้อม fallback เมื่อ WebGL ไม่ทำงาน

## 6. สิ่งที่ยังไม่นับเป็น 20 หน้า

รายการต่อไปนี้เป็น capability, component หรือระยะอนาคต และ **ไม่ได้รับรองว่าเป็นหน้าแยกของ MVP**:

- S-Curve, Cash Flow chart, EVM: อาจเป็น tab/view ภายใน Gantt, Finance หรือ Reports; ยังไม่ยืนยันแยก route
- Weekly Lookahead, Daily Huddles, Change Order และ notification workflows: Phase 2
- Mobile-first / Offline Draft: Phase 3
- OCR + AI extraction: Phase 4 โดยต้องมี human review
- Forecasting / Scenario / AI executive query: Phase 5
- BIM / IFC / BCF: Phase 6
- 3D scene ของ `pmndrs/react-three-fiber`: embedded UI ไม่ใช่หน้าใหม่

## 7. หลักฐานและข้อจำกัด

### แหล่งที่ใช้

1. `NORTH_STAR.md` — หัวข้อ 6 (Executive Questions), 10–12 (Domain/MVP), 13 (Phased Product Boundary), 15 (Security), 16 (Architecture)
2. `Construction-Management-Web-Research.md` — โมดูล portfolio, task/schedule, finance, evidence, permissions, import
3. ประวัติโครงการ CED-2026 ที่ค้นได้: P2 UI areas ได้แก่ Authentication, Construction Evidence, Executive Dashboard, Gantt/Schedule; ยังไม่พบ officially frozen page inventory
4. คำขอ UI ในแชตนี้: Desktop-first 1920×1080 และหน้า Gantt ที่ใช้ React Three Fiber เป็นส่วนเสริม
5. URL หน้า Gantt ที่ Owner ส่งมา: `http://127.0.0.1:8765/projects/7c6e9879-82bf-4f5a-8858-5c7e5e432caa/gantt` — **เป็น URL ที่ได้รับจากผู้ใช้ ไม่ได้ทดสอบว่าสามารถเปิดได้จากสภาพแวดล้อมนี้**

### การตีความสถานะ

- `VERIFIED`: ยืนยันได้ว่ามี **7 MVP modules** ใน North Star และ **4 P2 UI areas** ในประวัติที่ค้นพบ
- `PROPOSED`: ตัวเลข **20 หน้า**, ชื่อ screen IDs, หมวด navigation, การแยกหน้า, flow map และ 3D component placement
- `UNKNOWN`: จำนวน page/route ที่มีใน codebase จริง, route name ทั้งหมด, UI implementation completeness, Owner-frozen sitemap
- **ห้าม:** นำ 20-page proposal ไปอ้างว่าเป็น 20 หน้า Live หรือใช้เป็นอำนาจแก้โค้ด / push / deploy โดยไม่ตรวจ repository และ authorization

## 8. Next action ที่ใช้ได้จริง

1. Read-only inspect local repository: `routes`, Views/Blade, Livewire components, navigation, auth, current Gantt implementation
2. ทำ **route inventory** ที่มีหลักฐานจริงเทียบกับ 20-page proposal
3. ส่ง ATHENA ออก design spec ของหน้า `EXE-01` และ `SCH-01` ที่ 1920×1080 ก่อน รวม empty/loading/error states, 3D fallback และ accessibility
4. ให้ RIN แยก proposal/approved/implementation authorization ตาม canonical Roadmap
5. ให้ HERMES พัฒนาต้นแบบเฉพาะที่ได้รับอนุญาต แล้ว THEMIS ตรวจอิสระ และ MIRA checkpoint Project Brain

---

**สรุป:** `7 MVP modules` ≠ `4 P2 UI areas` ≠ `20 proposed screens`  
**จำนวนที่เคย freeze เป็นหน้าเว็บจริง:** `UNKNOWN / NOT CONFIRMED`  
**จำนวนหน้าที่เสนอในไฟล์นี้สำหรับทำ Design Flow:** `20`
