<script setup lang="ts">
import * as THREE from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { RoundedBoxGeometry } from 'three/examples/jsm/geometries/RoundedBoxGeometry.js';
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * The hero's WebGL composition, drawn as software rather than decoration.
 *
 * - `studio`: a system architecture diagram — glossy service nodes (UI, API,
 *   Auth, Queue, DB, Cache) joined by tubes, with data packets flowing along
 *   every connection.
 * - `founder`: floating code-editor windows at different depths, each with
 *   syntax-coloured code and a blinking cursor, plus a few orbiting tokens.
 *
 * Tinted with the face's `--scene-1..3` tokens and re-tinted when the theme
 * flips. Leans toward the pointer, turns as the page scrolls, and pauses while
 * off screen. Loaded lazily by the hero; emits `ready` after the first frame
 * and `failed` when no WebGL context is available.
 */
type Props = {
    variant?: 'studio' | 'founder';
};

const props = withDefaults(defineProps<Props>(), { variant: 'studio' });

const emit = defineEmits<{ ready: []; failed: [] }>();

const host = ref<HTMLElement | null>(null);

let renderer: THREE.WebGLRenderer | null = null;
let frame: number | null = null;
let resizeObserver: ResizeObserver | null = null;
let visibility: IntersectionObserver | null = null;
let themeObserver: MutationObserver | null = null;
let visible = true;
const disposers: (() => void)[] = [];

const pointer = { x: 0, y: 0 };
const eased = { x: 0, y: 0, scroll: 0 };

function css(name: string, fallback: string): string {
    return (
        getComputedStyle(document.body).getPropertyValue(name).trim() ||
        fallback
    );
}

function color(name: string, fallback: string): THREE.Color {
    const c = new THREE.Color();

    try {
        c.set(css(name, fallback));
    } catch {
        c.set(fallback);
    }

    return c;
}

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

function glossy(tint: THREE.Color): THREE.MeshPhysicalMaterial {
    return new THREE.MeshPhysicalMaterial({
        color: tint,
        roughness: 0.2,
        metalness: 0.05,
        clearcoat: 1,
        clearcoatRoughness: 0.08,
        iridescence: 0.5,
        iridescenceIOR: 1.35,
    });
}

/** A crisp label texture for a node face: an icon glyph and a name. */
function labelTexture(
    title: string,
    glyph: string,
    accent: string,
): THREE.CanvasTexture {
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 256;
    const ctx = canvas.getContext('2d')!;

    ctx.clearRect(0, 0, 512, 256);
    ctx.fillStyle = 'rgba(255,255,255,0.92)';
    ctx.beginPath();
    ctx.roundRect(16, 16, 480, 224, 48);
    ctx.fill();

    ctx.fillStyle = accent;
    ctx.beginPath();
    ctx.roundRect(48, 64, 128, 128, 32);
    ctx.fill();

    ctx.fillStyle = '#ffffff';
    ctx.font = '600 64px "JetBrains Mono", ui-monospace, monospace';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(glyph, 112, 130);

    ctx.fillStyle = '#18181b';
    ctx.textAlign = 'left';
    ctx.font = '700 72px "Plus Jakarta Sans", Inter, sans-serif';
    ctx.fillText(title, 208, 132);

    const texture = new THREE.CanvasTexture(canvas);
    texture.colorSpace = THREE.SRGBColorSpace;
    texture.anisotropy = 4;

    return texture;
}

type CodeLine = { indent: number; tokens: [string, string][] };

/** Draws a code-editor window onto a canvas; `cursor` toggles the caret. */
function drawEditor(
    ctx: CanvasRenderingContext2D,
    file: string,
    lines: CodeLine[],
    cursor: boolean,
    accent: string,
) {
    const w = ctx.canvas.width;
    const h = ctx.canvas.height;
    const dark = isDark();

    ctx.clearRect(0, 0, w, h);
    ctx.fillStyle = dark ? 'rgba(24,24,27,0.96)' : 'rgba(255,255,255,0.96)';
    ctx.beginPath();
    ctx.roundRect(0, 0, w, h, 36);
    ctx.fill();

    ctx.fillStyle = dark ? 'rgba(255,255,255,0.06)' : 'rgba(10,10,11,0.04)';
    ctx.beginPath();
    ctx.roundRect(0, 0, w, 72, [36, 36, 0, 0]);
    ctx.fill();

    ['#ff5f57', '#febc2e', '#28c840'].forEach((dot, i) => {
        ctx.fillStyle = dot;
        ctx.beginPath();
        ctx.arc(44 + i * 34, 36, 10, 0, Math.PI * 2);
        ctx.fill();
    });

    ctx.fillStyle = dark ? '#a1a1aa' : '#71717a';
    ctx.font = '500 24px "JetBrains Mono", ui-monospace, monospace';
    ctx.fillText(file, 160, 44);

    ctx.font = '500 28px "JetBrains Mono", ui-monospace, monospace';
    let lastX = 0;
    let lastY = 0;

    lines.forEach((line, row) => {
        const y = 128 + row * 46;
        ctx.fillStyle = dark ? '#52525b' : '#a1a1aa';
        ctx.fillText(String(row + 1).padStart(2, ' '), 32, y);

        let x = 96 + line.indent * 34;

        for (const [text, tone] of line.tokens) {
            ctx.fillStyle =
                tone === 'accent'
                    ? accent
                    : tone === 'text'
                      ? dark
                          ? '#e4e4e7'
                          : '#27272a'
                      : tone;
            ctx.fillText(text, x, y);
            x += ctx.measureText(text).width;
        }

        lastX = x;
        lastY = y;
    });

    if (cursor) {
        ctx.fillStyle = accent;
        ctx.fillRect(lastX + 4, lastY - 26, 14, 34);
    }
}

function onPointerMove(event: PointerEvent) {
    pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
    pointer.y = (event.clientY / window.innerHeight) * 2 - 1;
}

onMounted(() => {
    const element = host.value;

    if (!element) {
        return;
    }

    try {
        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    } catch {
        emit('failed');

        return;
    }

    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    element.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;

    // The founder's editors sit wide of the portrait, so that scene pulls back further.
    const BASE_Z = props.variant === 'studio' ? 12 : 13;
    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(0, 0, BASE_Z);

    const key = new THREE.DirectionalLight('#ffffff', 1.2);
    key.position.set(4, 6, 8);
    scene.add(key, new THREE.AmbientLight('#ffffff', 0.4));

    const group = new THREE.Group();
    scene.add(group);

    const tints = () => [
        color('--scene-1', '#6366f1'),
        color('--scene-2', '#a855f7'),
        color('--scene-3', '#38bdf8'),
    ];

    let palette = tints();
    const retint: (() => void)[] = [];
    let animate: (t: number) => void = () => {};

    if (props.variant === 'studio') {
        /* ── Architecture graph ─────────────────────────── */
        const nodes = [
            {
                name: 'UI',
                glyph: '</>',
                pos: new THREE.Vector3(-2.2, 1.7, 0.4),
                tint: 0,
            },
            {
                name: 'API',
                glyph: '{}',
                pos: new THREE.Vector3(0, 0.6, 0.9),
                tint: 1,
            },
            {
                name: 'Auth',
                glyph: '✓',
                pos: new THREE.Vector3(2.1, 1.9, -0.4),
                tint: 2,
            },
            {
                name: 'Queue',
                glyph: '≡',
                pos: new THREE.Vector3(-2.0, -1.4, -0.3),
                tint: 2,
            },
            {
                name: 'DB',
                glyph: '▤',
                pos: new THREE.Vector3(0.4, -2.2, 0.2),
                tint: 0,
            },
            {
                name: 'Cache',
                glyph: '⚡',
                pos: new THREE.Vector3(2.2, -1.0, 0.6),
                tint: 1,
            },
        ];

        const edges: [number, number][] = [
            [0, 1],
            [1, 2],
            [1, 3],
            [1, 4],
            [1, 5],
            [3, 4],
            [5, 4],
        ];

        const box = new RoundedBoxGeometry(1.9, 1.0, 0.5, 6, 0.22);
        const face = new THREE.PlaneGeometry(1.7, 0.85);
        const bodies: THREE.MeshPhysicalMaterial[] = [];
        const meshes: THREE.Object3D[] = [];

        nodes.forEach((node, i) => {
            const holder = new THREE.Group();
            holder.position.copy(node.pos);

            const material = glossy(palette[node.tint]!);
            bodies.push(material);
            holder.add(new THREE.Mesh(box, material));

            const label = new THREE.Mesh(
                face,
                new THREE.MeshBasicMaterial({
                    map: labelTexture(
                        node.name,
                        node.glyph,
                        `#${palette[node.tint]!.getHexString()}`,
                    ),
                    transparent: true,
                }),
            );
            label.position.z = 0.26;
            holder.add(label);

            holder.userData.offset = i * 1.3;
            group.add(holder);
            meshes.push(holder);
        });

        // Tubes and the packets that travel them.
        const tubeMaterial = new THREE.MeshBasicMaterial({
            transparent: true,
            opacity: 0.35,
        });
        const packetGeometry = new THREE.SphereGeometry(0.09, 24, 24);
        const packetMaterial = new THREE.MeshBasicMaterial();
        const curves: THREE.CatmullRomCurve3[] = [];
        const packets: {
            mesh: THREE.Mesh;
            curve: number;
            speed: number;
            phase: number;
        }[] = [];

        edges.forEach(([a, b], i) => {
            const from = nodes[a]!.pos;
            const to = nodes[b]!.pos;
            const mid = from
                .clone()
                .lerp(to, 0.5)
                .add(new THREE.Vector3(0, 0, 0.8));
            const curve = new THREE.CatmullRomCurve3([from, mid, to]);
            curves.push(curve);
            group.add(
                new THREE.Mesh(
                    new THREE.TubeGeometry(curve, 48, 0.025, 8),
                    tubeMaterial,
                ),
            );

            for (let k = 0; k < 2; k++) {
                const mesh = new THREE.Mesh(packetGeometry, packetMaterial);
                group.add(mesh);
                packets.push({
                    mesh,
                    curve: i,
                    speed: 0.18 + Math.random() * 0.12,
                    phase: k * 0.5 + Math.random() * 0.2,
                });
            }
        });

        const paint = () => {
            tubeMaterial.color.copy(palette[1]!);
            packetMaterial.color
                .copy(palette[0]!)
                .lerp(new THREE.Color('#ffffff'), 0.25);
            bodies.forEach((material, i) =>
                material.color.copy(palette[nodes[i]!.tint]!),
            );
        };
        paint();
        retint.push(paint);

        animate = (t) => {
            meshes.forEach((holder) => {
                holder.position.y +=
                    Math.sin(t * 0.9 + holder.userData.offset) * 0.0025;
                holder.rotation.y =
                    Math.sin(t * 0.5 + holder.userData.offset) * 0.12;
            });

            for (const p of packets) {
                const u = (t * p.speed + p.phase) % 1;
                p.mesh.position.copy(curves[p.curve]!.getPointAt(u));
                p.mesh.scale.setScalar(0.7 + Math.sin(u * Math.PI) * 0.6);
            }
        };
    } else {
        /* ── Floating code editors ──────────────────────── */
        const editors: {
            file: string;
            lines: CodeLine[];
            pos: THREE.Vector3;
            rot: number;
            scale: number;
        }[] = [
            {
                file: 'portfolio.ts',
                pos: new THREE.Vector3(-2.3, 2.5, -1.6),
                rot: 0.35,
                scale: 1,
                lines: [
                    {
                        indent: 0,
                        tokens: [
                            ['const ', '#c678dd'],
                            ['dev', 'text'],
                            [' = {', 'text'],
                        ],
                    },
                    {
                        indent: 1,
                        tokens: [
                            ['stack', 'text'],
                            [': ', 'text'],
                            ["['laravel', 'vue']", 'accent'],
                        ],
                    },
                    {
                        indent: 1,
                        tokens: [
                            ['ships', 'text'],
                            [': ', 'text'],
                            ['true', '#e5a50a'],
                        ],
                    },
                    {
                        indent: 1,
                        tokens: [
                            ['coffee', 'text'],
                            [': ', 'text'],
                            ['Infinity', '#e5a50a'],
                        ],
                    },
                    { indent: 0, tokens: [['};', 'text']] },
                ],
            },
            {
                file: 'deploy.sh',
                pos: new THREE.Vector3(2.5, -2.4, -1.2),
                rot: -0.35,
                scale: 0.85,
                lines: [
                    {
                        indent: 0,
                        tokens: [
                            ['$ ', 'accent'],
                            ['php artisan test', 'text'],
                        ],
                    },
                    { indent: 0, tokens: [['  ✓ tests passed', '#22c55e']] },
                    {
                        indent: 0,
                        tokens: [
                            ['$ ', 'accent'],
                            ['git push origin', 'text'],
                        ],
                    },
                    { indent: 0, tokens: [['  ✓ deployed', '#22c55e']] },
                ],
            },
        ];

        const live: {
            ctx: CanvasRenderingContext2D;
            texture: THREE.CanvasTexture;
            e: (typeof editors)[number];
            mesh: THREE.Mesh;
        }[] = [];

        editors.forEach((e, i) => {
            const canvas = document.createElement('canvas');
            canvas.width = 900;
            canvas.height = 120 + e.lines.length * 46 + 30;
            const ctx = canvas.getContext('2d')!;
            const texture = new THREE.CanvasTexture(canvas);
            texture.colorSpace = THREE.SRGBColorSpace;
            texture.anisotropy = 4;

            const aspect = canvas.height / canvas.width;
            const mesh = new THREE.Mesh(
                new THREE.PlaneGeometry(4.2 * e.scale, 4.2 * e.scale * aspect),
                new THREE.MeshBasicMaterial({
                    map: texture,
                    transparent: true,
                }),
            );
            mesh.position.copy(e.pos);
            mesh.rotation.y = e.rot;
            mesh.userData.offset = i * 2;
            group.add(mesh);
            live.push({ ctx, texture, e, mesh });
        });

        // Orbiting glossy tokens.
        const tokens: THREE.Mesh[] = [];
        const tokenMaterials = [
            glossy(palette[0]!),
            glossy(palette[1]!),
            glossy(palette[2]!),
        ];
        const shapes = [
            new THREE.TorusGeometry(0.32, 0.12, 32, 64),
            new RoundedBoxGeometry(0.55, 0.55, 0.55, 4, 0.12),
            new THREE.SphereGeometry(0.28, 48, 48),
            new THREE.OctahedronGeometry(0.32, 0),
        ];
        const spots = [
            new THREE.Vector3(-2.9, -1.6, 0.6),
            new THREE.Vector3(2.8, 2.0, 0.2),
            new THREE.Vector3(-2.6, 2.6, -0.4),
            new THREE.Vector3(3.0, -2.6, 0.8),
        ];
        shapes.forEach((geometry, i) => {
            const mesh = new THREE.Mesh(geometry, tokenMaterials[i % 3]!);
            mesh.position.copy(spots[i]!);
            mesh.userData.offset = i * 1.7;
            group.add(mesh);
            tokens.push(mesh);
        });

        let blink = true;
        const redraw = () => {
            const accent = `#${palette[0]!.getHexString()}`;
            live.forEach(({ ctx, texture, e }) => {
                drawEditor(ctx, e.file, e.lines, blink, accent);
                texture.needsUpdate = true;
            });
        };
        redraw();
        const blinkTimer = window.setInterval(() => {
            blink = !blink;
            redraw();
        }, 600);
        disposers.push(() => window.clearInterval(blinkTimer));

        retint.push(() => {
            tokenMaterials.forEach((material, i) =>
                material.color.copy(palette[i]!),
            );
            redraw();
        });

        animate = (t) => {
            live.forEach(({ mesh }) => {
                mesh.position.y +=
                    Math.sin(t * 0.8 + mesh.userData.offset) * 0.002;
                mesh.rotation.x =
                    Math.sin(t * 0.4 + mesh.userData.offset) * 0.05;
            });
            tokens.forEach((mesh) => {
                mesh.position.y +=
                    Math.sin(t * 1.1 + mesh.userData.offset) * 0.003;
                mesh.rotation.x += 0.006;
                mesh.rotation.y += 0.009;
            });
        };
    }

    function resize() {
        if (!renderer || !element) {
            return;
        }

        const { width, height } = element.getBoundingClientRect();

        if (width === 0 || height === 0) {
            return;
        }

        renderer.setSize(width, height, false);
        camera.aspect = width / height;
        // Pull back on narrow frames so the composition never crops.
        camera.position.z =
            camera.aspect < 1 ? BASE_Z / Math.max(camera.aspect, 0.6) : BASE_Z;
        camera.updateProjectionMatrix();
    }

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(element);
    resize();

    visibility = new IntersectionObserver(([entry]) => {
        visible = entry?.isIntersecting ?? true;
    });
    visibility.observe(element);

    themeObserver = new MutationObserver(() => {
        palette = tints();
        retint.forEach((fn) => fn());
    });
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });

    window.addEventListener('pointermove', onPointerMove, { passive: true });

    const clock = new THREE.Clock();
    let first = true;

    function tick() {
        frame = requestAnimationFrame(tick);

        if (!visible || !renderer) {
            return;
        }

        const t = clock.getElapsedTime();
        const scroll = Math.min(
            1,
            window.scrollY / Math.max(1, window.innerHeight),
        );

        eased.x += (pointer.x - eased.x) * 0.05;
        eased.y += (pointer.y - eased.y) * 0.05;
        eased.scroll += (scroll - eased.scroll) * 0.08;

        animate(t);

        group.rotation.y =
            eased.x * 0.3 + eased.scroll * 0.5 + Math.sin(t * 0.2) * 0.08;
        group.rotation.x = eased.y * 0.18 + eased.scroll * 0.2;
        group.position.y = eased.scroll * 0.9;

        renderer.render(scene, camera);

        if (first) {
            first = false;
            emit('ready');
        }
    }

    tick();
});

onBeforeUnmount(() => {
    if (frame !== null) {
        cancelAnimationFrame(frame);
    }

    disposers.forEach((dispose) => dispose());
    window.removeEventListener('pointermove', onPointerMove);
    resizeObserver?.disconnect();
    visibility?.disconnect();
    themeObserver?.disconnect();

    renderer?.dispose();
    renderer?.domElement.remove();
    renderer = null;
});
</script>

<template>
    <div ref="host" class="size-full [&>canvas]:size-full" aria-hidden="true" />
</template>
