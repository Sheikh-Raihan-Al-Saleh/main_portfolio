<script setup lang="ts">
import { ArrowRight, Download, MapPin } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import CodeTerminal from '@/components/site/CodeTerminal.vue';
import { useLazyScene } from '@/composables/useLazyScene';
import { contentConfig } from '@/lib/content';
import { EASE_OUT } from '@/lib/motion';
import type { PortfolioStats, Profile } from '@/types';

/**
 * The founder's hero: introduction, rotating roles and figures on the left;
 * the portrait on the right, framed by glossy WebGL shapes and floating skill
 * chips. The figures are server-rendered with their real values.
 */
type Props = {
    profile: Profile;
    stats: PortfolioStats;
    highlights?: string[];
};

const props = withDefaults(defineProps<Props>(), { highlights: () => [] });

const content = computed(() => contentConfig(props.profile));

const { scene: Scene, ready, fail } = useLazyScene();

const roles = computed(() =>
    props.profile.roles?.length
        ? props.profile.roles
        : [props.profile.headline ?? 'Software Engineer'],
);

const roleIndex = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    if (
        roles.value.length < 2 ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        return;
    }

    timer = setInterval(() => {
        roleIndex.value = (roleIndex.value + 1) % roles.value.length;
    }, 3000);
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const firstName = computed(() => props.profile.name?.split(' ')[0] ?? '');

const heroTitle = computed(() =>
    (props.profile.hero_title ?? "Hi, I'm {{name}}.")
        .replaceAll('{{name}}', props.profile.name ?? 'Developer')
        .trim(),
);

/** The title with the founder's name picked out in the gradient. */
const titleParts = computed(() => {
    const title = heroTitle.value;
    const name = props.profile.name ?? '';
    const target = name && title.includes(name) ? name : firstName.value;
    const index = target ? title.indexOf(target) : -1;

    if (index < 0) {
        return [{ text: title, accent: false }];
    }

    return [
        { text: title.slice(0, index), accent: false },
        { text: target, accent: true },
        { text: title.slice(index + target.length), accent: false },
    ];
});

const statement = computed(() =>
    (props.profile.hero_statement ?? props.profile.tagline ?? '').trim(),
);

const chips = computed(() => props.highlights.slice(0, 3));

/** The session the floating terminal replays, built from the profile. */
const sessionLog = computed(() => [
    { text: 'whoami', kind: 'cmd' as const },
    { text: props.profile.name ?? 'developer', kind: 'out' as const },
    { text: 'cat stack.txt', kind: 'cmd' as const },
    {
        text:
            props.highlights.slice(0, 3).join(' · ').toLowerCase() ||
            'laravel · vue',
        kind: 'dim' as const,
    },
    {
        text: props.profile.available_for_work
            ? '✓ open to work'
            : '● currently booked',
        kind: 'ok' as const,
    },
]);

/** The button draws its own arrow, so drop one typed into the label. */
const primaryLabel = computed(() =>
    content.value.hero.primary_cta_label.replace(/\s*→\s*$/, ''),
);

const chipPositions = [
    'top-[6%] -left-[6%]',
    'top-[38%] -right-[8%]',
    '-bottom-[3%] right-[10%]',
];

function rise(delay: number) {
    return {
        hidden: { opacity: 0, y: 28 },
        visible: {
            opacity: 1,
            y: 0,
            transition: { duration: 0.9, ease: EASE_OUT, delay },
        },
    };
}
</script>

<template>
    <section class="relative overflow-hidden pt-32 pb-16 lg:pt-40 lg:pb-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <CircuitBackground flip />
            <div
                class="ui-blob ui-blob-a -top-24 right-[6%] size-[32rem] opacity-35"
            />
            <div
                class="ui-blob ui-blob-b top-[40%] -left-24 size-[22rem] opacity-30"
            />
        </div>

        <div
            class="site-container relative grid items-center gap-14 lg:grid-cols-12 lg:gap-10"
        >
            <motion.div
                initial="hidden"
                animate="visible"
                class="relative z-10 flex flex-col items-start gap-6 lg:col-span-7"
            >
                <motion.span :variants="rise(0)" class="ui-eyebrow">
                    <span
                        :class="
                            profile.available_for_work
                                ? 'ui-pulse-dot'
                                : 'size-2 rounded-full bg-muted-foreground'
                        "
                    />
                    {{
                        profile.available_for_work
                            ? content.about.available_open
                            : content.about.available_closed
                    }}
                </motion.span>

                <motion.h1 :variants="rise(0.08)" class="ui-display ui-h1">
                    <template v-for="(part, i) in titleParts" :key="i">
                        <span v-if="part.accent" class="text-gradient">{{
                            part.text
                        }}</span>
                        <template v-else>{{ part.text }}</template>
                    </template>
                </motion.h1>

                <motion.p
                    :variants="rise(0.14)"
                    class="flex h-9 items-center gap-3 overflow-hidden text-xl font-semibold sm:text-2xl"
                >
                    <span
                        class="h-px w-8 bg-gradient-to-r from-[var(--grad-from)] to-[var(--grad-to)]"
                        aria-hidden="true"
                    />
                    <AnimatePresence mode="wait">
                        <motion.span
                            :key="roleIndex"
                            :initial="{ opacity: 0, y: 16 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :exit="{ opacity: 0, y: -16 }"
                            :transition="{ duration: 0.4 }"
                            class="text-muted-foreground"
                        >
                            {{ roles[roleIndex] }}
                        </motion.span>
                    </AnimatePresence>
                </motion.p>

                <motion.p
                    v-if="statement"
                    :variants="rise(0.2)"
                    class="max-w-xl text-lg leading-relaxed text-pretty text-muted-foreground"
                >
                    {{ statement }}
                </motion.p>

                <motion.div :variants="rise(0.26)" class="flex flex-wrap gap-3">
                    <a
                        :href="content.hero.primary_cta_url || '#projects'"
                        class="ui-btn ui-btn-primary"
                    >
                        {{ primaryLabel }}
                        <ArrowRight class="size-4" />
                    </a>
                    <a
                        :href="content.hero.secondary_cta_url || '#contact'"
                        class="ui-btn ui-btn-secondary"
                    >
                        {{ content.hero.secondary_cta_label }}
                    </a>
                    <a
                        v-if="profile.resume_url"
                        href="/resume"
                        target="_blank"
                        rel="noopener"
                        class="ui-btn ui-btn-secondary"
                    >
                        <Download class="size-4" />
                        {{ content.hero.resume_label }}
                    </a>
                </motion.div>

                <motion.div
                    :variants="rise(0.32)"
                    class="mt-2 grid w-full max-w-lg grid-cols-3 gap-3"
                >
                    <div class="ui-card flex flex-col gap-1 rounded-2xl p-4">
                        <span
                            class="ui-display text-2xl tabular-nums sm:text-3xl"
                            ><AnimatedCounter
                                :value="stats.yearsExperience"
                                suffix="+"
                        /></span>
                        <span class="text-xs text-muted-foreground">{{
                            content.hero.years_label
                        }}</span>
                    </div>
                    <div class="ui-card flex flex-col gap-1 rounded-2xl p-4">
                        <span
                            class="ui-display text-2xl tabular-nums sm:text-3xl"
                            ><AnimatedCounter :value="stats.projects"
                        /></span>
                        <span class="text-xs text-muted-foreground">{{
                            content.hero.projects_label
                        }}</span>
                    </div>
                    <div class="ui-card flex flex-col gap-1 rounded-2xl p-4">
                        <span
                            class="ui-display text-2xl tabular-nums sm:text-3xl"
                            ><AnimatedCounter :value="stats.skills"
                        /></span>
                        <span class="text-xs text-muted-foreground">{{
                            content.hero.skills_label
                        }}</span>
                    </div>
                </motion.div>
            </motion.div>

            <!-- Portrait in its 3D field -->
            <div
                class="relative mx-auto w-full max-w-md lg:col-span-5 lg:max-w-none"
            >
                <div
                    class="absolute -inset-x-[28%] -inset-y-[22%] transition-opacity duration-1000"
                    :class="ready ? 'opacity-100' : 'opacity-0'"
                >
                    <component
                        :is="Scene"
                        v-if="Scene"
                        variant="founder"
                        @ready="ready = true"
                        @failed="fail"
                    />
                </div>

                <motion.div
                    :initial="{ opacity: 0, scale: 0.94, y: 24 }"
                    :animate="{ opacity: 1, scale: 1, y: 0 }"
                    :transition="{ duration: 1, ease: EASE_OUT, delay: 0.15 }"
                    class="relative"
                >
                    <div
                        class="ui-card relative overflow-hidden rounded-[2rem] p-2 shadow-[var(--shadow-lift)]"
                    >
                        <div
                            class="relative aspect-[4/5] overflow-hidden rounded-[1.6rem] bg-muted"
                        >
                            <img
                                v-if="profile.avatar_url"
                                :src="profile.avatar_url"
                                :alt="profile.name"
                                class="size-full object-cover"
                            />
                            <span
                                v-else
                                class="ui-display text-gradient grid size-full place-items-center text-[8rem]"
                            >
                                {{ firstName.charAt(0) }}
                            </span>
                            <div
                                class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent"
                                aria-hidden="true"
                            />
                            <div
                                class="absolute right-4 bottom-4 left-4 flex items-end justify-between gap-3 text-white"
                            >
                                <div>
                                    <p class="text-lg font-semibold">
                                        {{ profile.name }}
                                    </p>
                                    <p
                                        v-if="profile.headline"
                                        class="text-sm text-white/80"
                                    >
                                        {{ profile.headline }}
                                    </p>
                                </div>
                                <span
                                    v-if="profile.location"
                                    class="inline-flex items-center gap-1 text-xs text-white/80"
                                >
                                    <MapPin class="size-3.5" />
                                    {{ profile.location }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <span
                        v-for="(chip, i) in chips"
                        :key="chip"
                        :class="[
                            chipPositions[i],
                            'ui-glass ui-float absolute hidden rounded-full px-3.5 py-2 text-sm font-semibold shadow-[var(--shadow-soft)] sm:inline-flex',
                        ]"
                        :style="{ animationDelay: `${i * -1.5}s` }"
                    >
                        <span
                            class="mr-2 inline-block size-2 self-center rounded-full bg-gradient-to-br from-[var(--grad-from)] to-[var(--grad-to)]"
                        />
                        {{ chip }}
                    </span>

                    <!-- A terminal session answering "who is this?" -->
                    <div
                        class="ui-float absolute top-[36%] -left-[18%] hidden w-56 [animation-delay:-2s] lg:block"
                    >
                        <CodeTerminal title="~/portfolio" :lines="sessionLog" />
                    </div>
                </motion.div>
            </div>
        </div>
    </section>
</template>
