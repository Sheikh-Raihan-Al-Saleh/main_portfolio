<script setup lang="ts">
import * as THREE from 'three';
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * The Studio's hero object: a system of connected services.
 *
 * A sphere of nodes wired to their nearest neighbours, with data pulses running
 * along the wires. It is the visual answer to "we build connected software" —
 * services, APIs and infrastructure as one organism — and it is deliberately
 * the *only* WebGL on the Studio side.
 *
 * Cost control, in the order it matters:
 *
 * - The parent mounts this lazily (dynamic import) after checking reduced
 *   motion and WebGL support, so three.js never lands on a first paint.
 * - Node and edge counts scale down with the viewport and the device's core
 *   count; DPR is capped below 1.75.
 * - The render loop only runs while the canvas is on screen *and* the tab is
 *   visible, so a hidden hero costs nothing.
 * - Three draw calls total: points, edges, pulses. No post-processing, no
 *   shadows, no per-frame allocation.
 * - Everything three.js allocates is disposed on unmount, and a lost WebGL
 *   context is treated as a failure that hands the hero back to its CSS
 *   fallback rather than throwing.
 */

const emit = defineEmits<{ ready: []; failed: [] }>();

const container = ref<HTMLDivElement | null>(null);

let renderer: THREE.WebGLRenderer | null = null;
let scene: THREE.Scene | null = null;
let camera: THREE.PerspectiveCamera | null = null;
let group: THREE.Group | null = null;

let nodeGeometry: THREE.BufferGeometry | null = null;
let nodeMaterial: THREE.PointsMaterial | null = null;
let nodes: THREE.Points | null = null;

let edgeGeometry: THREE.BufferGeometry | null = null;
let edgeMaterial: THREE.LineBasicMaterial | null = null;
let edges: THREE.LineSegments | null = null;

let pulseGeometry: THREE.BufferGeometry | null = null;
let pulseMaterial: THREE.PointsMaterial | null = null;
let pulses: THREE.Points | null = null;

let sprite: THREE.CanvasTexture | null = null;

const rings: THREE.LineLoop[] = [];

let frameId: number | null = null;
let resizeObserver: ResizeObserver | null = null;
let visibilityObserver: IntersectionObserver | null = null;
let themeObserver: MutationObserver | null = null;

let onScreen = true;
let started = false;
let disposed = false;

/** Pulse bookkeeping — pooled, never re-allocated per frame. */
type Pulse = {
    from: number;
    to: number;
    t: number;
    speed: number;
};

const pulseState: Pulse[] = [];
const nodePositions: THREE.Vector3[] = [];
const edgeIndex: [number, number][] = [];

/** Smoothed pointer, so the camera eases instead of snapping. */
const pointer = { x: 0, y: 0 };
const current = { x: 0, y: 0 };

const SPHERE_RADIUS = 2.5;

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

/** The live brand token, so the scene recolours with the theme. */
function brandColor(): THREE.Color {
    const raw = getComputedStyle(document.documentElement)
        .getPropertyValue('--brand')
        .trim();

    const color = new THREE.Color();

    try {
        color.setStyle(raw || '#dc2626');
    } catch {
        color.setStyle('#dc2626');
    }

    return color;
}

/* ── Scene population ──────────────────────────────────── */

/**
 * How dense the system is allowed to be. Small screens and low core counts get
 * a visibly simpler graph rather than the same graph rendered smaller.
 */
function nodeCount(): number {
    const width = window.innerWidth;
    const cores = navigator.hardwareConcurrency ?? 4;

    if (width < 768 || cores <= 4) {
        return 46;
    }

    if (width < 1280) {
        return 72;
    }

    return 96;
}

/** Evenly distributes points over a sphere (Fibonacci lattice). */
function fibonacciSphere(count: number): THREE.Vector3[] {
    const points: THREE.Vector3[] = [];
    const golden = Math.PI * (3 - Math.sqrt(5));

    for (let i = 0; i < count; i++) {
        const y = 1 - (i / Math.max(count - 1, 1)) * 2;
        const radius = Math.sqrt(Math.max(1 - y * y, 0));
        const theta = golden * i;

        points.push(
            new THREE.Vector3(
                Math.cos(theta) * radius,
                y,
                Math.sin(theta) * radius,
            ).multiplyScalar(SPHERE_RADIUS),
        );
    }

    return points;
}

function buildSprite(): THREE.CanvasTexture {
    const size = 64;
    const canvas = document.createElement('canvas');

    canvas.width = size;
    canvas.height = size;

    const context = canvas.getContext('2d');

    if (context) {
        const gradient = context.createRadialGradient(
            size / 2,
            size / 2,
            0,
            size / 2,
            size / 2,
            size / 2,
        );

        gradient.addColorStop(0, 'rgba(255, 255, 255, 1)');
        gradient.addColorStop(0.35, 'rgba(255, 255, 255, 0.75)');
        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

        context.fillStyle = gradient;
        context.fillRect(0, 0, size, size);
    }

    const texture = new THREE.CanvasTexture(canvas);

    texture.needsUpdate = true;

    return texture;
}

/**
 * Wires every node to its two nearest neighbours. The pair list is deduped and
 * kept on hand so the pulses know which segment to run along.
 */
function buildEdges(points: THREE.Vector3[]): Float32Array {
    const seen = new Set<string>();
    const positions: number[] = [];
    const neighbours = 2;

    for (let i = 0; i < points.length; i++) {
        const source = points[i];

        if (!source) {
            continue;
        }

        const ranked = points
            .map((point, index) => ({
                index,
                distance: point.distanceToSquared(source),
            }))
            .filter((candidate) => candidate.index !== i)
            .sort((a, b) => a.distance - b.distance)
            .slice(0, neighbours);

        for (const candidate of ranked) {
            const from = Math.min(i, candidate.index);
            const to = Math.max(i, candidate.index);
            const key = `${from}:${to}`;

            if (seen.has(key)) {
                continue;
            }

            seen.add(key);

            const target = points[candidate.index];

            if (!target) {
                continue;
            }

            positions.push(
                source.x,
                source.y,
                source.z,
                target.x,
                target.y,
                target.z,
            );

            edgeIndex.push([from, to]);
        }
    }

    return new Float32Array(positions);
}

function buildPulses(): Float32Array {
    const count = Math.min(16, Math.max(8, Math.round(edgeIndex.length / 6)));
    const positions = new Float32Array(count * 3);

    for (let i = 0; i < count; i++) {
        pulseState.push({
            from: 0,
            to: 0,
            t: Math.random(),
            speed: 0.16 + Math.random() * 0.24,
        });
    }

    return positions;
}

/** Assigns a fresh random segment to a pulse, travelling either direction. */
function rewirePulse(pulse: Pulse) {
    const pair = edgeIndex[Math.floor(Math.random() * edgeIndex.length)];

    if (!pair) {
        return;
    }

    const forward = Math.random() > 0.5;

    pulse.from = forward ? pair[0] : pair[1];
    pulse.to = forward ? pair[1] : pair[0];
    pulse.t = 0;
}

function buildRings(color: THREE.Color) {
    const tilts: [number, number][] = [
        [Math.PI / 2.4, 0],
        [Math.PI / 1.9, Math.PI / 5],
        [Math.PI / 3.2, -Math.PI / 7],
    ];

    for (const [tilt, spin] of tilts) {
        const curve = new THREE.EllipseCurve(
            0,
            0,
            SPHERE_RADIUS * 1.1,
            SPHERE_RADIUS * 1.1,
            0,
            Math.PI * 2,
        );

        const geometry = new THREE.BufferGeometry().setFromPoints(
            curve.getPoints(96).map((point) => new THREE.Vector3(point.x, point.y, 0)),
        );

        const material = new THREE.LineBasicMaterial({
            color,
            transparent: true,
            opacity: isDark() ? 0.22 : 0.14,
            depthWrite: false,
        });

        const ring = new THREE.LineLoop(geometry, material);

        ring.rotation.set(tilt, spin, 0);

        rings.push(ring);
        group?.add(ring);
    }
}

/* ── Theme ─────────────────────────────────────────────── */

function applyTheme() {
    if (!nodeMaterial || !pulseMaterial || !edgeMaterial) {
        return;
    }

    const color = brandColor();
    const dark = isDark();

    nodeMaterial.color.copy(color);
    nodeMaterial.opacity = dark ? 0.85 : 0.7;
    nodeMaterial.blending = dark
        ? THREE.AdditiveBlending
        : THREE.NormalBlending;

    pulseMaterial.color.copy(color);
    pulseMaterial.opacity = dark ? 1 : 0.85;
    pulseMaterial.blending = dark
        ? THREE.AdditiveBlending
        : THREE.NormalBlending;

    edgeMaterial.color.copy(color);
    edgeMaterial.opacity = dark ? 0.14 : 0.11;

    for (const ring of rings) {
        const material = ring.material as THREE.LineBasicMaterial;

        material.color.copy(color);
        material.opacity = dark ? 0.22 : 0.14;
    }

    nodeMaterial.needsUpdate = true;
    pulseMaterial.needsUpdate = true;
}

/* ── Frame loop ────────────────────────────────────────── */

function renderFrame() {
    if (!renderer || !scene || !camera || !group || !pulses) {
        return;
    }

    current.x += (pointer.x - current.x) * 0.045;
    current.y += (pointer.y - current.y) * 0.045;

    // Idle rotation plus the pointer's influence: the system keeps turning
    // when the cursor is still, but leans where the visitor is looking.
    group.rotation.y += 0.0016;
    group.rotation.x = 0.12 + Math.sin(group.rotation.y * 0.35) * 0.07;

    camera.position.x = current.x * 0.85;
    camera.position.y = -current.y * 0.6;
    camera.lookAt(0, 0, 0);

    // Walk the pulses along their segments.
    const attribute = pulseGeometry?.getAttribute(
        'position',
    ) as THREE.BufferAttribute | undefined;

    if (attribute) {
        const array = attribute.array as Float32Array;

        for (let i = 0; i < pulseState.length; i++) {
            const pulse = pulseState[i];

            if (!pulse) {
                continue;
            }

            pulse.t += pulse.speed * 0.016;

            if (pulse.t >= 1) {
                rewirePulse(pulse);
            }

            const from = nodePositions[pulse.from];
            const to = nodePositions[pulse.to];

            if (!from || !to) {
                continue;
            }

            // Still inside the rotating group's local space, which is why the
            // pulse attribute is in local coordinates too.
            array[i * 3] = from.x + (to.x - from.x) * pulse.t;
            array[i * 3 + 1] = from.y + (to.y - from.y) * pulse.t;
            array[i * 3 + 2] = from.z + (to.z - from.z) * pulse.t;
        }

        attribute.needsUpdate = true;
    }

    renderer.render(scene, camera);
    frameId = requestAnimationFrame(renderFrame);
}

function start() {
    if (frameId === null && started && onScreen && !document.hidden) {
        frameId = requestAnimationFrame(renderFrame);
    }
}

function stop() {
    if (frameId !== null) {
        cancelAnimationFrame(frameId);
        frameId = null;
    }
}

function onVisibilityChange() {
    if (document.hidden) {
        stop();
    } else {
        start();
    }
}

function onPointerMove(event: PointerEvent) {
    pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (event.clientY / window.innerHeight) * 2 - 1;
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

/* ── Lifecycle ─────────────────────────────────────────── */

function fail() {
    if (disposed) {
        return;
    }

    emit('failed');
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
        // No WebGL context: the caller keeps its CSS composition.
        fail();

        return;
    }

    const lowPower = (navigator.hardwareConcurrency ?? 4) <= 4;

    renderer.setPixelRatio(
        Math.min(window.devicePixelRatio, lowPower ? 1.25 : 1.75),
    );
    renderer.setClearColor(0x000000, 0);
    element.appendChild(renderer.domElement);

    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    renderer.domElement.style.display = 'block';

    renderer.domElement.addEventListener(
        'webglcontextlost',
        (event) => {
            event.preventDefault();
            stop();
            fail();
        },
        { once: true },
    );

    scene = new THREE.Scene();
    camera = new THREE.PerspectiveCamera(50, 1, 0.1, 60);
    camera.position.set(0, 0, 8);

    group = new THREE.Group();
    scene.add(group);

    sprite = buildSprite();

    const points = fibonacciSphere(nodeCount());

    nodePositions.push(...points);

    nodeGeometry = new THREE.BufferGeometry().setFromPoints(points);
    nodeMaterial = new THREE.PointsMaterial({
        map: sprite,
        size: 0.17,
        transparent: true,
        opacity: isDark() ? 0.85 : 0.7,
        sizeAttenuation: true,
        depthWrite: false,
        blending: isDark() ? THREE.AdditiveBlending : THREE.NormalBlending,
        color: brandColor(),
    });

    nodes = new THREE.Points(nodeGeometry, nodeMaterial);
    group.add(nodes);

    edgeGeometry = new THREE.BufferGeometry();
    edgeGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(buildEdges(points), 3),
    );
    edgeMaterial = new THREE.LineBasicMaterial({
        color: brandColor(),
        transparent: true,
        opacity: isDark() ? 0.14 : 0.11,
        depthWrite: false,
    });

    edges = new THREE.LineSegments(edgeGeometry, edgeMaterial);
    group.add(edges);

    pulseGeometry = new THREE.BufferGeometry();
    pulseGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(buildPulses(), 3),
    );
    pulseMaterial = new THREE.PointsMaterial({
        map: sprite,
        size: 0.34,
        transparent: true,
        opacity: isDark() ? 1 : 0.85,
        sizeAttenuation: true,
        depthWrite: false,
        blending: isDark() ? THREE.AdditiveBlending : THREE.NormalBlending,
        color: brandColor(),
    });

    pulses = new THREE.Points(pulseGeometry, pulseMaterial);
    group.add(pulses);

    for (const pulse of pulseState) {
        rewirePulse(pulse);
    }

    buildRings(brandColor());

    resize();

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(element);

    // Off-screen heroes should not burn a frame budget they cannot show.
    visibilityObserver = new IntersectionObserver(
        (entries) => {
            onScreen = entries.some((entry) => entry.isIntersecting);

            if (onScreen) {
                start();
            } else {
                stop();
            }
        },
        { threshold: 0 },
    );
    visibilityObserver.observe(element);

    window.addEventListener('pointermove', onPointerMove, { passive: true });
    document.addEventListener('visibilitychange', onVisibilityChange);

    themeObserver = new MutationObserver(applyTheme);
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });

    started = true;

    // One immediate frame so the caller can swap out its fallback with no
    // flash of empty space, even before the loop is scheduled.
    renderer.render(scene, camera);
    emit('ready');

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        // Static frame only: the composition still reads, nothing moves.
        stop();
        started = false;

        return;
    }

    start();
});

onBeforeUnmount(() => {
    disposed = true;

    stop();

    window.removeEventListener('pointermove', onPointerMove);
    document.removeEventListener('visibilitychange', onVisibilityChange);

    themeObserver?.disconnect();
    themeObserver = null;

    resizeObserver?.disconnect();
    resizeObserver = null;

    visibilityObserver?.disconnect();
    visibilityObserver = null;

    for (const ring of rings) {
        ring.geometry.dispose();
        (ring.material as THREE.Material).dispose();
    }

    rings.length = 0;

    nodes?.removeFromParent();
    edges?.removeFromParent();
    pulses?.removeFromParent();

    nodeGeometry?.dispose();
    nodeMaterial?.dispose();
    edgeGeometry?.dispose();
    edgeMaterial?.dispose();
    pulseGeometry?.dispose();
    pulseMaterial?.dispose();
    sprite?.dispose();

    renderer?.dispose();
    renderer?.domElement.remove();

    nodeGeometry = null;
    nodeMaterial = null;
    nodes = null;
    edgeGeometry = null;
    edgeMaterial = null;
    edges = null;
    pulseGeometry = null;
    pulseMaterial = null;
    pulses = null;
    sprite = null;
    renderer = null;
    scene = null;
    camera = null;
    group = null;
});
</script>

<template>
    <div
        ref="container"
        class="absolute inset-0 h-full w-full"
        aria-hidden="true"
    />
</template>
