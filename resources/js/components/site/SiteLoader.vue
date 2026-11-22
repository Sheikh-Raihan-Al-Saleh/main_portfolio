<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import type { PublicNav } from '@/composables/usePublicNav';

/**
 * The first-visit boot screen.
 *
 * Everything on it is real data: the uploaded logo (or the owner's initials),
 * the owner's name, and boot lines built from the page being opened — its
 * project, client, skill and year counts, or a case study's stack. Progress
 * follows the page actually loading (mount, then web fonts, then the window
 * load event) rather than a fixed timer.
 *
 * Shown once per browser session. It can never trap the page: it is capped
 * at a few seconds, a CSS failsafe hides it if JavaScript never runs its
 * exit, and app.blade.php hides it entirely when scripting is off.
 */
type Props = {
    nav: PublicNav;
};

const props = defineProps<Props>();

const page = usePage();

const SEEN_KEY = 'site-loader-seen';
const MIN_MS = 1100;
const MAX_MS = 3500;

const visible = ref(true);
const leaving = ref(false);
const progress = ref(0);

const name = computed(() => props.nav.owner.value?.name ?? '');

type Line = { text: string; kind: 'cmd' | 'ok' };

function count(value: unknown): number | null {
    return typeof value === 'number' && value > 0 ? value : null;
}

/** Boot lines for the page being opened, from its own props. */
const lines = computed<Line[]>(() => {
    const p = page.props as Record<string, unknown>;
    const stats = (p.stats ?? {}) as Record<string, unknown>;
    const project = p.project as
        { title?: string; tech_stack?: string[] | null } | undefined;
    const out: Line[] = [];

    if (project?.title && p.landingPage) {
        out.push({ text: `open ${project.title}`, kind: 'cmd' });

        const stack = (project.tech_stack ?? []).slice(0, 3);

        if (stack.length) {
            out.push({ text: `stack: ${stack.join(' · ')}`, kind: 'ok' });
        }

        out.push({ text: 'Rendering case study', kind: 'ok' });

        return out;
    }

    if (props.nav.isCompanySite.value) {
        out.push({ text: `boot ${name.value || 'studio'}`, kind: 'cmd' });

        const projects = count(stats.projects);
        const clients = count(stats.clients);
        const years = count(stats.yearsInBusiness);

        if (projects) {
            out.push({
                text: `${projects} products in production`,
                kind: 'ok',
            });
        }

        if (clients) {
            out.push({ text: `${clients} client partnerships`, kind: 'ok' });
        }

        if (years) {
            out.push({ text: `${years}+ years shipping software`, kind: 'ok' });
        }
    } else {
        out.push({ text: `whoami → ${name.value || 'founder'}`, kind: 'cmd' });

        const projects = count(stats.projects);
        const skills = count(stats.skills);
        const years = count(stats.yearsExperience);

        if (projects) {
            out.push({ text: `${projects} projects loaded`, kind: 'ok' });
        }

        if (skills) {
            out.push({ text: `${skills} skills compiled`, kind: 'ok' });
        }

        if (years) {
            out.push({ text: `${years}+ years of experience`, kind: 'ok' });
        }
    }

    out.push({ text: 'All systems online', kind: 'ok' });

    return out;
});

/** Lines appear as loading advances, the last one only at the very end. */
const shownLines = computed(() => {
    const total = lines.value.length;
    const shown = Math.ceil((progress.value / 100) * total);

    return lines.value.slice(0, Math.max(1, shown));
});

let target = 0;
let raf: number | null = null;
let lastFrame = 0;
let startedAt = 0;
let capTimer: ReturnType<typeof setTimeout> | null = null;
let finishing = false;

/** How long the bar takes to run up to 100% once loading is done. */
const SETTLE_MS = 450;

function advance(to: number) {
    target = Math.max(target, to);

    // The exit runs on a timer, never on animation frames: on a busy or slow
    // device frames can crawl, and the boot screen must still leave on time.
    if (target >= 100 && !finishing) {
        finishing = true;
        setTimeout(finish, SETTLE_MS);
    }
}

function animate(now: number) {
    // Ease the shown value toward the real target, by elapsed time rather
    // than per frame, so a low frame rate does not slow the bar down.
    const dt = lastFrame ? Math.min(now - lastFrame, 100) : 16;
    lastFrame = now;
    progress.value += (target - progress.value) * (1 - Math.exp(-dt / 90));

    raf = requestAnimationFrame(animate);
}

function finish() {
    progress.value = 100;

    if (raf !== null) {
        cancelAnimationFrame(raf);
        raf = null;
    }

    const wait = Math.max(0, MIN_MS - (performance.now() - startedAt));

    setTimeout(leave, wait);
}

function leave() {
    if (leaving.value) {
        return;
    }

    leaving.value = true;

    try {
        sessionStorage.setItem(SEEN_KEY, '1');
    } catch {
        // Storage can be blocked; the loader simply shows again next visit.
    }

    setTimeout(() => {
        visible.value = false;
    }, 650);
}

function onWindowLoad() {
    advance(100);
}

onMounted(() => {
    let seen = false;

    try {
        seen = sessionStorage.getItem(SEEN_KEY) === '1';
    } catch {
        seen = false;
    }

    if (seen) {
        visible.value = false;

        return;
    }

    startedAt = performance.now();
    advance(35);
    raf = requestAnimationFrame(animate);

    document.fonts?.ready.then(() => advance(70)).catch(() => advance(70));

    if (document.readyState === 'complete') {
        advance(100);
    } else {
        window.addEventListener('load', onWindowLoad, { once: true });
    }

    // Never hold the page hostage to a slow image or third-party script.
    capTimer = setTimeout(() => advance(100), MAX_MS);
});

onBeforeUnmount(() => {
    window.removeEventListener('load', onWindowLoad);

    if (raf !== null) {
        cancelAnimationFrame(raf);
    }

    if (capTimer !== null) {
        clearTimeout(capTimer);
    }
});
</script>

<template>
    <div
        v-if="visible"
        class="site-loader fixed inset-0 z-[200] flex flex-col overflow-hidden bg-background text-foreground"
        :class="{ 'site-loader--leaving': leaving }"
        role="status"
        aria-live="polite"
        data-testid="site-loader"
    >
        <span class="sr-only">Loading {{ name }}</span>

        <div
            class="site-container flex flex-1 flex-col justify-between py-8 sm:py-10"
            aria-hidden="true"
        >
            <!-- Brand -->
            <div class="flex items-center justify-between gap-6">
                <span
                    v-if="nav.brandLogoUrl.value"
                    class="inline-flex items-center rounded-sm bg-white px-4 py-2.5 ring-1 ring-black/10"
                >
                    <img
                        :src="nav.brandLogoUrl.value"
                        :alt="name"
                        class="h-16 w-auto max-w-[14rem] object-contain sm:h-20 sm:max-w-[18rem]"
                    />
                </span>
                <span
                    v-else
                    class="ui-display grid size-20 place-items-center bg-[var(--accent-fill)] text-4xl text-[var(--accent-ink)]"
                >
                    {{ nav.initials.value || '{ }' }}
                </span>
                <p v-if="name" class="ui-label text-right">{{ name }}</p>
            </div>

            <!-- Boot log and the counter -->
            <div class="flex flex-wrap items-end justify-between gap-8">
                <div class="ui-label flex min-w-0 flex-col gap-1.5">
                    <p
                        v-for="(line, i) in shownLines"
                        :key="i"
                        class="site-loader__line truncate"
                        :class="
                            line.kind === 'cmd'
                                ? 'text-foreground'
                                : 'text-muted-foreground'
                        "
                    >
                        <span>{{ line.kind === 'cmd' ? '→ ' : '✓ ' }}</span
                        >{{ line.text }}
                    </p>
                </div>
                <p
                    class="ui-display text-[clamp(6rem,22vw,20rem)] leading-[0.8] tabular-nums"
                >
                    {{ Math.round(progress) }}%
                </p>
            </div>
        </div>

        <!-- Progress -->
        <div class="h-2 w-full bg-muted" aria-hidden="true">
            <div
                class="h-full bg-[var(--accent-fill)]"
                :style="{ width: `${Math.round(progress)}%` }"
            />
        </div>
    </div>
</template>

<style scoped>
/* If the client never runs the exit (a script error, a blocked bundle), the
   overlay still steps aside on its own. */
.site-loader {
    animation: site-loader-failsafe 0.01s linear 6s forwards;
    transition: transform 0.65s cubic-bezier(0.77, 0, 0.18, 1);
}

/* The exit: the whole sheet lifts away like a page being turned up. */
.site-loader--leaving {
    transform: translateY(-100%);
    pointer-events: none;
}

@keyframes site-loader-failsafe {
    to {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
}

.site-loader__line {
    animation: site-loader-line 0.35s ease-out both;
}

@keyframes site-loader-line {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .site-loader__line {
        animation: none;
    }

    .site-loader {
        transition: opacity 0.3s ease;
    }

    .site-loader--leaving {
        transform: none;
        opacity: 0;
    }
}
</style>
