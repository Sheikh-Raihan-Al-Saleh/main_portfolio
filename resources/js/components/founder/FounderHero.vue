<script setup lang="ts">
import { ArrowUpRight, Download } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import EditorialMarquee from '@/components/site/EditorialMarquee.vue';
import LiquidBlob from '@/components/site/LiquidBlob.vue';
import LiquidImage from '@/components/site/LiquidImage.vue';
import { contentConfig } from '@/lib/content';
import { EASE_OUT } from '@/lib/motion';
import type { PortfolioStats, Profile } from '@/types';

/**
 * The founder's hero, as a magazine cover: a mono masthead (name, role,
 * location, availability), his introduction in giant condensed type with his
 * name on the lime marker, his portrait as a liquid image, then the
 * statement, buttons, figures and main tools, and a running band of roles.
 *
 * Everything comes from the profile. The figures are server-rendered with
 * their real values (the SSR feature test reads that markup).
 */
type Props = {
    profile: Profile;
    stats: PortfolioStats;
    highlights?: string[];
};

const props = withDefaults(defineProps<Props>(), { highlights: () => [] });

const content = computed(() => contentConfig(props.profile));

const roles = computed(() =>
    props.profile.roles?.length
        ? props.profile.roles
        : [props.profile.headline ?? 'Software Engineer'],
);

const firstName = computed(() => props.profile.name?.split(' ')[0] ?? '');

const heroTitle = computed(() =>
    (props.profile.hero_title ?? "Hi, I'm {{name}}.")
        .replaceAll('{{name}}', props.profile.name ?? 'Developer')
        .trim(),
);

/** The title with the founder's name picked out on the marker. */
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

/**
 * The title as words, each flagged when it belongs to the highlighted name.
 * Spacing is kept exactly as typed, so "Nabil." never becomes "Nabil ."
 */
const titleWords = computed(() => {
    const words: { word: string; accent: boolean; space: boolean }[] = [];

    for (const part of titleParts.value) {
        for (const token of part.text.split(/(\s+)/)) {
            if (token === '') {
                continue;
            }

            if (/^\s+$/.test(token)) {
                const last = words.at(-1);

                if (last) {
                    last.space = true;
                }

                continue;
            }

            words.push({ word: token, accent: part.accent, space: false });
        }
    }

    return words;
});

const statement = computed(() =>
    (props.profile.hero_statement ?? props.profile.tagline ?? '').trim(),
);

const tools = computed(() => props.highlights.slice(0, 6));

/** The running band: his roles, topped up with his tools if there is one. */
const band = computed(() =>
    roles.value.length > 1 ? roles.value : [...roles.value, ...tools.value],
);

/** The button draws its own arrow, so drop one typed into the label. */
const primaryLabel = computed(() =>
    content.value.hero.primary_cta_label.replace(/\s*→\s*$/, ''),
);

const masthead = computed(() =>
    [props.profile.name, props.profile.headline, props.profile.location].filter(
        (item): item is string => Boolean(item),
    ),
);

function rise(delay: number) {
    return {
        hidden: { opacity: 0, y: 20 },
        visible: {
            opacity: 1,
            y: 0,
            transition: { duration: 0.9, ease: EASE_OUT, delay },
        },
    };
}
</script>

<template>
    <section class="relative flex min-h-[100svh] flex-col overflow-hidden">
        <div
            class="site-container relative flex flex-1 flex-col pt-28 pb-10 sm:pt-32 lg:pb-14"
        >
            <!-- Masthead -->
            <motion.div
                :variants="rise(0)"
                initial="hidden"
                animate="visible"
                class="ui-label flex flex-wrap items-center justify-between gap-x-8 gap-y-2 border-b border-border pb-4"
            >
                <span class="flex flex-wrap gap-x-6 gap-y-1">
                    <span v-for="item in masthead" :key="item">{{ item }}</span>
                </span>
                <span class="inline-flex items-center gap-2">
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
                </span>
            </motion.div>

            <!-- Name and portrait -->
            <div
                class="grid flex-1 items-center gap-10 py-10 lg:grid-cols-12 lg:gap-8"
            >
                <h1
                    class="ui-display text-[clamp(3.75rem,10.5vw,11rem)] lg:col-span-8"
                    :aria-label="heroTitle"
                >
                    <template v-for="(item, i) in titleWords" :key="i">
                        <span class="ui-word" aria-hidden="true"
                            ><span
                                :class="item.accent ? 'ui-accent' : undefined"
                                :style="{ '--i': i, '--base': '120ms' }"
                                >{{ item.word }}</span
                            ></span
                        >{{ item.space ? ' ' : '' }}
                    </template>
                </h1>

                <motion.div
                    :initial="{ opacity: 0, y: 40 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 1.1, ease: EASE_OUT, delay: 0.3 }"
                    class="relative mx-auto w-full max-w-sm lg:col-span-4 lg:max-w-none"
                >
                    <LiquidImage
                        v-if="profile.avatar_url"
                        :src="profile.avatar_url"
                        :alt="profile.name"
                        eager
                        class="aspect-[4/5] w-full rounded-sm bg-muted"
                    />
                    <LiquidBlob v-else class="aspect-[4/5] w-full" />
                    <span
                        class="ui-label absolute -bottom-3 left-4 bg-[var(--accent-fill)] px-2 py-1 text-[var(--accent-ink)]"
                        >{{ roles[0] }}</span
                    >
                </motion.div>
            </div>

            <!-- Statement, buttons, figures -->
            <motion.div
                initial="hidden"
                animate="visible"
                class="grid gap-8 border-t border-border pt-6 lg:grid-cols-12 lg:items-end"
            >
                <motion.div
                    :variants="rise(0.5)"
                    class="flex flex-col gap-5 lg:col-span-5"
                >
                    <p
                        v-if="statement"
                        class="max-w-md text-lg leading-relaxed text-pretty"
                    >
                        {{ statement }}
                    </p>
                    <ul
                        v-if="tools.length"
                        class="flex flex-wrap gap-1.5"
                        aria-label="Main tools"
                    >
                        <li v-for="tool in tools" :key="tool" class="ui-pill">
                            {{ tool }}
                        </li>
                    </ul>
                </motion.div>

                <motion.div
                    :variants="rise(0.6)"
                    class="flex flex-wrap gap-3 lg:col-span-3"
                >
                    <a
                        :href="content.hero.primary_cta_url || '#projects'"
                        class="ui-btn ui-btn-primary"
                    >
                        {{ primaryLabel }}
                        <ArrowUpRight class="size-4" />
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

                <!--
                    Figures: the span structure (tabular-nums value, then its
                    label) is asserted by the SSR feature test; keep it.
                -->
                <motion.div
                    :variants="rise(0.7)"
                    class="grid grid-cols-3 gap-6 lg:col-span-4"
                >
                    <div class="flex flex-col">
                        <span
                            class="ui-display text-5xl tabular-nums sm:text-6xl"
                            ><AnimatedCounter
                                :value="stats.yearsExperience"
                                suffix="+"
                        /></span>
                        <span class="ui-label text-muted-foreground">{{
                            content.hero.years_label
                        }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="ui-display text-5xl tabular-nums sm:text-6xl"
                            ><AnimatedCounter :value="stats.projects"
                        /></span>
                        <span class="ui-label text-muted-foreground">{{
                            content.hero.projects_label
                        }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="ui-display text-5xl tabular-nums sm:text-6xl"
                            ><AnimatedCounter :value="stats.skills"
                        /></span>
                        <span class="ui-label text-muted-foreground">{{
                            content.hero.skills_label
                        }}</span>
                    </div>
                </motion.div>
            </motion.div>
        </div>

        <EditorialMarquee :items="band" />
    </section>
</template>
