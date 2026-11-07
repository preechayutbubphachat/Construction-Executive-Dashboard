# Phase 4 — เสาหลัก 1: 3D Spatial Digital Twin & BIM Visualizer Implementation Plan

> **Goal:** Build an interactive 3D WebGL Construction Digital Twin at `/projects/{project}/3d-twin` using Three.js and OrbitControls, mapping real-time WBS task progress and site constraints onto 3D building elements with raycasting inspection, exploded views, and camera presets.
> **Architecture:** Pure deterministic backend projection service (`SiteSpatial3DProjectionService`) feeding a high-performance Three.js WebGL canvas with Livewire 3/Alpine HUD controls and drawer inspection.
> **Tech Stack:** Three.js, OrbitControls, Laravel 11, Livewire 3, Tailwind CSS, Alpine.js.
> **Execution Strategy:** Test-Driven Development (TDD) for projection and API endpoints, WebGL performance optimization (60 FPS, proper disposal, zero memory leak).

---

## 1. Global Constraints & Invariants
1. **Authorization Default-Deny:** `Gate::authorize('view', $project)` enforced on both the web page and the 3D data endpoint.
2. **Deterministic Projection:** The backend 3D spatial model is pure read-only with zero database mutations.
3. **WebGL Performance:** Must render at 60 FPS on standard devices, using instanced materials, requestAnimationFrame loop with delta time, and proper geometry disposal on component unmount.
4. **Graceful Fallback:** If WebGL is unsupported or disabled in the browser, display an accessible structural hierarchy fallback.

---

## 2. Architectural Blueprint & File Map

```
application/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── SiteSpatial3DController.php             # JSON API for 3D spatial data
│   ├── Livewire/
│   │   └── SiteSpatial3DView.php                       # Livewire HUD & Inspector Drawer
│   └── Services/
│       └── SiteSpatial3DProjectionService.php          # 3D spatial decomposition engine
├── resources/
│   ├── js/
│   │   └── site-3d-visualizer.js                       # Three.js Scene, Raycaster & OrbitControls
│   └── views/
│       ├── livewire/
│       │   └── site-spatial-3d-view.blade.php          # 3D Canvas container & HUD overlay
│       └── projects/
│           └── spatial-3d.blade.php                    # Fullscreen 3D twin layout
├── tests/
│   ├── Unit/
│   │   └── SiteSpatial3DProjectionServiceTest.php      # Unit tests for 3D spatial engine
│   └── Feature/
│       └── SiteSpatial3DSurfaceTest.php                # Feature tests for route & authorization
└── routes/
    └── web.php                                         # /projects/{project}/3d-twin & data endpoint
```

---

## 3. Bite-Sized Implementation Tasks

- [ ] **Task 1: 3D Spatial Projection Engine & Unit Tests (TDD)**
  - Write `tests/Unit/SiteSpatial3DProjectionServiceTest.php`.
  - Implement `App\Services\SiteSpatial3DProjectionService` (`CED_SITE_SPATIAL_3D_V1`):
    - Computes building floors/levels, zones, dimensions, positions, and assigns colors based on WBS task status and active constraints.
  - Verify unit tests pass.

- [ ] **Task 2: 3D Controller, Route & Feature Tests**
  - Implement `SiteSpatial3DController` returning JSON spatial topology.
  - Register `/projects/{project}/3d-twin` and `/projects/{project}/3d-twin/data` in `routes/web.php`.
  - Write `tests/Feature/SiteSpatial3DSurfaceTest.php` asserting authorization and JSON schema.
  - Verify feature tests pass.

- [ ] **Task 3: Three.js 3D WebGL Engine & HUD Component**
  - Create `resources/js/site-3d-visualizer.js`:
    - Scene setup with realistic architectural lighting (Sunlight, Ambient, Shadow).
    - Procedural 3D Building Generator (Grid, Foundation, Slabs, Columns, Roof).
    - PBR materials (Frosted glass for planned, Solid concrete for completed, Glowing pulse for blocked).
    - OrbitControls with damping and angle limits.
    - Raycaster click interaction dispatching element selection to HUD drawer.
    - Exploded view slider animation (vertical expansion of levels).
    - Preset camera buttons (Isometric, Top-down Site Plan, Front Elevation).
  - Implement `SiteSpatial3DView` Livewire component and Blade view.
  - Update `vite.config.js` and build assets.

- [ ] **Task 4: Cross-View Integration & Navigation**
  - Add "3D Spatial Twin 🌐" navigation links to Portfolio, Schedule, Lookahead, Constraints, Report, and Site Supervisor views.

- [ ] **Task 5: Verification & Code Style**
  - Run Laravel Pint.
  - Run entire PHPUnit test suite.
  - Update memory bank (`active_context.md`).
  - Commit all changes to Git.
