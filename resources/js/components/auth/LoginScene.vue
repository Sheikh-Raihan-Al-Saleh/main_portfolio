<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import SmartImage from '@/components/media/SmartImage.vue';
import type { LoginSceneCard, LoginSceneEmits, LoginSceneProps } from '@/types';

/**
 * The 3D scene beside the sign-in form, drawn with three.js.
 *
 * Published project covers (and the studio's logo mark) float as textured
 * cards in space. They drift slowly, the camera follows the pointer, and the
 * card under the pointer comes forward and reports its title.
 *
 * three.js is loaded on demand, so it never weighs on the rest of the site.
 * Without WebGL, or for visitors who prefer reduced motion, a still collage
 * of the same images is shown instead.
 */

const props = defineProps<LoginSceneProps>();

const emit = defineEmits<LoginSceneEmits>();

const host = ref<HTMLDivElement | null>(null);
const live = ref(false);
const fallback = ref(false);

let cleanup: (() => void) | null = null;
let unmounted = false;

/** Draw an image (any format, SVG included) onto a canvas, cropped to fill. */
function loadCanvas(
    src: string,
    width: number,
    height: number,
    contain = false,
): Promise<HTMLCanvasElement | null> {
    return new Promise((resolve) => {
        const image = new Image();
        image.decoding = 'async';
        image.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            if (!ctx) {
                resolve(null);

                return;
            }

            ctx.fillStyle = '#fffdf9';
            ctx.fillRect(0, 0, width, height);

            const iw = image.naturalWidth || width;
            const ih = image.naturalHeight || height;
            const scale = contain
                ? Math.min(width / iw, height / ih) * 0.6
                : Math.max(width / iw, height / ih);
            const w = iw * scale;
            const h = ih * scale;
            ctx.drawImage(image, (width - w) / 2, (height - h) / 2, w, h);
            resolve(canvas);
        };
        image.onerror = () => resolve(null);
        image.src = src;
    });
}

/** A typographic card for when there are few or no covers. */
function titleCanvas(
    text: string,
    width: number,
    height: number,
    ink: string,
    paper: string,
): HTMLCanvasElement {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d')!;
    ctx.fillStyle = paper;
    ctx.fillRect(0, 0, width, height);
    ctx.fillStyle = ink;
    ctx.font = `italic ${Math.round(height * 0.16)}px "Instrument Serif", Georgia, serif`;
    ctx.textBaseline = 'bottom';
    ctx.fillText(text, width * 0.07, height * 0.88, width * 0.86);

    return canvas;
}

function webglAvailable(): boolean {
    try {
        const canvas = document.createElement('canvas');

        return Boolean(
            canvas.getContext('webgl2') ?? canvas.getContext('webgl'),
        );
    } catch {
        return false;
    }
}

onMounted(async () => {
    const reduced = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (reduced || !webglAvailable() || !host.value) {
        fallback.value = true;

        return;
    }

    const THREE = await import('three');

    if (unmounted || !host.value) {
        return;
    }

    const container = host.value;
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    container.appendChild(renderer.domElement);
    renderer.domElement.className = 'absolute inset-0 size-full';
    renderer.domElement.setAttribute('aria-hidden', 'true');

    const scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0xf6f4ef, 9, 22);

    const camera = new THREE.PerspectiveCamera(38, 1, 0.1, 100);
    camera.position.set(0, 0, 12);

    // ── Cards ────────────────────────────────────────────────────────
    const styles = getComputedStyle(container);
    const accent = styles.getPropertyValue('--brand').trim() || '#ff4d00';

    const cards: LoginSceneCard[] = [];
    const disposables: { dispose: () => void }[] = [];
    const geometry = new THREE.PlaneGeometry(1, 1, 1, 1);
    disposables.push(geometry);

    // A loose spiral, so the cards never line up into a grid.
    const slots = 9;
    const positions = Array.from({ length: slots }, (_, i) => {
        const angle = i * 2.4;
        const radius = 1.2 + i * 0.45;

        return new THREE.Vector3(
            Math.cos(angle) * radius * 1.35,
            Math.sin(angle) * radius * 0.8,
            -i * 0.9 + 1.5,
        );
    });

    const sources: {
        title: string | null;
        canvas: HTMLCanvasElement | null;
        aspect: number;
    }[] = [];

    const coverCanvases = await Promise.all(
        props.covers.map((cover) => loadCanvas(cover.cover, 960, 600)),
    );
    props.covers.forEach((cover, i) =>
        sources.push({
            title: cover.title,
            canvas: coverCanvases[i],
            aspect: 1.6,
        }),
    );

    if (props.logoUrl) {
        const logo = await loadCanvas(props.logoUrl, 512, 512, true);
        sources.splice(1, 0, {
            title: props.brandName,
            canvas: logo,
            aspect: 1,
        });
    }

    // Fill the remaining slots with typographic cards in the accent palette.
    const words = ['Studio', 'Projects', 'Content', 'Craft', 'Build', 'Ship'];
    let word = 0;

    while (sources.length < slots) {
        const accentCard = sources.length % 3 === 0;
        sources.push({
            title: null,
            canvas: titleCanvas(
                words[word++ % words.length],
                800,
                500,
                accentCard ? '#ffffff' : '#111111',
                accentCard ? accent : '#ece8df',
            ),
            aspect: 1.6,
        });
    }

    if (unmounted) {
        renderer.dispose();

        return;
    }

    sources.slice(0, slots).forEach((source, i) => {
        const canvas =
            source.canvas ??
            titleCanvas(
                source.title ?? props.brandName,
                800,
                500,
                '#111111',
                '#ece8df',
            );
        const texture = new THREE.CanvasTexture(canvas);
        texture.colorSpace = THREE.SRGBColorSpace;
        texture.anisotropy = 4;
        const material = new THREE.MeshBasicMaterial({
            map: texture,
            side: THREE.DoubleSide,
            transparent: true,
        });
        const mesh = new THREE.Mesh(geometry, material);
        const width = source.aspect > 1 ? 2.6 : 1.7;
        mesh.scale.set(width, width / source.aspect, 1);
        mesh.position.copy(positions[i]);
        mesh.rotation.set(
            (Math.random() - 0.5) * 0.3,
            (Math.random() - 0.5) * 0.5,
            (Math.random() - 0.5) * 0.15,
        );
        scene.add(mesh);
        disposables.push(texture, material);
        cards.push({
            mesh,
            title: source.title,
            base: positions[i].clone(),
            phase: Math.random() * Math.PI * 2,
            hover: 0,
        });
    });

    // ── Interaction ──────────────────────────────────────────────────
    const pointer = new THREE.Vector2(0, 0);
    const target = new THREE.Vector2(0, 0);
    const raycaster = new THREE.Raycaster();
    let hovered: LoginSceneCard | null = null;

    const onMove = (event: PointerEvent) => {
        const rect = container.getBoundingClientRect();
        pointer.set(
            ((event.clientX - rect.left) / rect.width) * 2 - 1,
            -((event.clientY - rect.top) / rect.height) * 2 + 1,
        );
        target.copy(pointer);
    };
    const onLeave = () => {
        target.set(0, 0);
        pointer.set(10, 10);
    };
    container.addEventListener('pointermove', onMove, { passive: true });
    container.addEventListener('pointerleave', onLeave);

    const resize = () => {
        const { clientWidth: w, clientHeight: h } = container;
        renderer.setSize(w, h, false);
        camera.aspect = w / Math.max(h, 1);
        camera.updateProjectionMatrix();
    };
    const observer = new ResizeObserver(resize);
    observer.observe(container);
    resize();

    // Pause when the tab is hidden or the scene is off screen.
    let visible = true;
    const io = new IntersectionObserver(
        ([entry]) => (visible = entry.isIntersecting),
    );
    io.observe(container);

    const clock = new THREE.Clock();
    let frame = 0;

    const tick = () => {
        frame = requestAnimationFrame(tick);

        if (!visible || document.hidden) {
            return;
        }

        const t = clock.getElapsedTime();

        camera.position.x += (target.x * 1.4 - camera.position.x) * 0.04;
        camera.position.y += (target.y * 0.9 - camera.position.y) * 0.04;
        camera.lookAt(0, 0, 0);

        raycaster.setFromCamera(pointer, camera);
        const hit = raycaster.intersectObjects(
            cards.map((card) => card.mesh),
        )[0];
        const next = hit
            ? (cards.find((card) => card.mesh === hit.object) ?? null)
            : null;

        if (next !== hovered) {
            hovered = next;
            emit('hover', hovered?.title ?? null);
            container.style.cursor = hovered?.title ? 'pointer' : '';
        }

        for (const card of cards) {
            card.hover += ((card === hovered ? 1 : 0) - card.hover) * 0.08;
            card.mesh.position.set(
                card.base.x + Math.sin(t * 0.35 + card.phase) * 0.25,
                card.base.y + Math.cos(t * 0.3 + card.phase) * 0.2,
                card.base.z + card.hover * 1.6,
            );
            card.mesh.rotation.y =
                Math.sin(t * 0.2 + card.phase) * 0.18 * (1 - card.hover);
        }

        renderer.render(scene, camera);
    };

    tick();
    live.value = true;

    cleanup = () => {
        cancelAnimationFrame(frame);
        observer.disconnect();
        io.disconnect();
        container.removeEventListener('pointermove', onMove);
        container.removeEventListener('pointerleave', onLeave);
        disposables.forEach((item) => item.dispose());
        renderer.dispose();
        renderer.domElement.remove();
    };
});

onBeforeUnmount(() => {
    unmounted = true;
    cleanup?.();
});
</script>

<template>
    <div ref="host" class="relative size-full overflow-hidden">
        <!-- Still collage: shown without WebGL / for reduced motion, and while loading. -->
        <div
            v-if="fallback || !live"
            class="absolute inset-0 grid grid-cols-2 gap-4 p-10 transition-opacity duration-700"
            :class="!fallback && 'opacity-40'"
            aria-hidden="true"
        >
            <SmartImage
                v-for="(cover, i) in covers.slice(0, 4)"
                :key="cover.cover"
                :src="cover.cover"
                alt=""
                ratio="16/10"
                class="rounded-2xl shadow-lg"
                :style="{
                    transform: `rotate(${(i % 2 ? 1 : -1) * (2 + i)}deg) translateY(${i * 12}px)`,
                }"
            />
        </div>
    </div>
</template>
