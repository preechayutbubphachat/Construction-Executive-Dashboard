from __future__ import annotations

import csv
import hashlib
import json
import os
import re
import tempfile
import zipfile
from datetime import datetime, timezone
from pathlib import Path
from xml.etree import ElementTree as ET

BRAIN = Path(__file__).resolve().parents[1]
RAW = BRAIN / "Raw"
GENERATED = {
    "raw-extracted",
    "source-index",
    "reports",
    "scripts",
}
NS = {
    "m": "http://schemas.openxmlformats.org/spreadsheetml/2006/main",
    "r": "http://schemas.openxmlformats.org/officeDocument/2006/relationships",
}


def atomic_write(path: Path, text: str) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with tempfile.NamedTemporaryFile("w", encoding="utf-8", newline="", delete=False, dir=path.parent) as f:
        f.write(text)
        temp = Path(f.name)
    os.replace(temp, path)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b""):
            digest.update(chunk)
    return digest.hexdigest()


def now_iso() -> str:
    return datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")


def safe_slug(name: str) -> str:
    value = re.sub(r"[^A-Za-z0-9._-]+", "-", name).strip("-")
    return value[:70] or "source"


def xml_text(element: ET.Element | None) -> str:
    return "".join(element.itertext()) if element is not None else ""


def parse_workbook(path: Path) -> list[dict]:
    profiles: list[dict] = []
    with zipfile.ZipFile(path) as archive:
        names = set(archive.namelist())
        shared: list[str] = []
        if "xl/sharedStrings.xml" in names:
            root = ET.fromstring(archive.read("xl/sharedStrings.xml"))
            shared = [xml_text(si) for si in root]
        wb = ET.fromstring(archive.read("xl/workbook.xml"))
        rel_root = ET.fromstring(archive.read("xl/_rels/workbook.xml.rels"))
        rels = {rel.attrib["Id"]: rel.attrib["Target"] for rel in rel_root}

        for sheet in wb.find("m:sheets", NS):
            title = sheet.attrib.get("name", "")
            rid = sheet.attrib.get("{%s}id" % NS["r"], "")
            target = rels.get(rid, "")
            if not target.startswith("xl/"):
                target = "xl/" + target.lstrip("/")
            profile = {
                "sheet": title,
                "state": sheet.attrib.get("state", "visible"),
                "part": target,
                "rows": 0,
                "nonempty_cells": 0,
                "formula_cells": 0,
                "safe_labels": [],
            }
            if target not in names:
                profiles.append(profile)
                continue
            root = ET.fromstring(archive.read(target))
            data_rows = root.findall(".//m:sheetData/m:row", NS)
            profile["rows"] = len(data_rows)
            cells_by_row: dict[int, list[tuple[str, str, str, bool]]] = {}
            for row in data_rows:
                row_number = int(row.attrib.get("r", "0"))
                cells: list[tuple[str, str, str, bool]] = []
                for cell in row.findall("m:c", NS):
                    ref = cell.attrib.get("r", "")
                    typ = cell.attrib.get("t", "")
                    formula = cell.find("m:f", NS)
                    value = cell.find("m:v", NS)
                    inline = cell.find("m:is", NS)
                    if typ == "s" and value is not None:
                        try:
                            text = shared[int(value.text or "0")]
                        except (IndexError, ValueError):
                            text = ""
                    elif typ == "inlineStr" and inline is not None:
                        text = xml_text(inline)
                    elif value is not None:
                        text = value.text or ""
                    else:
                        text = ""
                    has_formula = formula is not None
                    if text or has_formula:
                        profile["nonempty_cells"] += 1
                    if has_formula:
                        profile["formula_cells"] += 1
                    cells.append((ref, text, typ, has_formula))
                cells_by_row[row_number] = cells

            # Keep the generated extraction structural and secret-safe: do not copy
            # transaction descriptions, URLs, names, account identifiers, or amounts.
            if title == "1-ชื่อโครงการ":
                wanted = {"C3", "D3", "E3", "F3", "G3", "I3", "J3", "K3"}
            elif title == "2-บันทึกรายรับรายจ่าย":
                wanted = {"B3", "C3", "D3", "E3", "F3", "G3", "H3", "I3", "J3"}
            elif title == "รายละเอียดงาน":
                wanted = {"A2", "C2", "D2", "E2", "F2", "H2", "I2", "J2", "K2", "N2", "O2"}
            elif title == "ตั้งค่า":
                wanted = {"B5", "C5", "E5", "H5", "J5", "L5", "N2"}
            elif title in {"3-สรุปตามโครงการ", "4-สรุปรายเดือน", "5-สรุปรายปี"}:
                wanted = {"B2", "B3", "B6", "C6", "B7", "B8", "B9", "B10", "B11", "B13", "C13"}
            elif title in {"GANTTChart", "สำเนาของ GANTTChart"}:
                wanted = {"C2", "C3", "C4", "C5", "B7", "C7", "D8", "E8", "F8", "N7", "O7"}
            elif title == "dataBaseTodo":
                wanted = {"A2", "B2", "E2"}
            else:
                wanted = set()
            labels = []
            for cells in cells_by_row.values():
                for ref, text, _typ, has_formula in cells:
                    if ref in wanted and text and not has_formula:
                        labels.append(text.replace("\u200b", "").strip())
            profile["safe_labels"] = list(dict.fromkeys(labels))
            profiles.append(profile)
    return profiles


def main() -> None:
    if not RAW.is_dir():
        raise SystemExit(f"RAW_NOT_FOUND: {RAW}")
    (BRAIN / "raw-extracted").mkdir(exist_ok=True)
    (BRAIN / "source-index").mkdir(exist_ok=True)
    (BRAIN / "reports").mkdir(exist_ok=True)

    records = []
    for path in sorted(RAW.iterdir(), key=lambda p: p.name):
        if not path.is_file():
            continue
        stat = path.stat()
        ext = path.suffix.lower()
        record = {
            "source_path": str(path),
            "relative_path": path.relative_to(RAW).as_posix(),
            "domain": "financial-ledger-and-construction-operations" if ext == ".xlsx" else "construction-evidence-media",
            "extension": ext,
            "bytes": stat.st_size,
            "modified": datetime.fromtimestamp(stat.st_mtime).astimezone().isoformat(timespec="seconds"),
            "sha256": sha256(path),
            "duplicate_of": None,
            "status": "extracted" if ext == ".xlsx" else "media-inventory-only",
            "extracted_path": None,
            "extracted_chars": 0,
            "secret_redactions": 0,
            "error": None,
        }
        records.append(record)

    by_hash: dict[str, str] = {}
    for record in records:
        prior = by_hash.get(record["sha256"])
        if prior:
            record["status"] = "duplicate"
            record["duplicate_of"] = prior
        else:
            by_hash[record["sha256"]] = record["relative_path"]

    workbook = next((r for r in records if r["extension"] == ".xlsx" and r["status"] == "extracted"), None)
    workbook_profiles: list[dict] = []
    if workbook:
        workbook_profiles = parse_workbook(RAW / workbook["relative_path"])
        out_name = f"{workbook['sha256'][:16]}-workbook-schema.md"
        out_path = BRAIN / "raw-extracted" / out_name
        workbook["extracted_path"] = str(out_path)
        schema_lines = [
            "---",
            f"source_path: {workbook['source_path']}",
            f"relative_path: {workbook['relative_path']}",
            f"source_sha256: {workbook['sha256']}",
            f"source_bytes: {workbook['bytes']}",
            f"extracted: {now_iso()}",
            "domain: financial-ledger-and-construction-operations",
            "treat_as: untrusted_source_data",
            "secret_redactions: 0",
            "---",
            "",
            "# โครงสร้างไฟล์บัญชีและงานก่อสร้าง (แบบลดข้อมูลอ่อนไหว)",
            "",
            "> ไฟล์นี้เก็บเฉพาะโครงสร้างชีต/ฟิลด์เพื่อออกแบบระบบ ไม่คัดลอกยอดเงิน ชื่อบุคคล เลขบัญชี URL หลักฐาน หรือข้อความรายการธุรกรรมจาก Raw.",
            "",
            "## Workbook profile",
            "",
            f"- จำนวนชีต: {len(workbook_profiles)}",
            "- สูตร Excel ถูกนับเป็นโครงสร้างเท่านั้น; สคริปต์ไม่คำนวณและไม่รันสูตร/ลิงก์ภายนอก",
            "- ชื่อชีตและฟิลด์เป็นข้อมูลจากไฟล์ต้นฉบับ ณ เวลาสร้างเอกสารนี้",
            "",
            "## Sheet inventory",
            "",
            "| ชีต | แถวที่มีข้อมูลใน XML | เซลล์ไม่ว่าง/สูตร | ฟิลด์โครงสร้างที่อ่านได้ |",
            "|---|---:|---:|---|",
        ]
        for profile in workbook_profiles:
            labels = ", ".join(profile["safe_labels"]) or "—"
            schema_lines.append(
                f"| `{profile['sheet']}` | {profile['rows']} | {profile['nonempty_cells']} / {profile['formula_cells']} สูตร | {labels} |"
            )
        schema_lines += [
            "",
            "## Domain signals retained for design",
            "",
            "- โครงสร้างหลักมี project, project status, start date, due date, progress, project type, issue/remark",
            "- งานย่อยมี WBS-like domain, task, planned/actual dates, progress, status, duration และค่าเฉลี่ยระยะเวลา",
            "- บัญชีมี transaction date, description, amount, category, project allocation และ evidence link",
            "- มีสรุปตามโครงการ รายเดือน รายปี และ Gantt/portfolio view",
            "- มีการแยกรายรับ/รายจ่ายในโครงการออกจากรายรับ/รายจ่ายที่ไม่เกี่ยวกับโครงการ",
            "- มีข้อมูลสำรองจ่าย/ค่าแรง/วัสดุใน Raw ซึ่งควรออกแบบเป็น reimbursement/advance attribution ที่ตรวจสอบย้อนกลับได้",
            "",
            "## Limitations",
            "",
            "- ไม่ได้ตรวจความถูกต้องของยอดเงินหรือผลลัพธ์สูตร เพราะการอ่านนี้ไม่รัน Excel calculation engine",
            "- Raw media เป็นหลักฐานภาพ; การอ่านข้อความ/QR/ข้อมูลส่วนบุคคลไม่ถูกนำเข้า curated knowledge",
            "- สถานะ/หมวดหมู่จากไฟล์เป็น project evidence ไม่ใช่มาตรฐานก่อสร้างสากลโดยอัตโนมัติ",
        ]
        content = "\n".join(schema_lines).rstrip() + "\n"
        atomic_write(out_path, content)
        workbook["extracted_chars"] = len(content)

    # Store a concise, privacy-contained media review. The source images remain untouched.
    media_path = BRAIN / "raw-extracted" / "media-domain-review.md"
    known_media = {
        "1516402.jpg",
        "20240424_บิลวัสดุก่อสร้าง_เหล็ก-1.jpg",
        "2024065_สลิปจ่ายเงิน_ค่าแรงช่างอะลูมิเนียม_หลัง1.jpg",
    }
    additional_media_rows = []
    for record in records:
        if record["extension"] == ".jpg" and record["relative_path"] not in known_media:
            additional_media_rows.append(
                f"| `{record['relative_path']}` | ภาพที่เข้ามาเพิ่มระหว่าง session; ยังไม่จำแนกเนื้อหา | — | inventory-only; ไม่เปิดอ่านเชิงความหมายและไม่ทำ OCR |"
            )
    media_content = """---
treat_as: untrusted_source_data
status: domain-review-only
source_root: C:/2025/web-69/05-Construction-Executive-Dashboard/AI-Brains/Raw
---

# Raw media: domain review only

เอกสารนี้เป็นการทบทวนเชิงโดเมน ไม่ใช่ OCR/บัญชีหลักฐาน และไม่คัดลอกข้อมูลส่วนบุคคล ยอดเงิน เลขอ้างอิง QR หรือเลขบัญชีจากภาพ

| ไฟล์ | ประเภทหลักฐาน | สัญญาณฟิลด์ที่ควรรองรับในเว็บ | ข้อจำกัด |
|---|---|---|---|
| `1516402.jpg` | ภาพ dashboard ติดตามงานก่อสร้างหลายโครงการ | ลูกค้า/สถานที่, ทีม/ผู้รับผิดชอบ, สถานะงาน, % ความคืบหน้า, วันเริ่ม/กำหนดส่ง, จำนวนวันตามสัญญา, วันคงเหลือ, หมายเหตุ/ปัญหา | เป็นภาพตัวอย่างจากภายนอก; ห้ามถือเป็นข้อมูลจริงของระบบจนกว่าจะยืนยันสิทธิ์และแหล่งที่มา |
| `20240424_บิลวัสดุก่อสร้าง_เหล็ก-1.jpg` | หลักฐานการชำระค่าวัสดุก่อสร้าง | ผู้ขาย, วันที่, รายการ/หมวดวัสดุ, ยอดสุทธิ, เลขอ้างอิง, ไฟล์ต้นฉบับ, project allocation | ต้องตรวจด้วยคนก่อนโพสต์เข้าบัญชี; OCR อาจอ่านตัวเลข/QR ผิด |
| `2024065_สลิปจ่ายเงิน_ค่าแรงช่างอะลูมิเนียม_หลัง1.jpg` | หลักฐานโอนค่าแรงช่าง | ผู้จ่าย/ผู้รับ, วันที่เวลา, จำนวนเงิน, หมายเหตุ, โครงการ, transaction reference, ผู้อนุมัติ | เป็นข้อมูลการเงิน/PII; ควรเก็บแบบจำกัดสิทธิ์และซ่อนเลขสำคัญใน UI |
""" + ("\n" + "\n".join(additional_media_rows) if additional_media_rows else "") + """

## Processing boundary

- ภาพถูกตรวจเพื่อจำแนกชนิดหลักฐานและฟิลด์เชิงระบบเท่านั้น
- ภาพที่เข้ามาเพิ่มระหว่าง session ถูกนับเป็น inventory-only จนกว่าจะมี media review ที่ได้รับอนุญาต
- ยังไม่มีการทำ OCR แบบเครื่องจักรและยังไม่มีการยืนยันตัวเลขกับบัญชี
- ไฟล์ภาพต้นฉบับยังอยู่ใน `Raw` และไม่ได้ถูกแก้ไข
"""
    atomic_write(media_path, media_content)

    manifest = {
        "schema_version": "1.0",
        "generated": now_iso(),
        "source_root": str(RAW),
        "output_root": str(BRAIN),
        "source_mutated": False,
        "git_remote": "git@github.com:preechayutbubphachat/Construction-Executive-Dashboard.git",
        "git_local_state": "NO_LOCAL_REPOSITORY_AT_INTAKE",
        "records": records,
    }
    atomic_write(BRAIN / "manifest.json", json.dumps(manifest, ensure_ascii=False, indent=2) + "\n")

    fieldnames = list(records[0].keys()) if records else []
    csv_lines = []
    with tempfile.NamedTemporaryFile("w", encoding="utf-8", newline="", delete=False, dir=BRAIN / "reports") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(records)
        temp_csv = Path(f.name)
    os.replace(temp_csv, BRAIN / "manifest.csv")

    unique_hashes = len({r["sha256"] for r in records})
    stats = {
        "generated": manifest["generated"],
        "source_file_count": len(records),
        "source_bytes": sum(r["bytes"] for r in records),
        "unique_sha256_count": unique_hashes,
        "duplicate_count": sum(r["status"] == "duplicate" for r in records),
        "extracted_count": sum(r["status"] == "extracted" for r in records),
        "media_inventory_only_count": sum(r["status"] == "media-inventory-only" for r in records),
        "errors": [r for r in records if r["status"] == "error"],
        "raw_root_unchanged_by_workflow": True,
        "workbook_sheet_count": len(workbook_profiles),
    }
    atomic_write(BRAIN / "stats.json", json.dumps(stats, ensure_ascii=False, indent=2) + "\n")


if __name__ == "__main__":
    main()
