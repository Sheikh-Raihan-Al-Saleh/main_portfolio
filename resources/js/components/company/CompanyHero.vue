<script setup lang="ts">
import { ArrowRight, Mail, MapPin, Sparkles } from '@lucide/vue';
import { motion, useTransform } from 'motion-v';
import { computed, ref } from 'vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { blurUp, fadeUp, parallaxFloat, stagger } from '@/lib/motion';
import type { Company, CompanyStats } from '@/types';

type Props = {
    company: Company;
    stats: CompanyStats;
};

const props = defineProps<Props>();

const heroRef = ref<HTMLElement | null>(null);
const { progress } = useScrollProgress({
    target: heroRef,
    offset: ['start start', 'end start'],
});
const heroY = useTransform(progress, [0, 1], [0, 90]);
const heroOpacity = useTransform(progress, [0, 0.85], [1, 0]);
const heroScale = useTransform(progress, [0, 1], [1, 0.985]);

const heroTitle = computed(() =>
    (props.company.hero_title ?? '{{name}} builds software that ships.')
        .replaceAll('{{name}}', props.company.name)
        .trim(),
);

const initials = computed(() => getInitials(props.company.name));

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
    >
        <!-- Background -->
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -top-24 -left-24 h-[520px] w-[520px] rounded-full bg-gradient-to-br from-brand/20 via-brand/10 to-transparent blur-3xl">
                <motion.div
                    class="size-full rounded-full"
                    :animate="parallaxFloat"
                    :transition="{
                        duration: 14,
                        repeat: Infinity,
                        ease: 'easeInOut',
                    }"
                />
            </div>
            <motion.div
                class="absolute top-1/3 -right-24 h-[420px] w-[420px] rounded-full bg-gradient-to-br from-violet-500/15 via-brand/8 to-transparent blur-3xl"
                :animate="{
                    y: [0, -18, 0],
                    x: [0, 10, 0],
                    scale: [1, 1.06, 1],
                }"
                :transition="{
                    duration: 11,
                    repeat: Infinity,
                    ease: 'easeInOut',
                    delay: 0.8,
                }"
            />
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
                            v-if="company.logo_url"
                            :src="company.logo_url"
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
                        <Button
                            v-if="
                                company.primary_cta_url &&
                                company.primary_cta_label
                            "
                            as-child
                            size="lg"
                            class="btn-laravel-primary rounded-lg px-7 shadow-lg shadow-brand/10"
                        >
                            <a :href="company.primary_cta_url">
                                {{ company.primary_cta_label }}
                                <ArrowRight class="size-4" />
                            </a>
                        </Button>
                        <Button
                            v-if="
                                company.secondary_cta_url &&
                                company.secondary_cta_label
                            "
                            as-child
                            size="lg"
                            variant="outline"
                            class="btn-laravel rounded-lg px-7"
                        >
                            <a :href="company.secondary_cta_url">{{
                                company.secondary_cta_label
                            }}</a>
                        </Button>
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

            <!-- Wordmark: the uploaded logo when there is one, initials otherwise. -->
            <div
                class="pointer-events-none absolute right-8 bottom-8 hidden opacity-[0.06] lg:block dark:opacity-[0.1]"
                aria-hidden="true"
            >
                <img
                    v-if="company.logo_url"
                    :src="company.logo_url"
                    alt=""
                    class="h-24 w-auto object-contain"
                />
                <span
                    v-else
                    class="font-display text-[10rem] leading-none font-bold"
                >
                    {{ initials }}
                </span>
            </div>
        </motion.div>
    </section>
</template>
