<script setup lang="ts">
import { ArrowRight, Mail, MapPin, Sparkles } from '@lucide/vue';
import { motion, useTransform } from 'motion-v';
import {
    computed,
    defineAsyncComponent,
    onBeforeUnmount,
    onMounted,
    ref,
    shallowRef,
} from 'vue';
import MagneticButton from '@/components/motion/MagneticButton.vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { blurUp, fadeUp, stagger } from '@/lib/motion';
import type { Company, CompanyStats } from '@/types';

type Props = {
    company: Company;
    stats: CompanyStats;
    /**
     * The site's resolved brand mark (founder logo first, company logo as a
     * fallback) — the same artwork the header and footer use. Passed in rather
     * than read from `company` so one logo appears everywhere on the site.
     */
    brandLogoUrl?: string | null;
};

const props = withDefaults(defineProps<Props>(), { brandLogoUrl: null });

const heroRef = ref<HTMLElement | null>(null);
const stageRef = ref<HTMLElement | null>(null);

/**
 * The hero's WebGL scene — a sphere of connected services.
 *
 * Loaded the same way the founder's particle field is: only after the page has
 * mounted, only when motion is welcome and the browser can actually render it,
 * and only on viewports where it can be shown, so three.js lands in its own
 * chunk and never blocks the first paint. If any of that fails, the CSS
 * composition below is the hero — not a broken half of one.
 */
const StudioCanvas = shallowRef<ReturnType<typeof defineAsyncComponent> | null>(
    null,
);

/** Set once the scene has drawn its first frame, which retires the CSS stage. */
const canvasReady = ref(false);

/**
 * Whether the hero may use WebGL at all. The three-dimensional stage is only
 * rendered from `lg` up, so smaller screens never pay for a context they
 * cannot see.
 */
function canUseWebGl(): boolean {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return false;
    }

    if (!window.matchMedia('(min-width: 1024px)').matches) {
        return false;
    }

    try {
        const canvas = document.createElement('canvas');

        return Boolean(canvas.getContext('webgl2') ?? canvas.getContext('webgl'));
    } catch {
        return false;
    }
}

const { progress } = useScrollProgress({
    target: heroRef,
    offset: ['start start', 'end start'],
});

const heroY = useTransform(progress, [0, 1], [0, 90]);
const heroOpacity = useTransform(progress, [0, 0.85], [1, 0]);
const heroScale = useTransform(progress, [0, 1], [1, 0.985]);

/**
 * Scroll-linked motion for the 3D stage: as the hero leaves the viewport the
 * stage turns and lifts away, so the depth reads as a real object rather than
 * a flat backdrop. Both are motion values, so this scrubs without re-rendering.
 */
const stageSpin = useTransform(progress, [0, 1], [0, -16]);
const stageLift = useTransform(progress, [0, 1], [0, -70]);

/**
 * Pointer tilt for the 3D stage.
 *
 * The angles are written straight to CSS custom properties on the stage
 * element — one style write per frame, no reactive state and therefore no
 * component re-render while the pointer moves. Disabled on touch, on small
 * screens (where the stage is not rendered at all) and for reduced motion.
 */
const tilt = { x: 0, y: 0 };
let tiltFrame: number | null = null;
let tiltAllowed = false;

const TILT_MAX = 7;

function flushTilt() {
    tiltFrame = null;

    const stage = stageRef.value;

    if (!stage) {
        return;
    }

    stage.style.setProperty('--tilt-x', `${tilt.x.toFixed(2)}deg`);
    stage.style.setProperty('--tilt-y', `${tilt.y.toFixed(2)}deg`);
}

function scheduleTilt() {
    if (tiltFrame === null) {
        tiltFrame = requestAnimationFrame(flushTilt);
    }
}

function onHeroPointerMove(event: PointerEvent) {
    const stage = stageRef.value;

    if (!tiltAllowed || !stage) {
        return;
    }

    const rect = stage.getBoundingClientRect();

    if (!rect.width || !rect.height) {
        return;
    }

    // Offset of the pointer from the stage centre, normalised to -1..1.
    const x = (event.clientX - (rect.left + rect.width / 2)) / rect.width;
    const y = (event.clientY - (rect.top + rect.height / 2)) / rect.height;

    tilt.x = Math.max(-1, Math.min(1, x)) * TILT_MAX;
    tilt.y = Math.max(-1, Math.min(1, y)) * -TILT_MAX;

    scheduleTilt();
}

function onHeroPointerLeave() {
    tilt.x = 0;
    tilt.y = 0;

    if (tiltAllowed) {
        scheduleTilt();
    }
}

onMounted(() => {
    tiltAllowed =
        window.matchMedia('(prefers-reduced-motion: no-preference)').matches &&
        window.matchMedia('(pointer: fine)').matches &&
        window.matchMedia('(min-width: 1024px)').matches;

    if (canUseWebGl()) {
        StudioCanvas.value = defineAsyncComponent(
            () => import('@/components/company/StudioSystemsCanvas.vue'),
        );
    }
});

onBeforeUnmount(() => {
    if (tiltFrame !== null) {
        cancelAnimationFrame(tiltFrame);
    }

    tiltFrame = null;
});

const heroTitle = computed(() =>
    (props.company.hero_title ?? '{{name}} builds software that ships.')
        .replaceAll('{{name}}', props.company.name)
        .trim(),
);

/**
 * The two doors out of the hero.
 *
 * The admin owns the labels and destinations, so whatever the studio set is
 * used. The fallbacks keep the hero complete on a fresh install: one path to
 * the enquiry form, one to the work.
 */
const primaryCta = computed(() => ({
    label: props.company.primary_cta_label ?? 'Start a project',
    url: props.company.primary_cta_url ?? '#contact',
}));

const secondaryCta = computed(() => ({
    label: props.company.secondary_cta_label ?? 'Explore our work',
    url: props.company.secondary_cta_url ?? '#work',
}));

const initials = computed(() => getInitials(props.company.name));

/** Whichever mark the site is using, with a final fallback to the company's. */
const brandLogo = computed(
    () => props.brandLogoUrl ?? props.company.logo_url ?? null,
);

/**
 * Only the stats that actually carry a value, so a company that has not set a
 * founding year does not render a "0 years" claim.
 */
const figures = computed(() =>
    [
        {
            value: props.stats.projects,
            suffix: '+',
            label: 'products shipped',
        },
        {
            value: props.stats.clients,
            suffix: '',
            label: 'clients served',
        },
        {
            value: props.stats.yearsInBusiness,
            suffix: '+',
            label: 'years in business',
        },
        props.stats.foundedYear !== null
            ? {
                  value: props.stats.foundedYear,
                  suffix: '',
                  label: 'founded',
              }
            : null,
    ].filter((figure): figure is { value: number; suffix: string; label: string } =>
        Number.isFinite(figure?.value as number),
    ),
);
</script>

<template>
    <section
        id="top"
        ref="heroRef"
        class="bg-noise relative min-h-screen overflow-hidden scroll-mt-20"
        @pointermove="onHeroPointerMove"
        @pointerleave="onHeroPointerLeave"
    >
        <!-- Background -->
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <!--
                Scrolling moves these layers at different rates, so the hero
                has depth: the grid travels furthest, the ambient gradient
                barely moves. Native CSS scroll timelines — no JS per frame.
            -->
            <div class="hero-veil hero-veil-ambient">
                <div class="hero-blob hero-blob-a" />
                <div class="hero-blob hero-blob-b" />
                <div
                    class="hero-blob hero-blob-c hidden lg:block"
                />
            </div>

            <div class="hero-veil hero-veil-grid">
                <div
                    class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]"
                    style="
                        background-image:
                            linear-gradient(
                                to right,
                                var(--border) 1px,
                                transparent 1px
                            ),
                            linear-gradient(
                                to bottom,
                                var(--border) 1px,
                                transparent 1px
                            );
                        background-size: 80px 80px;
                    "
                />
            </div>

            <!--
                3D motion stage. Pure CSS 3D (perspective + preserve-3d), so
                it costs no WebGL context and no extra bytes: rings tilted on
                the X axis, particles orbiting them, and a floating logo card
                with a shadow that tightens as the card rises.
            -->
            <div class="hero-stage-wrap hidden lg:block">
                <motion.div
                    class="hero-stage-tilt"
                    :style="{ rotate: stageSpin, y: stageLift }"
                >
                    <div
                        ref="stageRef"
                        class="hero-stage"
                        :class="canvasReady && 'hero-stage--live'"
                    >
                        <div class="hero-stage-glow" />

                        <!--
                            The connected-systems graph. It draws its first
                            frame before announcing itself, so the rings below
                            are retired only once there is something to replace
                            them with.
                        -->
                        <component
                            :is="StudioCanvas"
                            v-if="StudioCanvas"
                            class="hero-stage-canvas"
                            @ready="canvasReady = true"
                        />

                        <!-- Stand-in composition: also the whole hero for
                             reduced-motion visitors, browsers without WebGL
                             and anything narrower than a laptop. -->
                        <template v-if="!canvasReady">
                            <div class="hero-ring hero-ring-1" />
                            <div class="hero-ring hero-ring-2" />

                            <div class="hero-orbit hero-orbit-1">
                                <span class="hero-dot hero-dot-a" />
                                <span class="hero-dot hero-dot-b" />
                            </div>

                            <div class="hero-orbit hero-orbit-2">
                                <span class="hero-dot hero-dot-c" />
                            </div>
                        </template>

                        <div
                            class="hero-float-card"
                            :class="brandLogo && 'hero-float-card--wide'"
                        >
                            <div
                                class="hero-card"
                                :class="brandLogo && 'hero-card--wide'"
                            >
                                <img
                                    v-if="brandLogo"
                                    :src="brandLogo"
                                    :alt="company.name"
                                    class="hero-card-logo"
                                />
                                <span
                                    v-else
                                    class="font-display text-6xl font-bold text-brand"
                                >
                                    {{ initials }}
                                </span>
                            </div>
                        </div>

                        <div class="hero-card-shadow" />
                    </div>
                </motion.div>
            </div>
        </div>

        <motion.div
            :style="{ y: heroY, opacity: heroOpacity, scale: heroScale }"
            class="relative"
        >
            <div
                class="container-laravel flex min-h-screen flex-col justify-center pt-24 pb-16 sm:pt-32"
            >
                <motion.div
                    :variants="stagger(0.12)"
                    initial="hidden"
                    animate="visible"
                    class="flex max-w-3xl flex-col gap-6"
                >
                    <motion.div
                        :variants="fadeUp"
                        class="flex items-center gap-3"
                    >
                        <img
                            v-if="brandLogo"
                            :src="brandLogo"
                            :alt="company.name"
                            class="h-10 w-auto max-w-[180px] object-contain"
                        />
                        <span
                            v-else
                            class="flex size-10 items-center justify-center rounded-lg border border-border bg-muted font-display text-sm font-bold"
                        >
                            {{ initials }}
                        </span>
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-brand/20 bg-brand/5 px-4 py-1.5 text-xs font-semibold tracking-widest text-brand uppercase"
                        >
                            <Sparkles class="size-3.5" />
                            {{
                                company.hero_eyebrow ??
                                company.headline ??
                                'Product studio'
                            }}
                        </span>
                        <span
                            v-if="company.accepting_projects"
                            class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            <span
                                class="size-1.5 animate-pulse rounded-full bg-emerald-500"
                            />
                            {{ company.status_text ?? 'Accepting new projects' }}
                        </span>
                    </motion.div>

                    <motion.h1
                        :variants="blurUp"
                        class="font-display text-4xl leading-[1.1] font-bold tracking-tight text-balance sm:text-5xl md:text-6xl"
                    >
                        {{ heroTitle }}
                        <br v-if="company.hero_statement" />
                        <span
                            v-if="company.hero_statement"
                            class="text-xl font-medium text-muted-foreground sm:text-2xl md:text-3xl"
                        >
                            {{ company.hero_statement }}
                        </span>
                    </motion.h1>

                    <motion.p
                        :variants="fadeUp"
                        class="max-w-xl text-lg leading-relaxed text-muted-foreground"
                    >
                        {{ company.tagline }}
                    </motion.p>

                    <motion.div
                        :variants="fadeUp"
                        class="flex flex-wrap items-center gap-3 pt-2"
                    >
                        <MagneticButton>
                            <Button
                                as-child
                                size="lg"
                                class="btn-laravel-primary rounded-lg px-7 shadow-lg shadow-brand/10 transition-shadow hover:shadow-brand/25"
                            >
                                <a :href="primaryCta.url">
                                    {{ primaryCta.label }}
                                    <ArrowRight
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                </a>
                            </Button>
                        </MagneticButton>
                        <MagneticButton>
                            <Button
                                as-child
                                size="lg"
                                variant="outline"
                                class="btn-laravel rounded-lg px-7"
                            >
                                <a :href="secondaryCta.url">{{
                                    secondaryCta.label
                                }}</a>
                            </Button>
                        </MagneticButton>
                    </motion.div>

                    <motion.div
                        :variants="fadeUp"
                        class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-muted-foreground"
                    >
                        <a
                            v-if="company.public_email"
                            :href="`mailto:${company.public_email}`"
                            class="inline-flex items-center gap-2 transition-colors hover:text-foreground"
                        >
                            <Mail class="size-4" />
                            {{ company.public_email }}
                        </a>
                        <span
                            v-if="company.location"
                            class="inline-flex items-center gap-2"
                        >
                            <MapPin class="size-4" />
                            {{ company.location }}
                        </span>
                        <a
                            v-if="company.website"
                            :href="company.website"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-mono text-xs transition-colors hover:text-foreground"
                        >
                            {{ company.website.replace(/^https?:\/\//, '') }}
                        </a>
                    </motion.div>

                    <motion.div
                        v-if="figures.length"
                        :variants="fadeUp"
                        class="flex flex-wrap items-center gap-x-8 gap-y-4 border-t border-border pt-6"
                    >
                        <div
                            v-for="figure in figures"
                            :key="figure.label"
                            class="flex items-baseline gap-2"
                        >
                            <span
                                class="text-3xl font-bold tracking-tight tabular-nums"
                            >
                                <AnimatedCounter
                                    :value="figure.value"
                                    :suffix="figure.suffix"
                                />
                            </span>
                            <span class="text-xs text-muted-foreground">{{
                                figure.label
                            }}</span>
                        </div>
                    </motion.div>
                </motion.div>
            </div>

            <!--
                Corner wordmark: always the monogram, never the uploaded logo.
                The logo is a full lockup on a solid plate, so at watermark
                opacity it renders as a washed-out rectangle rather than a
                mark. The brand is already shown twice above (the eyebrow chip
                and the floating card), and a large monogram reads as a
                deliberate typographic accent.
            -->
            <div
                class="pointer-events-none absolute right-8 bottom-8 hidden opacity-[0.06] lg:block dark:opacity-[0.1]"
                aria-hidden="true"
            >
                <span
                    class="font-display text-[10rem] leading-none font-bold"
                >
                    {{ initials }}
                </span>
            </div>
        </motion.div>
    </section>
</template>

<style scoped>
/*
 * The 3D stage is scoped to the hero: nothing else on the site shares these
 * rules, and keeping them here means the global stylesheet stays about the
 * design system rather than one component's scenery.
 *
 * Every loop is transform-only (compositor work) and is switched off under
 * prefers-reduced-motion, leaving a still, deliberate composition.
 */

.hero-stage-wrap {
    position: absolute;
    top: 50%;
    right: 4%;
    height: 460px;
    width: 460px;
    transform: translateY(-50%);
    perspective: 1400px;
}

/* Holds the 3D space so the stage's own rotation is not flattened. */
.hero-stage-tilt {
    height: 100%;
    width: 100%;
    transform-style: preserve-3d;
}

.hero-stage {
    position: relative;
    height: 100%;
    width: 100%;
    transform-style: preserve-3d;
    /* Pointer-driven lean, written by the script as custom properties. */
    transform: rotateY(var(--tilt-x, 0deg)) rotateX(var(--tilt-y, 0deg));
    transition: transform 700ms cubic-bezier(0.16, 1, 0.3, 1);
}

/* The WebGL system graph fills the stage; the logo card floats in front of
   it, and the ambient glow sits behind both. */
.hero-stage-canvas {
    position: absolute;
    inset: 0;
    z-index: 0;
}

/* With the rings retired the glow has the stage to itself, so it is allowed
   a softer, wider falloff. */
.hero-stage--live .hero-stage-glow {
    filter: blur(36px);
}

.hero-stage-glow {
    position: absolute;
    inset: 2.5rem;
    border-radius: 50%;
    /*
     * Halved in the light theme. `--brand-glow` is a 50%-alpha red, which over
     * a white page turns the whole stage pink and swallows the graph drawn on
     * top of it; two thirds of that reads as warmth without the wash.
     */
    background: radial-gradient(
        circle at 42% 38%,
        color-mix(in oklab, var(--brand-glow) 62%, transparent),
        transparent 66%
    );
    filter: blur(26px);
    animation: hero-glow 6s ease-in-out infinite;
}

.dark .hero-stage-glow {
    background: radial-gradient(
        circle at 42% 38%,
        var(--brand-glow),
        transparent 66%
    );
}

/* ── Tilted orbit rings ────────────────────────────────── */

.hero-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    border-radius: 50%;
    /* Enough contrast to read as a tilted ring on both themes; anything
       fainter disappears against the ambient glow. */
    border: 1px solid color-mix(in oklab, var(--brand) 45%, transparent);
    transform-style: preserve-3d;
}

.hero-ring-1 {
    height: 18rem;
    width: 18rem;
    margin: -9rem 0 0 -9rem;
    transform: rotateX(72deg);
}

.hero-ring-2 {
    height: 24rem;
    width: 24rem;
    margin: -12rem 0 0 -12rem;
    border-color: color-mix(in oklab, var(--chart-5, #8b5cf6) 38%, transparent);
    transform: rotateX(64deg) rotateZ(28deg);
}

/* ── Particles orbiting the rings ──────────────────────── */

.hero-orbit {
    position: absolute;
    top: 50%;
    left: 50%;
    height: 0;
    width: 0;
}

.hero-orbit-1 {
    transform: rotateX(72deg);
}

.hero-orbit-2 {
    transform: rotateX(64deg) rotateZ(28deg);
}

.hero-dot {
    position: absolute;
    display: block;
    height: 0.625rem;
    width: 0.625rem;
    margin: -0.3125rem 0 0 -0.3125rem;
    border-radius: 50%;
    background: var(--brand);
    box-shadow: 0 0 16px 4px var(--brand-glow);
    animation: hero-orbit var(--orbit-duration, 14s) linear infinite;
    will-change: transform;
}

.hero-dot-a {
    --orbit-r: 9rem;
    --orbit-duration: 14s;
}

.hero-dot-b {
    --orbit-r: 12rem;
    --orbit-duration: 22s;
    animation-delay: -7s;
    height: 0.375rem;
    width: 0.375rem;
    margin: -0.1875rem 0 0 -0.1875rem;
    opacity: 0.75;
}

.hero-dot-c {
    --orbit-r: 12rem;
    --orbit-duration: 18s;
    animation-delay: -11s;
    background: var(--chart-5, #8b5cf6);
    box-shadow: 0 0 14px 3px
        color-mix(in oklab, var(--chart-5, #8b5cf6) 55%, transparent);
    height: 0.625rem;
    width: 0.625rem;
    margin: -0.3125rem 0 0 -0.3125rem;
}

/* ── Floating logo card ────────────────────────────────── */

.hero-float-card {
    position: absolute;
    top: 50%;
    left: 50%;
    margin: -5.5rem 0 0 -5.5rem;
    transform-style: preserve-3d;
    animation: hero-float 7s ease-in-out infinite;
    will-change: transform;
}

/* Matches the wide card's box so it stays centred on the stage. */
.hero-float-card--wide {
    margin: -4.5rem 0 0 -6.75rem;
}

.hero-card {
    display: grid;
    place-items: center;
    height: 11rem;
    width: 11rem;
    padding: 1.5rem;
    border-radius: 1.5rem;
    border: 1px solid color-mix(in oklab, var(--border) 85%, transparent);
    /*
     * Opaque on purpose. While it was translucent the bright ambient glow
     * behind the stage bled through unevenly, so the card read as a dark wedge
     * with a visible diagonal edge instead of a clean pane.
     *
     * The backdrop blur went with it: behind an opaque surface it filters
     * nothing, it only cost a composited layer.
     */
    background: var(--card);
    box-shadow:
        inset 0 1px 0 0 color-mix(in oklab, white 10%, transparent),
        0 24px 60px -22px var(--brand-glow);
}

/* Wide variant: the uploaded logos are landscape lockups, so a square tile
   leaves them stranded in empty space on every side. */
.hero-card--wide {
    height: 9rem;
    width: 13.5rem;
    padding: 1.25rem 1.5rem;
}

.hero-card-logo {
    display: block;
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.hero-card-shadow {
    position: absolute;
    bottom: 4rem;
    left: 50%;
    height: 1.25rem;
    width: 13rem;
    margin-left: -6.5rem;
    border-radius: 50%;
    background: color-mix(in oklab, var(--brand) 32%, transparent);
    filter: blur(10px);
    animation: hero-shadow 7s ease-in-out infinite;
}

/* ── Keyframes ─────────────────────────────────────────── */

@keyframes hero-glow {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.55;
    }
}

@keyframes hero-orbit {
    from {
        transform: rotate(0deg) translateX(var(--orbit-r, 9rem)) rotate(0deg);
    }
    to {
        transform: rotate(360deg) translateX(var(--orbit-r, 9rem))
            rotate(-360deg);
    }
}

@keyframes hero-float {
    0%,
    100% {
        transform: translateY(0) rotateX(0deg) rotateY(0deg) rotateZ(0deg);
    }
    25% {
        transform: translateY(-18px) rotateX(8deg) rotateY(-10deg)
            rotateZ(-2deg);
    }
    50% {
        transform: translateY(-6px) rotateX(-6deg) rotateY(8deg) rotateZ(2deg);
    }
    75% {
        transform: translateY(-14px) rotateX(4deg) rotateY(-6deg)
            rotateZ(-1deg);
    }
}

@keyframes hero-shadow {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.5;
    }
    50% {
        transform: scale(0.72);
        opacity: 0.26;
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-stage-glow,
    .hero-dot,
    .hero-float-card,
    .hero-card-shadow {
        animation: none !important;
    }
}
</style>
