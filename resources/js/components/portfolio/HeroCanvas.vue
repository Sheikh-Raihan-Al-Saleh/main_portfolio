<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Cursor-reactive particle field behind the hero.
 *
 * Loaded lazily by HeroSection so three.js lands in its own chunk. The caller
 * is responsible for deciding whether to mount this at all — it assumes motion
 * is permitted and only bails if WebGL itself is unavailable.
 */

const container = ref<HTMLDivElement | null>(null);
const failed = ref(false);

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let points: THREE.Points | null = null;
let geometry: THREE.BufferGeometry | null = null;
let material: THREE.PointsMaterial | null = null;
let frameId: number | null = null;
let resizeObserver: ResizeObserver | null = null;
let themeObserver: MutationObserver | null = null;

// Target vs current, lerped each frame so the field eases toward the cursor
// instead of snapping to it.
const pointer = { x: 0, y: 0 };
const current = { x: 0, y: 0 };

const PARTICLE_COUNT = 1400;

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

function brandColor(): THREE.Color {
    // Read the live token so the field recolours with the theme.
    const raw = getComputedStyle(document.documentElement)
        .getPropertyValue('--brand')
        .trim();

    const color = new THREE.Color();

    try {
        color.setStyle(raw || '#6366f1');
    } catch {
        color.setStyle('#6366f1');
    }

    return color;
}

function starColor(): THREE.Color {
    // Light mode: vivid red stars on white, dark mode: brand (also red) with additive glow
    if (isDark()) {
        return brandColor();
    }

    const color = new THREE.Color();

    color.setStyle('#ef4444');

    return color;
}

function applyThemeToMaterial() {
    if (!material) {
        return;
    }

    const dark = isDark();

    if (dark) {
        material.color.copy(brandColor());
        material.blending = THREE.AdditiveBlending;
        material.opacity = 0.75;
        material.size = 0.035;
    } else {
        // Light mode: solid red stars, normal blending so they stay crisp on white
        material.color.setStyle('#ef4444');
        material.blending = THREE.NormalBlending;
        material.opacity = 0.9;
        material.size = 0.042;
    }

    material.needsUpdate = true;
}

function buildParticles(): THREE.Points {
    geometry = new THREE.BufferGeometry();

    const positions = new Float32Array(PARTICLE_COUNT * 3);

    for (let i = 0; i < PARTICLE_COUNT; i++) {
        // Distribute through a slab rather than a cube: the camera looks down
        // -Z, so depth variation reads as parallax while X/Y fills the frame.
        positions[i * 3] = (Math.random() - 0.5) * 14;
        positions[i * 3 + 1] = (Math.random() - 0.5) * 9;
        positions[i * 3 + 2] = (Math.random() - 0.5) * 6;
    }

    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    const dark = isDark();

    material = new THREE.PointsMaterial({
        color: starColor(),
        size: dark ? 0.035 : 0.042,
        transparent: true,
        opacity: dark ? 0.75 : 0.9,
        sizeAttenuation: true,
        depthWrite: false,
        blending: dark ? THREE.AdditiveBlending : THREE.NormalBlending,
    });

    return new THREE.Points(geometry, material);
}

function onPointerMove(event: PointerEvent) {
    pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (event.clientY / window.innerHeight) * 2 - 1;
}

function renderFrame() {
    if (!renderer || !scene || !camera || !points) {
        return;
    }

    current.x += (pointer.x - current.x) * 0.035;
    current.y += (pointer.y - current.y) * 0.035;

    // Constant slow drift keeps the field alive when the cursor is still.
    points.rotation.y += 0.0004;
    points.rotation.x = current.y * 0.18;
    points.rotation.z = current.x * 0.06;

    camera.position.x = current.x * 0.5;
    camera.position.y = -current.y * 0.35;
    camera.lookAt(0, 0, 0);

    renderer.render(scene, camera);
    frameId = requestAnimationFrame(renderFrame);
}

function start() {
    if (frameId === null) {
        frameId = requestAnimationFrame(renderFrame);
    }
}

function stop() {
    if (frameId !== null) {
        cancelAnimationFrame(frameId);
        frameId = null;
    }
}

/** Pause the loop when the tab is hidden — an offscreen RAF is wasted battery. */
function onVisibilityChange() {
    if (document.hidden) {
        stop();
    } else {
        start();
    }
}

function resize() {
    const element = container.value;

    if (!element || !renderer || !camera) {
        return;
    }

    const { clientWidth, clientHeight } = element;

    if (clientWidth === 0 || clientHeight === 0) {
        return;
    }

    camera.aspect = clientWidth / clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(clientWidth, clientHeight, false);
}

onMounted(() => {
    const element = container.value;

    if (!element) {
        return;
    }

    try {
        renderer = new THREE.WebGLRenderer({
            alpha: true,
            antialias: true,
            powerPreference: 'low-power',
        });
    } catch {
        // No WebGL context: the parent's static gradient stays visible.
        failed.value = true;

        return;
    }

    // Cap DPR at 2 — beyond that the particle field costs far more than it looks.
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    element.appendChild(renderer.domElement);
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    renderer.domElement.style.display = 'block';

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(60, 1, 0.1, 100);
    camera.position.set(0, 0, 7);

    points = buildParticles();
    scene.add(points);

    resize();

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(element);

    window.addEventListener('pointermove', onPointerMove, { passive: true });
    document.addEventListener('visibilitychange', onVisibilityChange);

    // React to light/dark toggle — keep stars red in light mode, glowing in dark
    themeObserver = new MutationObserver(() => {
        applyThemeToMaterial();
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });

    start();
});

onBeforeUnmount(() => {
    stop();

    window.removeEventListener('pointermove', onPointerMove);
    document.removeEventListener('visibilitychange', onVisibilityChange);

    themeObserver?.disconnect();
    themeObserver = null;

    resizeObserver?.disconnect();
    resizeObserver = null;

    // Explicit teardown: three.js holds GPU resources that garbage collection
    // will not reclaim on its own.
    points?.removeFromParent();
    geometry?.dispose();
    material?.dispose();
    renderer?.dispose();
    renderer?.domElement.remove();

    points = null;
    geometry = null;
    material = null;
    renderer = null;
    scene = null;
    camera = null;
});
</script>

<template>
    <div
        v-show="!failed"
        ref="container"
        class="absolute inset-0 h-full w-full"
        aria-hidden="true"
    />
</template>
