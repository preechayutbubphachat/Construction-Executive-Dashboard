import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

export class SiteSpatial3DVisualizer {
    constructor(container, dataUrl, options = {}) {
        this.container = container;
        this.dataUrl = dataUrl;
        this.options = options;
        this.meshes = new Map();
        this.blockedMeshes = [];
        this.hoveredMesh = null;
        this.selectedMesh = null;
        this.explodedGap = 0.0;
        this.filterMode = 'ALL';
        this.animationFrameId = null;

        this.init();
    }

    async init() {
        const width = this.container.clientWidth || window.innerWidth;
        const height = this.container.clientHeight || window.innerHeight;

        // Scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0x020617); // Slate-950

        // Camera
        this.camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        this.camera.position.set(38, 30, 38);

        // Renderer
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.shadowMap.enabled = true;
        this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.1;

        this.container.innerHTML = '';
        this.container.appendChild(this.renderer.domElement);

        // OrbitControls
        this.controls = new OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.maxPolarAngle = Math.PI / 2.05; // Don't go below ground
        this.controls.target.set(0, 10, 0);

        // Lighting
        this.setupLights();

        // Ground & Site Grid
        this.setupEnvironment();

        // Raycasting
        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();

        this.setupEventListeners();

        // Load 3D Data
        await this.loadData();

        // Render Loop
        this.clock = new THREE.Clock();
        this.animate = this.animate.bind(this);
        this.animate();
    }

    setupLights() {
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.7);
        this.scene.add(ambientLight);

        const sunLight = new THREE.DirectionalLight(0xfffbeb, 1.6);
        sunLight.position.set(40, 70, 30);
        sunLight.castShadow = true;
        sunLight.shadow.mapSize.width = 2048;
        sunLight.shadow.mapSize.height = 2048;
        sunLight.shadow.camera.near = 0.5;
        sunLight.shadow.camera.far = 200;
        const d = 35;
        sunLight.shadow.camera.left = -d;
        sunLight.shadow.camera.right = d;
        sunLight.shadow.camera.top = d;
        sunLight.shadow.camera.bottom = -d;
        this.scene.add(sunLight);

        const hemiLight = new THREE.HemisphereLight(0x38bdf8, 0x0f172a, 0.4);
        this.scene.add(hemiLight);
    }

    setupEnvironment() {
        // Architectural grid
        const gridHelper = new THREE.GridHelper(90, 45, 0x334155, 0x1e293b);
        gridHelper.position.y = -2.01;
        this.scene.add(gridHelper);

        // Construction site boundary circle
        const sitePadGeo = new THREE.CylinderGeometry(28, 28, 0.2, 48);
        const sitePadMat = new THREE.MeshStandardMaterial({
            color: 0x0f172a,
            roughness: 0.9,
            metalness: 0.1,
        });
        const sitePad = new THREE.Mesh(sitePadGeo, sitePadMat);
        sitePad.position.y = -2.1;
        sitePad.receiveShadow = true;
        this.scene.add(sitePad);
    }

    async loadData() {
        try {
            const response = await fetch(this.dataUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) return;

            const data = await response.json();
            this.buildModel(data);
        } catch (e) {
            console.error('Failed to load 3D spatial data:', e);
        }
    }

    buildModel(data) {
        if (!data || !data.elements) return;

        this.elementsData = data.elements;
        this.buildingGroup = new THREE.Group();

        data.elements.forEach(elem => {
            const dims = elem.dimensions;
            const pos = elem.position;

            const geometry = new THREE.BoxGeometry(dims.width, dims.height, dims.depth);

            // Material
            const material = new THREE.MeshStandardMaterial({
                color: new THREE.Color(elem.color_hex),
                emissive: new THREE.Color(elem.emissive_hex || 0x000000),
                emissiveIntensity: elem.is_blocked ? 0.6 : 0.2,
                roughness: 0.35,
                metalness: 0.2,
                transparent: elem.opacity < 1.0,
                opacity: elem.opacity,
            });

            const mesh = new THREE.Mesh(geometry, material);
            mesh.position.set(pos.x, pos.y, pos.z);
            mesh.castShadow = true;
            mesh.receiveShadow = true;
            mesh.userData = elem;
            mesh.userData.baseY = pos.y;

            // Wireframe edges for high-precision architectural blueprint look
            const edges = new THREE.EdgesGeometry(geometry);
            const line = new THREE.LineSegments(
                edges,
                new THREE.LineBasicMaterial({ color: 0x94a3b8, transparent: true, opacity: 0.4 })
            );
            mesh.add(line);

            this.buildingGroup.add(mesh);
            this.meshes.set(elem.id, mesh);

            if (elem.is_blocked) {
                this.blockedMeshes.push(mesh);
            }
        });

        this.scene.add(this.buildingGroup);

        if (data.camera_bounds && data.camera_bounds.center) {
            const c = data.camera_bounds.center;
            this.controls.target.set(c[0], c[1], c[2]);
        }
    }

    setupEventListeners() {
        const dom = this.renderer.domElement;

        dom.addEventListener('pointermove', (event) => {
            const rect = dom.getBoundingClientRect();
            this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            this.checkHover();
        });

        dom.addEventListener('click', () => {
            if (this.hoveredMesh) {
                this.selectMesh(this.hoveredMesh);
            }
        });

        window.addEventListener('resize', () => {
            const w = this.container.clientWidth;
            const h = this.container.clientHeight;
            this.camera.aspect = w / h;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(w, h);
        });

        // Listen for Livewire custom events
        window.addEventListener('spatial3d-filter-changed', (e) => {
            this.setFilter(e.detail.mode);
        });

        window.addEventListener('spatial3d-exploded-changed', (e) => {
            this.setExplodedGap(e.detail.gap);
        });

        window.addEventListener('spatial3d-camera-changed', (e) => {
            this.setCameraPreset(e.detail.preset);
        });
    }

    checkHover() {
        this.raycaster.setFromCamera(this.mouse, this.camera);
        const meshArray = Array.from(this.meshes.values());
        const intersects = this.raycaster.intersectObjects(meshArray);

        if (intersects.length > 0) {
            const hit = intersects[0].object;
            if (this.hoveredMesh !== hit) {
                this.resetHover();
                this.hoveredMesh = hit;
                this.hoveredMesh.material.emissiveIntensity = 0.9;
                this.container.style.cursor = 'pointer';
            }
        } else {
            this.resetHover();
            this.container.style.cursor = 'default';
        }
    }

    resetHover() {
        if (this.hoveredMesh && this.hoveredMesh !== this.selectedMesh) {
            const isBlocked = this.hoveredMesh.userData.is_blocked;
            this.hoveredMesh.material.emissiveIntensity = isBlocked ? 0.6 : 0.2;
            this.hoveredMesh = null;
        }
    }

    selectMesh(mesh) {
        this.selectedMesh = mesh;
        const elemId = mesh.userData.id;

        // Dispatch selection to Livewire component
        if (window.Livewire) {
            const lw = window.Livewire.find(
                document.querySelector('[wire\\:id]')?.getAttribute('wire:id')
            );
            if (lw) {
                lw.call('selectElement', elemId);
            }
        }
    }

    setExplodedGap(gap) {
        this.explodedGap = gap;
        this.meshes.forEach(mesh => {
            const lvl = mesh.userData.level_index || 0;
            const baseY = mesh.userData.baseY || 0;
            mesh.position.y = baseY + (lvl * gap);
        });
    }

    setFilter(mode) {
        this.filterMode = mode;
        this.meshes.forEach(mesh => {
            const isBlocked = mesh.userData.is_blocked;
            if (mode === 'BLOCKED_ONLY') {
                mesh.visible = isBlocked;
            } else {
                mesh.visible = true;
            }
        });
    }

    setCameraPreset(preset) {
        const target = this.controls.target;
        if (preset === 'TOP_DOWN') {
            this.camera.position.set(target.x, target.y + 60, target.z + 0.1);
        } else if (preset === 'FRONT') {
            this.camera.position.set(target.x, target.y + 5, target.z + 55);
        } else {
            // ISOMETRIC
            this.camera.position.set(target.x + 38, target.y + 25, target.z + 38);
        }
        this.camera.lookAt(target);
    }

    animate() {
        this.animationFrameId = requestAnimationFrame(this.animate);

        const time = this.clock.getElapsedTime();

        // Pulse blocked elements with subtle red emission wave
        if (this.blockedMeshes.length > 0) {
            const pulse = (Math.sin(time * 3.5) + 1.0) / 2.0; // 0..1
            this.blockedMeshes.forEach(m => {
                if (m !== this.hoveredMesh) {
                    m.material.emissiveIntensity = 0.3 + (pulse * 0.6);
                }
            });
        }

        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }

    destroy() {
        if (this.animationFrameId) {
            cancelAnimationFrame(this.animationFrameId);
        }
        this.meshes.forEach(mesh => {
            mesh.geometry.dispose();
            mesh.material.dispose();
        });
        this.renderer.dispose();
    }
}

// Global initialization helper for Alpine.js
window.initSiteSpatial3D = function(containerId, dataUrl) {
    const el = document.getElementById(containerId);
    if (!el) return null;
    return new SiteSpatial3DVisualizer(el, dataUrl);
};
