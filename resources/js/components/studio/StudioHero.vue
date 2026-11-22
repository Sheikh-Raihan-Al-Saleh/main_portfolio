<script setup lang="ts">
import { ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import EditorialMarquee from '@/components/site/EditorialMarquee.vue';
import HeroHeadline from '@/components/site/HeroHeadline.vue';
import LiquidBlob from '@/components/site/LiquidBlob.vue';
import { EASE_OUT } from '@/lib/motion';
import type { Company, CompanyStats } from '@/types';

/**
 * The studio hero as a poster: a mono masthead (studio, founding year,
 * location, status), the title set in giant condensed type over a flowing
 * liquid shape in the studio's blue, then the promise, the buttons and the
 * live figures on one line, and a blue band of the ticker phrases running
 * underneath.
 *
 * Everything is admin-driven (Company, its stats and ticker). The liquid
 * shape loads when the browser is idle and has a CSS stand-in until then.
 */
type Props = {
    company: Company;
    stats: CompanyStats;
    /** Phrases for the running band under the hero. */
    ticker?: string[];
};

const props = withDefaults(defineProps<Props>(), { ticker: () => [] });

const title = computed(() =>
    (props.company.hero_title ?? '{{name}} builds software that ships.')
        .replaceAll('{{name}}', props.company.name)
        .trim(),
);

const primaryCta = computed(() => ({
    label: props.company.primary_cta_label ?? 'Start a project',
    url: props.company.primary_cta_url ?? '#contact',
}));

const secondaryCta = computed(() => ({
    label: props.company.secondary_cta_label ?? 'See our work',
    url: props.company.secondary_cta_url ?? '#work',
}));

type Figure = { value: number; suffix: string; label: string };

/** Only figures with a value, so the hero never claims "0 years". */
const figures = computed<Figure[]>(() =>
    [
        { value: props.stats.projects, suffix: '+', label: 'Products shipped' },
        { value: props.stats.clients, suffix: '', label: 'Clients' },
        {
            value: props.stats.yearsInBusiness,
            suffix: '+',
            label: 'Years',
        },
    ].filter((figure): figure is Figure =>
        Number.isFinite(figure.value as number),
    ),
);

/** The masthead line: only the facts the admin has filled in. */
const masthead = computed(() =>
    [
        props.company.name,
        props.company.founded_year
            ? `Est. ${props.company.founded_year}`
            : null,
        props.company.location,
    ].filter((item): item is string => Boolean(item)),
);

const status = computed(
    () =>
        props.company.status_text ??
        (props.company.accepting_projects
            ? 'Booking new projects'
            : 'Fully booked'),
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
                            company.accepting_projects
                                ? 'ui-pulse-dot'
                                : 'size-2 rounded-full bg-muted-foreground'
                        "
                    />
                    {{ status }}
                </span>
            </motion.div>

            <!-- Title over the liquid shape -->
            <div class="relative flex flex-1 items-center py-10 lg:py-6">
                <LiquidBlob
                    class="absolute top-1/2 right-[-10%] h-[85%] w-[75%] -translate-y-1/2 sm:right-[-4%] lg:w-[58%]"
                />
                <HeroHeadline
                    :text="title"
                    :highlight="1"
                    :delay="150"
                    class="ui-h1 relative max-w-[16ch]"
                />
            </div>

            <!-- Promise, buttons and figures -->
            <motion.div
                initial="hidden"
                animate="visible"
                class="grid gap-8 border-t border-border pt-6 lg:grid-cols-12 lg:items-end"
            >
                <motion.p
                    v-if="company.hero_statement"
                    :variants="rise(0.5)"
                    class="max-w-md text-lg leading-relaxed text-pretty lg:col-span-4"
                >
                    {{ company.hero_statement }}
                </motion.p>

                <motion.div
                    :variants="rise(0.6)"
                    class="flex flex-wrap gap-3 lg:col-span-4"
                >
                    <a :href="primaryCta.url" class="ui-btn ui-btn-primary">
                        {{ primaryCta.label }}
                        <ArrowUpRight class="size-4" />
                    </a>
                    <a :href="secondaryCta.url" class="ui-btn ui-btn-secondary">
                        {{ secondaryCta.label }}
                    </a>
                </motion.div>

                <motion.dl
                    v-if="figures.length"
                    :variants="rise(0.7)"
                    class="flex gap-8 lg:col-span-4 lg:justify-end"
                >
                    <div
                        v-for="figure in figures"
                        :key="figure.label"
                        class="flex flex-col-reverse"
                    >
                        <dt class="ui-label text-muted-foreground">
                            {{ figure.label }}
                        </dt>
                        <dd class="ui-display text-5xl sm:text-6xl">
                            <AnimatedCounter
                                :value="figure.value"
                                :suffix="figure.suffix"
                            />
                        </dd>
                    </div>
                </motion.dl>
            </motion.div>
        </div>

        <EditorialMarquee :items="ticker" />
    </section>
</template>
