<script setup lang="ts">
import { ArrowRight, Play } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import CodeTerminal from '@/components/site/CodeTerminal.vue';
import UptimeChip from '@/components/site/UptimeChip.vue';
import { useLazyScene } from '@/composables/useLazyScene';
import { EASE_OUT } from '@/lib/motion';
import type { Company, CompanyStats } from '@/types';

/**
 * The studio hero: the company's promise on the left, a glossy WebGL
 * composition on the right, and the live figures underneath. The scene is
 * optional — it loads after mount — and a soft gradient field stands in for
 * it until then (or for good, without WebGL). Every figure is
 * server-rendered with its real value.
 */
type Props = {
    company: Company;
    stats: CompanyStats;
};

const props = defineProps<Props>();

const { scene: Scene, ready, fail } = useLazyScene();

const title = computed(() =>
    (props.company.hero_title ?? '{{name}} builds software that ships.')
        .replaceAll('{{name}}', props.company.name)
        .trim(),
);

/** The title split so its last two words carry the gradient. */
const titleParts = computed(() => {
    const words = title.value.split(/\s+/).filter(Boolean);
    const cut = Math.max(0, words.length - 2);

    return {
        lead: words.slice(0, cut).join(' '),
        mark: words.slice(cut).join(' '),
    };
});

const primaryCta = computed(() => ({
    label: props.company.primary_cta_label ?? 'Start a project',
    url: props.company.primary_cta_url ?? '#contact',
}));

const secondaryCta = computed(() => ({
    label: props.company.secondary_cta_label ?? 'Explore our work',
    url: props.company.secondary_cta_url ?? '#work',
}));

type Figure = { value: number; suffix: string; label: string };

/** Only figures with a value, so the hero never claims "0 years". */
const figures = computed<Figure[]>(() =>
    [
        { value: props.stats.projects, suffix: '+', label: 'Products shipped' },
        { value: props.stats.clients, suffix: '', label: 'Clients served' },
        {
            value: props.stats.yearsInBusiness,
            suffix: '+',
            label: 'Years in business',
        },
    ].filter((figure): figure is Figure =>
        Number.isFinite(figure.value as number),
    ),
);

/** The deploy the floating terminal replays — what shipping looks like here. */
const deployLog = [
    { text: 'git push origin main', kind: 'cmd' as const },
    { text: 'Running tests…', kind: 'dim' as const },
    { text: '✓ All checks passed', kind: 'ok' as const },
    { text: 'Building & deploying…', kind: 'dim' as const },
    { text: '✓ Live in production', kind: 'ok' as const },
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
        <!-- Ambient field -->
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <CircuitBackground />
            <div
                class="ui-blob ui-blob-a -top-24 right-[8%] size-[34rem] opacity-35"
            />
            <div
                class="ui-blob ui-blob-b top-[30%] right-[30%] size-[22rem] opacity-30"
            />
        </div>

        <div
            class="site-container relative grid items-center gap-12 lg:grid-cols-12 lg:gap-8"
        >
            <motion.div
                initial="hidden"
                animate="visible"
                class="flex flex-col items-start gap-7 lg:col-span-6"
            >
                <motion.span :variants="rise(0)" class="ui-eyebrow">
                    <span
                        :class="
                            company.accepting_projects
                                ? 'ui-pulse-dot'
                                : 'size-2 rounded-full bg-muted-foreground'
                        "
                    />
                    {{
                        company.status_text ??
                        (company.accepting_projects
                            ? 'Booking new projects'
                            : 'Fully booked')
                    }}
                </motion.span>

                <motion.h1 :variants="rise(0.08)" class="ui-display ui-h1">
                    {{ titleParts.lead }}
                    <span class="text-gradient">{{ titleParts.mark }}</span>
                </motion.h1>

                <motion.p
                    v-if="company.hero_statement"
                    :variants="rise(0.16)"
                    class="max-w-xl text-lg leading-relaxed text-pretty text-muted-foreground sm:text-xl"
                >
                    {{ company.hero_statement }}
                </motion.p>

                <motion.div :variants="rise(0.24)" class="flex flex-wrap gap-3">
                    <a :href="primaryCta.url" class="ui-btn ui-btn-primary">
                        {{ primaryCta.label }}
                        <ArrowRight class="size-4" />
                    </a>
                    <a :href="secondaryCta.url" class="ui-btn ui-btn-secondary">
                        <Play class="size-3.5 fill-current" />
                        {{ secondaryCta.label }}
                    </a>
                </motion.div>

                <motion.dl
                    v-if="figures.length"
                    :variants="rise(0.32)"
                    class="mt-2 grid w-full max-w-lg grid-cols-3 gap-3"
                >
                    <div
                        v-for="figure in figures"
                        :key="figure.label"
                        class="ui-card flex flex-col-reverse gap-1 rounded-2xl p-4"
                    >
                        <dt class="text-xs text-muted-foreground">
                            {{ figure.label }}
                        </dt>
                        <dd class="ui-display text-2xl sm:text-3xl">
                            <AnimatedCounter
                                :value="figure.value"
                                :suffix="figure.suffix"
                            />
                        </dd>
                    </div>
                </motion.dl>
            </motion.div>

            <!-- The WebGL composition -->
            <div class="relative aspect-square w-full lg:col-span-6">
                <div
                    class="absolute inset-[12%] rounded-full bg-gradient-to-br from-[var(--scene-1)] via-[var(--scene-2)] to-[var(--scene-3)] blur-3xl transition-opacity duration-1000"
                    :class="ready ? 'opacity-25' : 'opacity-40'"
                    aria-hidden="true"
                />
                <div
                    class="absolute inset-0 transition-opacity duration-1000"
                    :class="ready ? 'opacity-100' : 'opacity-0'"
                >
                    <component
                        :is="Scene"
                        v-if="Scene"
                        variant="studio"
                        @ready="ready = true"
                        @failed="fail"
                    />
                </div>
                <!-- Floating widgets: a deploy log and a health check -->
                <div
                    class="ui-float absolute top-[2%] -left-[2%] hidden w-64 sm:block"
                >
                    <CodeTerminal title="deploy — zsh" :lines="deployLog" />
                </div>
                <UptimeChip
                    class="ui-float absolute right-[0%] bottom-[4%] hidden [animation-delay:-3s] sm:block"
                    label="API latency"
                    value="healthy"
                />
            </div>
        </div>
    </section>
</template>
