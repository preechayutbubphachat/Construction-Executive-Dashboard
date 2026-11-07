---
name: liquid-glass
description: Apple VisionOS-inspired Liquid Glass and WebGL glassmorphism design superpower. Use when creating realistic glass refraction, chromatic aberration, multi-layer blur, frosted liquid panels, glossy buttons, or sports broadcast glass UI overlays.
license: MIT
metadata:
  author: Antigravity Creative Frontend Engineering
  version: 2.0.0
---

# Liquid Glass JS & WebGL Glassmorphism Superpower

## Overview

The **Liquid Glass JS** superpower brings Apple VisionOS-inspired, television-grade liquid glass effects to web applications. It transforms flat, opaque interfaces into luminous, optical glass surfaces featuring real-time refraction, multi-layer blur, chromatic aberration, and specular light highlights.

```
┌───────────────────────────────────────────────────────────────────────────┐
│                    The Liquid Glass Optical Stack                         │
├───────────────────────────────────────────────────────────────────────────┤
│  Layer 1: Background Scene Capture (DOM / Canvas / WebGL Framebuffer)     │
│  Layer 2: UV Coordinate Distortion (Normal Map, Surface Curvature, Noise) │
│  Layer 3: Chromatic Aberration & Dispersion (Split RGB Channel Sampling)  │
│  Layer 4: Multi-pass Gaussian & Dual-Kawase Blur Filter                   │
│  Layer 5: Surface Specular Highlights, Rim Lighting & Border Glow         │
└───────────────────────────────────────────────────────────────────────────┘
```

---

## 3 Implementation Paradigms

### 1. WebGL DOM Engine (via `liquid-glass-js`)
For HTML elements (Cards, Buttons, Headers) needing real-time refractive distortion of underlying HTML content:

```javascript
import { Container, Button } from './lib/liquid-glass-js';

// Create a glass container with real-time refraction
const glassContainer = new Container({
  shape: 'rounded',       // 'rounded' | 'circle' | 'pill'
  radius: 24,
  blur: 16,
  refraction: 0.25,
  rimLight: true,
  tint: 'rgba(15, 23, 42, 0.45)', // Dark stadium tint
});

document.body.appendChild(glassContainer.element);
```

### 2. React Three Fiber (R3F) 3D Liquid Glass
When rendering true 3D meshes, floating stadium shields, or 3D scoreboard prisms using `MeshTransmissionMaterial` from `@react-three/drei`:

```tsx
import { MeshTransmissionMaterial } from '@react-three/drei';

export function LiquidGlassScorecard() {
  return (
    <mesh position={[0, 0, 0]}>
      <boxGeometry args={[4, 2, 0.2]} />
      <MeshTransmissionMaterial
        backside
        samples={16}
        resolution={512}
        transmission={0.95}
        roughness={0.12}
        thickness={0.6}
        ior={1.5}
        chromaticAberration={0.06}
        anisotropy={0.1}
        distortion={0.3}
        distortionScale={0.5}
        temporalResolve={true}
        color="#06b6d4" // Stadium cyan tint
      />
    </mesh>
  );
}
```

### 3. Pure CSS Hardware-Accelerated Fallback
For ultra-low power devices or high-framerate sports score overlays where WebGL canvas capture is too heavy:

```css
/* High-Performance Liquid Glass CSS Utility */
.liquid-glass-surface {
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.08) 0%,
    rgba(255, 255, 255, 0.02) 100%
  );
  backdrop-filter: blur(20px) saturate(180%);
  -webkit-backdrop-filter: blur(20px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow: 
    0 8px 32px 0 rgba(0, 0, 0, 0.37),
    inset 0 1px 1px 0 rgba(255, 255, 255, 0.2),
    inset 0 -1px 1px 0 rgba(0, 0, 0, 0.4);
}
```

---

## Arena Sports UI Applications (Basketball Scoreboard)

1. **Floating Live Header**: Translucent frosted glass banner displaying game clock and period without obscuring active court action behind it.
2. **Pulse Status Badges**: Pill-shaped liquid glass badges for `กำลังแข่ง` (Live In-Progress) with glowing emerald inner refraction.
3. **Player Stat HUD Card**: High-contrast glass card with tabular monospace typography, ensuring numbers remain razor-sharp over shifting backgrounds.

---

## Critical Performance Invariants
1. **Debounce Canvas Resampling**: Avoid re-capturing DOM background on every frame. Cache static backgrounds and only resample when contents animate.
2. **Cap Resolution**: Keep refraction framebuffers at half or quarter resolution (`resolution={512}` or `scale={0.5}`) and apply Gaussian blur to smooth pixels.
3. **Contrast Guarantees (WCAG AA)**: Always pair frosted glass with a semi-transparent dark backing (`rgba(15, 23, 42, 0.75)`) so white and gold scoreboard digits never wash out.

---

## Bundled Source Files
The actual WebGL shader engine from `dashersw/liquid-glass-js` is bundled directly in the `lib/` subfolder:
- `lib/container.js` — WebGL container with multi-layer refraction and mask rendering
- `lib/button.js` — Interactive glass button component with ripple and hover refraction
- `lib/glass.css` — Essential positioning and optical layer styles
- `lib/README.md` — Complete API reference
