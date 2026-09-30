<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { getInitials } from '@/composables/useInitials';

/**
 * Intro water scene — the site logo floating on water before the home page
 * is revealed.
 *
 * The whole scene is CSS only: a bobbing logo, a masked + blurred mirror
 * below the waterline, expanding surface ripples, drifting caustic light and
 * rising bubbles. Nothing here drives a JavaScript animation loop, and the
 * component is unmounted as soon as it finishes, so the cost is confined to
 * the ~2 seconds it is on screen.
 *
 * Choreography: the logo rises through the surface, holds, then the scene
 * drains upward (clip-path wipe) while the page beneath fades in. The parent
 * reveals content on `exit` rather than `done`, so the content is already
 * arriving as the water clears.
 *
 * Session gating, reduced-motion skipping and the decision to show this at
 * all live in PublicLayout, not here.
 */

type Props = {
    /** Uploaded logo, resolved by the parent. Null falls back to initials. */
    logoUrl?: string | null;
    /** Studio or owner name, shown as the verdict line and used for initials. */
    name?: string | null;
    /**
     * How long the scene holds before the exit wipe begins, in ms. Together
     * with the exit this is the whole intro: keep it short, it stands between
     * the visitor and the page.
     */
    hold?: number;
};

const props = withDefaults(defineProps<Props>(), {
    logoUrl: null,
    name: null,
    hold: 1150,
});

const emit = defineEmits<{ exit: []; done: [] }>();

/** Must stay in step with `water-drain` in the stylesheet below. */
const EXIT_MS = 620;

const phase = ref<'enter' | 'hold' | 'exit'>('enter');

const timers: number[] = [];
let finished = false;

const initials = computed(() => getInitials(props.name ?? '') || '{ }');

/** The progress bar is timed to the full scene, including the exit wipe. */
const totalMs = computed(() => Math.max(900, props.hold) + EXIT_MS);

const sceneStyle = computed(() => ({
    '--intro-total': `${totalMs.value}ms`,
}));

/**
 * Fixed bubble lanes rather than random values: deterministic markup means
 * no hydration mismatch and lets the lanes be tuned by hand.
 */
const bubbles = [
    { left: '14%', delay: -1.2, duration: 7.5, size: 6 },
    { left: '26%', delay: -4.0, duration: 9.2, size: 4 },
    { left: '38%', delay: -6.4, duration: 8.1, size: 8 },
    { left: '58%', delay: -2.6, duration: 10.4, size: 5 },
    { left: '70%', delay: -5.1, duration: 7.8, size: 7 },
    { left: '82%', delay: -7.3, duration: 9.6, size: 4 },
    { left: '90%', delay: -3.4, duration: 11.2, size: 6 },
];

function finish() {
    if (finished) {
        return;
    }

    finished = true;
    phase.value = 'exit';

    unlockScroll();

    emit('exit');

    timers.push(window.setTimeout(() => emit('done'), EXIT_MS));
}

/**
 * Lock the page behind the water, compensating for the scrollbar.
 *
 * Hiding overflow removes the scrollbar, which would otherwise shift the
 * whole layout sideways by its width the moment the intro appears and again
 * when it leaves. Reserving the same space as padding for the duration keeps
 * the page underneath completely still.
 */
function lockScroll() {
    const gap = window.innerWidth - document.documentElement.clientWidth;

    document.documentElement.classList.add('water-intro-lock');

    if (gap > 0) {
        document.documentElement.style.paddingRight = `${gap}px`;
    }
}

function unlockScroll() {
    document.documentElement.classList.remove('water-intro-lock');
    document.documentElement.style.paddingRight = '';
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape' || event.key === 'Enter' || event.key === ' ') {
        finish();
    }
}

onMounted(() => {
    lockScroll();

    window.addEventListener('keydown', onKeydown);

    timers.push(window.setTimeout(() => (phase.value = 'hold'), 460));
    timers.push(window.setTimeout(finish, Math.max(900, props.hold)));
});

onBeforeUnmount(() => {
    timers.forEach((timer) => window.clearTimeout(timer));
    timers.length = 0;

    window.removeEventListener('keydown', onKeydown);

    unlockScroll();
});
</script>

<template>
    <div
        class="water-intro"
        :data-phase="phase"
        :style="sceneStyle"
        role="status"
        aria-live="polite"
        @click="finish"
    >
        <span class="sr-only">
            Loading{{ name ? ` ${name}` : '' }} — press Escape to skip.
        </span>

        <!-- Depth wash: deep water rather than flat black -->
        <div class="water-sky" aria-hidden="true" />

        <!-- Drifting caustic light -->
        <div class="water-caustics" aria-hidden="true">
            <span class="caustic caustic-a" />
            <span class="caustic caustic-b" />
            <span class="caustic caustic-c" />
        </div>

        <!-- The floating logo and its reflection -->
        <div class="water-scene">
            <div class="water-float">
                <div class="water-bob">
                    <div class="water-chip">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="name ?? 'Logo'"
                            class="water-logo"
                            decoding="async"
                        />
                        <span v-else class="water-initials">
                            {{ initials }}
                        </span>
                    </div>
                </div>

                <!-- Mirror: same lockup, flipped, blurred and masked into
                     the water. Decorative, so it is hidden from AT. -->
                <div class="water-mirror" aria-hidden="true">
                    <div class="water-chip water-chip--mirror">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            alt=""
                            class="water-logo"
                            decoding="async"
                        />
                        <span v-else class="water-initials">
                            {{ initials }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Surface highlight + expanding rings -->
            <div class="waterline" aria-hidden="true" />
            <div class="water-rings" aria-hidden="true">
                <span />
                <span />
                <span />
            </div>
        </div>

        <div class="water-bubbles" aria-hidden="true">
            <span
                v-for="(bubble, index) in bubbles"
                :key="index"
                :style="{
                    left: bubble.left,
                    width: `${bubble.size}px`,
                    height: `${bubble.size}px`,
                    animationDuration: `${bubble.duration}s`,
                    animationDelay: `${bubble.delay}s`,
                }"
            />
        </div>

        <div class="water-progress" aria-hidden="true">
            <i />
        </div>

        <div class="water-meta">
            <span class="water-name">{{ name ?? 'Studio' }}</span>
            <button type="button" class="water-skip" @click="finish">
                Skip intro
            </button>
        </div>
    </div>
</template>

<style scoped>
/*
 * The whole scene is scoped to this component: it exists for one moment on
 * a first visit and nothing else on the site shares these rules.
 */

.water-intro {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: grid;
    place-items: center;
    overflow: hidden;
    cursor: pointer;
    background: #04060c;
}

/* ── Depth wash ────────────────────────────────────────── */

.water-sky {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(120% 85% at 50% -10%, #10203c 0%, transparent 60%),
        radial-gradient(90% 60% at 50% 120%, #0a1a2e 0%, transparent 70%),
        linear-gradient(180deg, #060a14 0%, #04060c 70%, #020409 100%);
    transition: opacity 0.4s ease;
}

/* ── Caustic light ─────────────────────────────────────── */

.water-caustics {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.caustic {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    mix-blend-mode: screen;
    opacity: 0.55;
    will-change: transform;
}

.caustic-a {
    top: -18%;
    left: 6%;
    width: 46vw;
    height: 46vw;
    background: radial-gradient(
        circle,
        rgba(56, 189, 248, 0.34),
        transparent 68%
    );
    animation: caustic-a 19s ease-in-out infinite;
}

.caustic-b {
    right: 4%;
    bottom: -20%;
    width: 40vw;
    height: 40vw;
    background: radial-gradient(
        circle,
        color-mix(in oklab, var(--brand) 40%, transparent),
        transparent 68%
    );
    animation: caustic-b 24s ease-in-out infinite;
    animation-delay: -8s;
}

.caustic-c {
    top: 30%;
    left: 40%;
    width: 30vw;
    height: 30vw;
    background: radial-gradient(
        circle,
        rgba(167, 139, 250, 0.28),
        transparent 70%
    );
    animation: caustic-c 30s ease-in-out infinite;
    animation-delay: -14s;
    opacity: 0.4;
}

@keyframes caustic-a {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(6%, 8%, 0) scale(1.12);
    }
}

@keyframes caustic-b {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(-7%, -6%, 0) scale(1.08);
    }
}

@keyframes caustic-c {
    0%,
    100% {
        transform: translate3d(0, 0, 0) scale(1);
    }
    50% {
        transform: translate3d(4%, -9%, 0) scale(1.16);
    }
}

/* ── Scene: logo above the waterline, mirror below ─────── */

.water-scene {
    position: relative;
    display: grid;
    place-items: center;
    padding-bottom: 3rem;
}

.water-float {
    position: relative;
    /* Rises through the surface on arrival. Kept short so the logo settles
       for a beat before the drain — a scene that only arrives as it leaves
       never reads. */
    animation: water-rise 760ms cubic-bezier(0.16, 1, 0.3, 1) both;
    will-change: transform;
}

.water-bob {
    animation: water-bob 5.5s ease-in-out infinite;
    will-change: transform;
}

.water-chip {
    display: grid;
    place-items: center;
    padding: 1.75rem 2.25rem;
    border-radius: 1.5rem;
    background: #ffffff;
    box-shadow:
        0 40px 80px -30px rgba(2, 8, 20, 0.9),
        0 0 0 1px rgba(255, 255, 255, 0.6) inset;
}

.water-logo {
    display: block;
    height: clamp(5rem, 12vh, 9rem);
    width: auto;
    max-width: min(52vw, 30rem);
    object-fit: contain;
}

.water-initials {
    font-family: var(--font-display);
    font-size: clamp(3rem, 9vh, 5.5rem);
    line-height: 1;
    font-weight: 700;
    color: #0b1220;
}

.water-mirror {
    position: absolute;
    /* Sits flush under the chip: the waterline highlight covers the seam, so
       the reflection reads as continuing from the object rather than being a
       separate panel below it. */
    top: 100%;
    left: 0;
    right: 0;
    transform: scaleY(-1);
    filter: blur(14px);
    opacity: 0.22;
    /*
     * Two masks intersected: one fades the reflection with depth, the other
     * fades its left and right edges. Without the second mask the mirrored
     * chip reads as a hard-edged panel of light instead of a reflection — the
     * rectangular edges are what give it away.
     *
     * The element is flipped, so its own bottom edge is what the viewer sees
     * at the waterline: the vertical mask therefore runs "to top" in local
     * space to fade *away* from the surface.
     */
    -webkit-mask-image:
        linear-gradient(to top, rgba(0, 0, 0, 0.95), transparent 55%),
        linear-gradient(
            to right,
            transparent,
            rgba(0, 0, 0, 1) 20%,
            rgba(0, 0, 0, 1) 80%,
            transparent
        );
    -webkit-mask-composite: source-in;
    mask-image:
        linear-gradient(to top, rgba(0, 0, 0, 0.95), transparent 55%),
        linear-gradient(
            to right,
            transparent,
            rgba(0, 0, 0, 1) 20%,
            rgba(0, 0, 0, 1) 80%,
            transparent
        );
    mask-composite: intersect;
    will-change: transform;
    animation: water-mirror 4.5s ease-in-out infinite;
}

.water-chip--mirror {
    box-shadow: none;
    background: #ffffff;
}

/* ── Surface: highlight line and ripples ───────────────── */

.waterline {
    position: absolute;
    top: 100%;
    left: 50%;
    width: min(46rem, 82vw);
    height: 3px;
    transform: translateX(-50%);
    border-radius: 9999px;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(186, 230, 253, 0.85),
        transparent
    );
    filter: blur(1px);
    opacity: 0.75;
}

.water-rings {
    position: absolute;
    /* Rings break from where the object meets the water, not from somewhere
       in the reflection below it. */
    top: calc(100% + 1.25rem);
    left: 50%;
    width: 0;
    height: 0;
    /* Squashed vertically so the rings read as perspective, not circles. */
    transform: scaleY(0.28);
}

.water-rings span {
    position: absolute;
    top: 0;
    left: 0;
    margin: -9rem 0 0 -9rem;
    width: 18rem;
    height: 18rem;
    border: 1px solid rgba(186, 230, 253, 0.22);
    border-radius: 50%;
    opacity: 0;
    animation: water-ripple 4.2s ease-out infinite;
    will-change: transform, opacity;
}

.water-rings span:nth-child(2) {
    animation-delay: 1.4s;
}

.water-rings span:nth-child(3) {
    animation-delay: 2.8s;
}

/* ── Bubbles ───────────────────────────────────────────── */

.water-bubbles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.water-bubbles span {
    position: absolute;
    bottom: -2rem;
    border-radius: 50%;
    background: rgba(224, 242, 254, 0.34);
    box-shadow: 0 0 10px rgba(186, 230, 253, 0.35);
    animation-name: water-bubble;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    will-change: transform, opacity;
}

/* ── Progress + meta row ───────────────────────────────── */

.water-progress {
    position: absolute;
    bottom: 4.25rem;
    left: 50%;
    transform: translateX(-50%);
    width: min(20rem, 58vw);
    height: 2px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.14);
    overflow: hidden;
}

.water-progress i {
    display: block;
    height: 100%;
    width: 100%;
    transform: scaleX(0);
    transform-origin: left;
    background: linear-gradient(
        90deg,
        color-mix(in oklab, var(--brand) 90%, white),
        #7dd3fc
    );
    animation: water-fill var(--intro-total, 1800ms) linear forwards;
}

.water-meta {
    position: absolute;
    bottom: 1.75rem;
    left: 50%;
    display: flex;
    align-items: center;
    gap: 1rem;
    transform: translateX(-50%);
    font-family: var(--font-mono);
    font-size: 0.6875rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(226, 232, 240, 0.55);
}

.water-skip {
    padding: 0.35rem 0.7rem;
    border: 1px solid rgba(226, 232, 240, 0.24);
    border-radius: 9999px;
    color: rgba(226, 232, 240, 0.78);
    transition:
        border-color 0.2s ease,
        color 0.2s ease;
}

.water-skip:hover {
    border-color: rgba(226, 232, 240, 0.55);
    color: #ffffff;
}

/* ── Keyframes ─────────────────────────────────────────── */

@keyframes water-rise {
    from {
        opacity: 0;
        transform: translateY(7rem) scale(0.84);
        filter: blur(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
    }
}

@keyframes water-bob {
    0%,
    100% {
        transform: translateY(0) rotate(-0.8deg);
    }
    50% {
        transform: translateY(-14px) rotate(0.8deg);
    }
}

@keyframes water-mirror {
    0%,
    100% {
        transform: scaleY(-1) skewX(0deg) translateX(0);
    }
    50% {
        transform: scaleY(-1) skewX(2.6deg) translateX(5px);
    }
}

@keyframes water-ripple {
    0% {
        opacity: 0;
        transform: scale(0.35);
    }
    12% {
        opacity: 0.75;
    }
    100% {
        opacity: 0;
        transform: scale(1.5);
    }
}

@keyframes water-bubble {
    0% {
        opacity: 0;
        transform: translateY(0) translateX(0) scale(0.7);
    }
    12% {
        opacity: 0.85;
    }
    100% {
        opacity: 0;
        transform: translateY(-48vh) translateX(14px) scale(1.05);
    }
}

@keyframes water-fill {
    to {
        transform: scaleX(1);
    }
}

@keyframes water-drain {
    from {
        clip-path: inset(0 0 0 0);
    }
    to {
        clip-path: inset(0 0 100% 0);
    }
}

@keyframes water-submerge {
    to {
        opacity: 0;
        transform: translateY(2rem) scale(1.07);
        filter: blur(12px);
    }
}

/* ── Exit: the water drains upward, content arrives beneath ── */

.water-intro[data-phase='exit'] {
    pointer-events: none;
    animation: water-drain 620ms cubic-bezier(0.7, 0, 0.2, 1) forwards;
}

.water-intro[data-phase='exit'] .water-sky {
    opacity: 0;
}

.water-intro[data-phase='exit'] .water-scene {
    animation: water-submerge 620ms ease-in forwards;
}

.water-intro[data-phase='exit'] .water-caustics,
.water-intro[data-phase='exit'] .water-bubbles,
.water-intro[data-phase='exit'] .water-progress {
    opacity: 0;
    transition: opacity 0.35s ease;
}

/* ── Phones and reduced motion: keep it light ──────────── */

@media (max-width: 640px) {
    .water-bubbles,
    .caustic-c {
        display: none;
    }

    .caustic {
        filter: blur(48px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .water-float,
    .water-bob,
    .water-mirror,
    .water-rings span,
    .caustic,
    .water-bubbles span {
        animation: none !important;
    }

    .water-rings span:first-child {
        opacity: 0.5;
    }

    .water-intro[data-phase='exit'],
    .water-intro[data-phase='exit'] .water-scene {
        animation-duration: 1ms;
    }
}
</style>
