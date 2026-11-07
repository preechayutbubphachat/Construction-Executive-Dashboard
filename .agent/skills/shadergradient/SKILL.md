---
name: shadergradient
description: Fluid 3D animated mesh gradients and WebGL/GLSL shader design superpower. Use when creating animated background gradients, fluid stadium atmospheres, interactive 3D noise waves, or dynamic color transitions with Three.js, React Three Fiber, and GLSL.
license: MIT
metadata:
  author: Antigravity Creative Frontend & Shader Engineering
  version: 2.0.0
---

# Shader Gradient & 3D Mesh Gradient Superpower

## Overview

The **Shader Gradient Superpower** enables the creation of fluid, television-grade 3D mesh gradients and dynamic WebGL background shaders. Unlike static CSS linear gradients or CPU-heavy canvas animations, shader gradients run entirely on the GPU via custom GLSL vertex and fragment shaders, delivering hypnotic 60-120 FPS waves with zero dropped frames.

```
┌───────────────────────────────────────────────────────────────────────────┐
│                    The Shader Gradient Architecture                       │
├───────────────────────────────────────────────────────────────────────────┤
│  Layer 1: Plane / Sphere 3D Mesh (Subdivided Vertex Grid 64x64 or 128x128)│
│  Layer 2: Vertex Shader (Simplex / Perlin Noise 3D Wave Displacement)     │
│  Layer 3: Fragment Shader (Multi-stop RGB Color Splines & Grain Texture)  │
│  Layer 4: Camera & Post-Processing (Tone Mapping, Dithering, Halftone)    │
│  Layer 5: Viewport Invalidation & Intersection Observer Optimization     │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## 3 Core Implementation Methods

### 1. Using `@shadergradient/react`
The official standard library for declarative React and Next.js applications:

```tsx
import { ShaderGradientCanvas, ShaderGradient } from '@shadergradient/react';

export function ArenaBackground() {
  return (
    <div className="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
      <ShaderGradientCanvas
        style={{ width: '100vw', height: '100vh' }}
        lazy={true}
        pixelDensity={1}
      >
        <ShaderGradient
          type="waterPlane"           // 'waterPlane' | 'plane' | 'sphere'
          animate="on"
          cAzimuthAngle={180}
          cPolarAngle={90}
          cDistance={3.6}
          cameraZoom={1}
          color1="#020617"             // Dark arena slate
          color2="#06b6d4"             // Stadium electric cyan
          color3="#f59e0b"             // Championship gold
          grain="on"
          grainBlending={0.2}
          speed={0.005}
          uStrength={1.5}
          uDensity={1.2}
        />
      </ShaderGradientCanvas>
    </div>
  );
}
```

### 2. Custom Lightweight GLSL Shader in React Three Fiber (R3F)
When you need full control without external package dependencies:

```tsx
import { useRef } from 'react';
import { useFrame } from '@react-three/fiber';
import * as THREE from 'three';

const vertexShader = `
  varying vec2 vUv;
  varying float vElevation;
  uniform float uTime;
  
  // Classic Perlin 3D Noise function
  // (Included in bundled shaders/perlin.glsl)
  
  void main() {
    vUv = uv;
    vec3 pos = position;
    float elevation = sin(pos.x * 2.0 + uTime * 0.5) * cos(pos.y * 2.0 + uTime * 0.5) * 0.3;
    pos.z += elevation;
    vElevation = elevation;
    gl_Position = projectionMatrix * modelViewMatrix * vec4(pos, 1.0);
  }
`;

const fragmentShader = `
  varying vec2 vUv;
  varying float vElevation;
  uniform vec3 uColor1;
  uniform vec3 uColor2;
  uniform vec3 uColor3;

  void main() {
    float mixStrength = (vElevation + 0.3) * 1.6;
    vec3 color = mix(uColor1, uColor2, smoothstep(0.0, 0.5, mixStrength));
    color = mix(color, uColor3, smoothstep(0.5, 1.0, mixStrength));
    gl_FragColor = vec4(color, 1.0);
  }
`;

export function FluidMeshGradient() {
  const meshRef = useRef<THREE.Mesh>(null!);
  const uniforms = useRef({
    uTime: { value: 0 },
    uColor1: { value: new THREE.Color('#020617') },
    uColor2: { value: new THREE.Color('#06b6d4') },
    uColor3: { value: new THREE.Color('#f59e0b') },
  });

  useFrame((_, delta) => {
    uniforms.current.uTime.value += delta;
  });

  return (
    <mesh ref={meshRef} rotation={[-Math.PI / 4, 0, 0]}>
      <planeGeometry args={[10, 10, 64, 64]} />
      <shaderMaterial
        vertexShader={vertexShader}
        fragmentShader={fragmentShader}
        uniforms={uniforms.current}
      />
    </mesh>
  );
}
```

### 3. CSS Animated Gradient Mesh (Zero-WebGL Fallback)
For battery-saver mode or minimal devices:

```css
.arena-mesh-gradient-css {
  background-color: #020617;
  background-image: 
    radial-gradient(at 10% 20%, rgba(6, 182, 212, 0.25) 0px, transparent 50%),
    radial-gradient(at 90% 10%, rgba(245, 158, 11, 0.20) 0px, transparent 50%),
    radial-gradient(at 50% 80%, rgba(239, 68, 68, 0.15) 0px, transparent 50%);
  filter: blur(40px);
  animation: meshFloat 12s ease-in-out infinite alternate;
}
```

---

## Arena Sports Color Palettes

| Theme Name | Primary (Canvas) | Secondary (Accent) | Highlight (Energy) | Best For |
| :--- | :--- | :--- | :--- | :--- |
| **Dark Arena Core** | `#020617` (Midnight) | `#06b6d4` (Cyan) | `#f59e0b` (Amber Gold) | Scoreboard Main & Operator Console |
| **Live Match Thriller** | `#050510` (Onyx) | `#10b981` (Pulse Emerald) | `#ef4444` (Shot Clock Red) | Active 4th Quarter Climax |
| **Championship Prestige**| `#09090b` (Zinc) | `#d97706` (Trophy Gold) | `#fde047` (Electric Yellow) | Tournament Bracket & Finals Podium |

---

## Critical Performance Invariants

1. **Pause When Offscreen**:
   - Always hook an `IntersectionObserver` or R3F `frameloop="demand"` when the canvas is hidden behind opaque panels or scrolled out of view.
2. **Cap Geometry Subdivision**:
   - Plane grid size should never exceed `64x64` on mobile or `128x128` on desktop. Higher densities produce visual diminishing returns while quadrupling vertex shader compute.
3. **Contrast Guarantee for Score Displays**:
   - The gradient must remain in the background with reduced opacity (`opacity-40` to `opacity-70`) or behind a dark scrim (`bg-slate-950/60`) so scoreboard digits and game clocks maintain WCAG AA 4.5:1 contrast at all times.
