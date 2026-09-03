<script setup lang="ts">
import { motion, useTransform } from 'motion-v';
import {
    computed,
    defineAsyncComponent,
    onBeforeUnmount,
    onMounted,
    ref,
    shallowRef,
} from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import { Button } from '@/components/ui/button';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { contentConfig } from '@/lib/content';
import { blurUp, fadeUp, stagger } from '@/lib/motion';
import type { PortfolioStats, Profile } from '@/types';

type Props = {
    profile: Profile;
    stats: PortfolioStats;
    highlights?: string[];
};

const props = withDefaults(defineProps<Props>(), { highlights: () => [] });

const roles = computed(() =>
    props.profile.roles?.length
        ? props.profile.roles
        : [props.profile.headline ?? 'Software Engineer'],
);

const roleIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const HeroCanvas = shallowRef<ReturnType<typeof defineAsyncComponent> | null>(
    null,
);

function supportsWebGl(): boolean {
    try {
        const canvas = document.createElement('canvas');

        return Boolean(
            canvas.getContext('webgl2') ?? canvas.getContext('webgl'),
        );
    } catch {
        return false;
    }
}

onMounted(() => {
    const reduced = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    if (!reduced && supportsWebGl()) {
        HeroCanvas.value = defineAsyncComponent(
            () => import('@/components/portfolio/HeroCanvas.vue'),
        );
    }

    if (roles.value.length < 2 || reduced) {
        return;
    }

    timer = setInterval(() => {
        roleIndex.value = (roleIndex.value + 1) % roles.value.length;
    }, 3600);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const firstName = computed(() => {
    const name = props.profile.name ?? 'Developer';

    return name.split(' ')[0] ?? name;
});

const heroTitle = computed(() =>
    (props.profile.hero_title ?? "Hi, I'm {{name}}.")
        .replaceAll('{{name}}', props.profile.name ?? 'Developer')
        .trim(),
);

const heroStatement = computed(() =>
    (props.profile.hero_statement ?? '').trim(),
);

const heroTitleParts = computed(() => {
    const title = heroTitle.value;
    const first = firstName.value;

    if (!first || !title.includes(first)) {
        return [{ text: title, accent: false }];
    }

    const index = title.indexOf(first);

    return [
        { text: title.slice(0, index), accent: false },
        { text: first, accent: true },
        { text: title.slice(index + first.length), accent: false },
    ];
});

const headline = computed(() => props.profile.headline ?? 'Software Engineer');

const marqueeItems = computed(() => [
    props.profile.headline ?? 'Full-Stack Engineering',
    ...props.highlights.slice(0, 8),
]);

const heroRef = ref<HTMLElement | null>(null);
const { progress } = useScrollProgress({
    target: heroRef,
    offset: ['start start', 'end start'],
});
const heroY = useTransform(progress, [0, 1], [0, 100]);
const heroOpacity = useTransform(progress, [0, 0.8], [1, 0]);
const heroScale = useTransform(progress, [0, 1], [1, 0.97]);

const initials = computed(() =>
    props.profile.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join(''),
);

const floatingBadges = computed(() =>
    props.highlights.slice(0, 5).map((h) => h.toLowerCase()),
);

const content = computed(() => contentConfig(props.profile));
</script>

<template>
    <section
        id="hero"
        ref="heroRef"
        class="bg-noise relative min-h-screen overflow-hidden"
    >
        <!-- Background elements -->
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="bg-red-glow absolute inset-0 opacity-60" />
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

        <component :is="HeroCanvas" v-if="HeroCanvas" />

        <motion.div
            :style="{ y: heroY, opacity: heroOpacity, scale: heroScale }"
            class="relative"
        >
            <div
                class="container-laravel flex min-h-screen flex-col justify-center pt-24 pb-16 sm:pt-32"
            >
                <div
                    class="grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16"
                >
                    <!-- Left: Text content -->
                    <motion.div
                        :variants="stagger(0.12)"
                        initial="hidden"
                        animate="visible"
                        class="flex flex-col gap-6"
                    >
                        <!-- Eyebrow -->
                        <motion.div
                            :variants="fadeUp"
                            class="flex items-center gap-3"
                        >
                            <span class="h-px w-10 bg-brand" />
                            <span
                                class="font-mono text-xs tracking-widest text-brand uppercase"
                            >
                                {{ headline }}
                            </span>
                        </motion.div>

                        <!-- Heading — word-by-word reveal -->
                        <motion.h1
                            :variants="blurUp"
                            class="font-display text-4xl leading-[1.1] font-bold tracking-tight text-balance sm:text-5xl md:text-6xl"
                        >
                            <template
                                v-for="(part, index) in heroTitleParts"
                                :key="`part-${index}`"
                            >
                                <span
                                    :class="
                                        part.accent ? 'text-brand' : undefined
                                    "
                                    >{{ part.text }}</span
                                >
                            </template>
                            <template>
                                <br v-if="heroStatement" />
                                <span
                                    v-if="heroStatement"
                                    class="text-xl font-medium text-muted-foreground sm:text-2xl md:text-3xl"
                                >
                                    {{ heroStatement }}
                                </span>
                            </template>
                        </motion.h1>

                        <!-- Subtitle -->
                        <motion.p
                            :variants="fadeUp"
                            class="max-w-lg text-lg leading-relaxed text-muted-foreground"
                        >
                            {{
                                profile.tagline ??
                                'Crafting performant, scalable applications with modern tools and clean architecture.'
                            }}
                        </motion.p>

                        <!-- Animated role — typing style -->
                        <motion.div
                            :variants="fadeUp"
                            class="flex items-center gap-2 font-mono text-sm"
                        >
                            <span class="text-muted-foreground">$</span>
                            <span class="text-foreground/60">echo</span>
                            <span class="font-semibold text-brand">
                                <Transition mode="out-in" name="role">
                                    <span :key="roleIndex">{{
                                        roles[roleIndex]
                                    }}</span>
                                </Transition>
                            </span>
                            <span class="cursor-blink h-4 w-0.5 bg-brand" />
                        </motion.div>

                        <!-- CTAs -->
                        <motion.div
                            :variants="fadeUp"
                            class="flex flex-wrap items-center gap-3 pt-2"
                        >
                            <Button
                                as-child
                                size="lg"
                                class="btn-laravel-primary rounded-lg px-7"
                            >
                                <a :href="content.hero.primary_cta_url">{{
                                    content.hero.primary_cta_label
                                }}</a>
                            </Button>
                            <Button
                                as-child
                                size="lg"
                                variant="outline"
                                class="btn-laravel rounded-lg px-7"
                            >
                                <a :href="content.hero.secondary_cta_url">{{
                                    content.hero.secondary_cta_label
                                }}</a>
                            </Button>
                            <a
                                v-if="profile.resume_url"
                                href="/resume"
                                target="_blank"
                                rel="noopener"
                                class="ml-2 font-mono text-xs text-muted-foreground transition-colors hover:text-foreground"
                            >
                                {{ content.hero.resume_label }}
                            </a>
                        </motion.div>

                        <!-- Stats row -->
                        <motion.div
                            :variants="fadeUp"
                            class="flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-border pt-6"
                        >
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-3xl font-bold tracking-tight tabular-nums"
                                >
                                    <AnimatedCounter
                                        :value="stats.yearsExperience"
                                        suffix="+"
                                    />
                                </span>
                                <span class="text-xs text-muted-foreground">{{
                                    content.hero.years_label
                                }}</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-3xl font-bold tracking-tight tabular-nums"
                                >
                                    <AnimatedCounter :value="stats.projects" />
                                </span>
                                <span class="text-xs text-muted-foreground">{{
                                    content.hero.projects_label
                                }}</span>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-3xl font-bold tracking-tight tabular-nums"
                                >
                                    <AnimatedCounter :value="stats.skills" />
                                </span>
                                <span class="text-xs text-muted-foreground">{{
                                    content.hero.skills_label
                                }}</span>
                            </div>
                        </motion.div>
                    </motion.div>

                    <!-- Right: Visual -->
                    <motion.div
                        :variants="stagger(0.15)"
                        initial="hidden"
                        animate="visible"
                        class="relative mx-auto w-full max-w-md lg:mx-0"
                    >
                        <!-- Profile image with gradient ring -->
                        <motion.div :variants="fadeUp" class="relative">
                            <div
                                class="relative mx-auto w-56 rounded-2xl sm:w-64 md:w-72"
                            >
                                <!-- Gradient border ring with light mode animation -->
                                <motion.div
                                    class="absolute -inset-1 rounded-2xl bg-gradient-to-br from-brand via-brand/50 to-brand opacity-60 blur-sm"
                                    :animate="{ opacity: [0.4, 0.8, 0.4] }"
                                    :transition="{
                                        duration: 3,
                                        repeat: Infinity,
                                        ease: 'easeInOut',
                                    }"
                                />
                                <div
                                    class="relative overflow-hidden rounded-2xl bg-background p-1"
                                >
                                    <div class="overflow-hidden rounded-xl">
                                        <img
                                            v-if="profile.avatar_url"
                                            :src="profile.avatar_url"
                                            :alt="profile.name"
                                            class="aspect-[3/4] w-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="grid aspect-[3/4] w-full place-items-center bg-muted font-mono text-5xl font-bold text-foreground"
                                        >
                                            {{ initials }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floating tech badges with light mode glow -->
                            <motion.span
                                v-for="(badge, index) in floatingBadges"
                                :key="badge"
                                :animate="{ y: [0, -8, 0], rotate: [0, 2, 0] }"
                                :transition="{
                                    duration: 4 + index * 0.5,
                                    repeat: Infinity,
                                    ease: 'easeInOut',
                                    delay: index * 0.4,
                                }"
                                class="badge-float absolute z-10 rounded-lg border border-border bg-background/90 px-3 py-1.5 font-mono text-xs font-medium shadow-lg backdrop-blur-sm"
                                :class="[
                                    index === 0 ? '-top-3 -left-4' : '',
                                    index === 1 ? 'top-8 -right-6' : '',
                                    index === 2 ? 'bottom-20 -left-8' : '',
                                    index === 3 ? '-right-4 -bottom-2' : '',
                                    index === 4
                                        ? 'top-1/2 right-1 sm:-right-10'
                                        : '',
                                ]"
                            >
                                <span class="text-brand">#</span>{{ badge }}
                            </motion.span>
                        </motion.div>

                        <!-- Code card below image with light mode glow -->
                        <motion.div
                            :variants="fadeUp"
                            class="code-window code-window-light mx-auto mt-8 w-full max-w-xs"
                        >
                            <div class="code-window-header">
                                <span class="code-window-dot bg-red-500" />
                                <span class="code-window-dot bg-yellow-500" />
                                <span class="code-window-dot bg-green-500" />
                                <span
                                    class="ml-2 text-xs text-muted-foreground"
                                >
                                    about.ts
                                </span>
                            </div>
                            <div
                                class="code-window-body text-[12px] leading-relaxed"
                            >
                                <p>
                                    <span class="text-syn-keyword"
                                        >export const</span
                                    >
                                    <span class="text-syn-variable">
                                        developer</span
                                    >
                                    <span class="text-syn-operator"> = </span>
                                    <span class="text-syn-operator">{</span>
                                </p>
                                <p class="pl-4">
                                    <span class="text-syn-keyword">name:</span>
                                    <span class="text-syn-string">
                                        "{{ profile.name }}"</span
                                    ><span class="text-syn-operator">,</span>
                                </p>
                                <p class="pl-4">
                                    <span class="text-syn-keyword">focus:</span>
                                    <span class="text-syn-string">
                                        "{{ roles[0] }}"</span
                                    ><span class="text-syn-operator">,</span>
                                </p>
                                <p class="pl-4">
                                    <span class="text-syn-keyword"
                                        >available:</span
                                    >
                                    <span class="text-syn-number">{{
                                        profile.available_for_work
                                    }}</span
                                    ><span class="text-syn-operator">,</span>
                                </p>
                                <p>
                                    <span class="text-syn-operator"
                                        >} as const;</span
                                    >
                                </p>
                            </div>
                        </motion.div>
                    </motion.div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div
                class="absolute bottom-16 left-1/2 -translate-x-1/2 sm:bottom-[4.25rem]"
            >
                <a
                    href="#about"
                    class="flex flex-col items-center gap-2 text-muted-foreground transition-colors hover:text-foreground"
                    aria-label="Scroll down"
                >
                    <span
                        class="font-mono text-[10px] tracking-widest uppercase"
                        >{{ content.hero.scroll_label }}</span
                    >
                    <motion.div
                        :animate="{ y: [0, 6, 0] }"
                        :transition="{
                            duration: 1.5,
                            repeat: Infinity,
                            ease: 'easeInOut',
                        }"
                    >
                        <svg
                            width="16"
                            height="24"
                            viewBox="0 0 16 24"
                            fill="none"
                            class="text-current"
                        >
                            <rect
                                x="1"
                                y="1"
                                width="14"
                                height="22"
                                rx="7"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <motion.circle
                                cx="8"
                                cy="8"
                                r="2"
                                fill="currentColor"
                                :animate="{ cy: [8, 14, 8] }"
                                :transition="{
                                    duration: 1.5,
                                    repeat: Infinity,
                                    ease: 'easeInOut',
                                }"
                            />
                        </svg>
                    </motion.div>
                </a>
            </div>

            <!-- Marquee at bottom -->
            <div class="border-t border-border py-3">
                <div class="animate-marquee flex items-center">
                    <template
                        v-for="(item, index) in [
                            ...marqueeItems,
                            ...marqueeItems,
                        ]"
                        :key="`marquee-${index}`"
                    >
                        <span
                            class="text-sm font-medium whitespace-nowrap text-muted-foreground/60"
                        >
                            {{ item }}
                        </span>
                        <span
                            aria-hidden="true"
                            class="px-6 text-border select-none"
                        >
                            /
                        </span>
                    </template>
                </div>
            </div>
        </motion.div>
    </section>
</template>

<style scoped>
.role-enter-active,
.role-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}
.role-enter-from {
    opacity: 0;
    transform: translateY(6px);
}
.role-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* Light mode enhancements */
:root:not([data-theme="dark"]) {
    --glow-animation: glow-pulse 3s ease-in-out infinite;
}

@keyframes glow-pulse {
    0%, 100% {
        filter: drop-shadow(0 0 8px rgba(99, 102, 241, 0.3));
    }
    50% {
        filter: drop-shadow(0 0 20px rgba(99, 102, 241, 0.6));
    }
}

/* Light mode badge float glow */
:root:not([data-theme="dark"]) .badge-float {
    box-shadow:
        0 4px 12px rgba(99, 102, 241, 0.15),
        inset 0 0 1px rgba(99, 102, 241, 0.1);
    transition: box-shadow 0.6s ease-in-out;
}

:root:not([data-theme="dark"]) .badge-float:hover {
    box-shadow:
        0 8px 24px rgba(99, 102, 241, 0.3),
        inset 0 0 1px rgba(99, 102, 241, 0.2);
}

/* Light mode code window glow and animation */
:root:not([data-theme="dark"]) .code-window-light {
    box-shadow:
        0 8px 32px rgba(99, 102, 241, 0.1),
        inset 0 1px 0 rgba(99, 102, 241, 0.1);
    animation: code-window-glow 4s ease-in-out infinite;
}

@keyframes code-window-glow {
    0%, 100% {
        box-shadow:
            0 8px 32px rgba(99, 102, 241, 0.1),
            inset 0 1px 0 rgba(99, 102, 241, 0.1);
    }
    50% {
        box-shadow:
            0 12px 48px rgba(99, 102, 241, 0.2),
            inset 0 1px 0 rgba(99, 102, 241, 0.15);
    }
}

@media (prefers-reduced-motion: reduce) {
    .role-enter-active,
    .role-leave-active {
        transition: none;
    }
    :root:not([data-theme="dark"]) {
        --glow-animation: none;
    }
}
</style>
