# Implementation Plan - Phase 4 เสาหลัก 3: Field Photo Evidence & Mobile Inspection Attachment (Daily Log)

## Problem Statement
Site engineers and foremen on the construction site require a quick, reliable mobile workflow to capture real-time field photo evidence directly from mobile devices, bind photos to specific WBS tasks or site constraints, stamp GPS and timestamp telemetry watermarks, and review progress/inspection photos in an on-site gallery.

## Architecture & Design
1. **Database Schema Extension**:
   - Migration `2026_11_07_000100_add_site_inspection_metadata_to_evidence_documents.php` adding:
     - `wbs_task_id` (nullable, index, fk to wbs_tasks)
     - `constraint_id` (nullable, index, fk to project_constraints)
     - `caption` (nullable string)
     - `gps_latitude` (nullable decimal 10, 7)
     - `gps_longitude` (nullable decimal 10, 7)
     - `captured_at` (nullable timestamp)
     - `metadata` (nullable json)
2. **Watermarking Service**:
   - `App\Services\SitePhotoWatermarkService`:
     - Applies physical watermark banner using PHP GD (Project ID/code, timestamp, GPS coordinates, task identifier, inspector name).
     - Calculates SHA-256 hash of watermarked image.
     - Stores file into private `evidence` disk.
     - Creates immutable `EvidenceDocument` record.
     - Emits `evidence.site_photo_uploaded` audit event.
3. **Evidence Preview Controller**:
   - `App\Http\Controllers\EvidencePreviewController`:
     - Validates project authorization & evidence policy.
     - Serves inline image stream (`Content-Disposition: inline`) for browser rendering.
4. **Livewire Component Upgrade**:
   - `SiteSupervisorQuickCaptureView`:
     - Enables `WithFileUploads`.
     - Adds `PHOTOS` tab.
     - Handles mobile camera input (`capture="environment"`), GPS lat/lng bindings, task/constraint selection, caption, and category.
     - Provides inspection gallery grid, filter, and full-screen preview modal.
5. **Security & Invariants**:
   - Strict `Gate::authorize('view', $project)`.
   - Default-deny member authorization.
   - SHA-256 immutability on evidence disk.
   - Windows ACL granted for all created files.
