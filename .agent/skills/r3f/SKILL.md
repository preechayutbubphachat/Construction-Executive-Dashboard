---
name: r3f
description: Comprehensive React Three Fiber (R3F) and Three.js 3D graphics, creative design, and animation skill. Use when creating 3D scenes, stadium/arena court visualizers, particle systems, broadcast graphics, shaders, camera controls, or optimizing 3D WebGL performance with @react-three/fiber, @react-three/drei, and Three.js.
license: MIT
metadata:
  author: Antigravity 3D Design & Engineering
  version: 2.0.0
---

# React Three Fiber & Three.js 3D Design Superpower

## Overview

This skill equips the agent with production-grade 3D creative design, WebGL performance engineering, and animation expertise using **React Three Fiber (R3F)**, **@react-three/drei**, and **Three.js**.

```
┌───────────────────────────────────────────────────────────────────────────┐
│                    The React Three Fiber 3D Stack                         │
├───────────────────────────────────────────────────────────────────────────┤
│  Layer 1: Scene & Canvas Setup (Canvas, Camera, DPR, Suspense, Error)     │
│  Layer 2: Declarative 3D Objects (Meshes, Geometries, Materials, Groups)  │
│  Layer 3: Shaders, Lighting & Atmosphere (PBR, Spotlights, Postprocessing) │
│  Layer 4: High-Performance Animation (useFrame, Delta-time, Lerp, Springs) │
│  Layer 5: Arena Sports Visualization (3D Courts, Shot Charts, Ball Traj)   │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## The 7 Inviolable Laws of React Three Fiber

1. **NEVER call `setState` or trigger React re-renders inside `useFrame`**:
   - ❌ Bad: `useFrame(() => setAngle(a => a + 0.01))` (Destroys framerate, causes 60-120 React re-renders/sec).
   - ✅ Good: Mutate ref properties directly: `useFrame((state, delta) => { meshRef.current.rotation.y += delta; })`.

2. **Always scale motion by frame delta (`delta`)**:
   - Never write `ref.current.position.x += 0.1`. Always multiply by delta: `ref.current.position.x += speed * delta` so animations remain identical on 60Hz, 120Hz, and 240Hz screens.

3. **Wrap async asset loaders in `<Suspense>`**:
   - Always wrap `useGLTF`, `useTexture`, or font loaders inside React `<Suspense fallback={<Loader />}>`.
   - Preload models outside components: `useGLTF.preload('/models/basketball-court.glb')`.

4. **Instancing for repetitive objects (`InstancedMesh`)**:
   - For stadium crowds, arena floor tiles, particles, or shot-chart markers, NEVER render hundreds of separate `<mesh>` elements. Use `<Instances>` from Drei or Three.js `<instancedMesh>`.

5. **Proper Memory Disposal**:
   - Geometries, materials, render targets, and textures must be disposed of when unmounting. R3F disposes automatically for declarative elements, but imperative Three.js instances need explicit `.dispose()`.

6. **Clamp Device Pixel Ratio (DPR)**:
   - Always set `dpr={[1, 2]}` on `<Canvas>` to prevent 4K retina displays from tanking GPU fill-rate.

7. **Arena Sports 3D Specifics (Basketball Broadcast Standards)**:
   - Court dimensions standard: FIBA/NBA 28m x 15m (or 94ft x 50ft).
   - Use soft shadow mapping (`contactShadows` or `accumulativeShadows`) under players and hoops.
   - Shot-clock / LED scoreboard boards: Emissive materials (`meshStandardMaterial emissive="#ff3300" emissiveIntensity={2}`) with Bloom post-processing.

---

## Detailed Rules & Playbook Reference
See the bundled `rules/` and `modules/` directories for specialized guidance on:
- `rules/perf-never-set-state-in-useframe.md`
- `rules/drei-use-gltf.md`
- `rules/geometry-instanced-mesh.md`
- `rules/lighting-shadows-advanced.md`
- `rules/postprocessing-optimization.md`
- `modules/r3f-shaders.md`
- `modules/r3f-physics.md`
- `modules/r3f-animation.md`
