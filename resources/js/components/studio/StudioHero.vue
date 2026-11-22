<script setup lang="ts">
import { ArrowDownRight, ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import AnimatedCounter from '@/components/site/AnimatedCounter.vue';
import LiquidBlob from '@/components/site/LiquidBlob.vue';
import Marquee from '@/components/site/Marquee.vue';
import { useSite } from '@/composables/useSite';
import { EASE_OUT } from '@/lib/motion';
import { fillTokens } from '@/lib/text';
import type { StudioHeroProps } from '@/types';

/**
 * The studio home hero: a huge editorial headline, the brand masthead with
 * the logo shown large, the headline numbers, and a ticker underneath.
 * The liquid shape behind it is drawn by three.js (lib/liquid/engine.ts).
 */
const props = defineProps<StudioHeroProps>();

const { studio } = useSite();

const title = computed(() =>
    fillTokens(
        studio.value.hero.title || '{{name}} builds software that ships.',
        {
            name: studio.value.name,
        },
    ),
);

/** The last two words of the headline are set in the accent italic. */
const headline = computed(() => {
    const words = title.value.trim().split(/\s+/);
    const split = Math.max(words.length - 2, 1);

    return {
        lead: words.slice(0, split).join(' '),
        accent: words.slice(split).join(' '),
    };
});

const figures = computed(() =>
    [
        {
            value: props.stats.yearsInBusiness,
            suffix: '+',
            label: 'Years building',
        },
        { value: props.stats.projects, suffix: '', label: 'Projects shipped' },
        { value: props.stats.clients, suffix: '', label: 'Teams served' },
    ].filter(
        (figure): figure is { value: number; suffix: string; label: string } =>
            figure.value !== null,
    ),
);

const reveal = (delay: number) => ({
    initial: { opacity: 0, y: 40 },
    animate: { opacity: 1, y: 0 },
    transition: { duration: 1, ease: EASE_OUT, delay },
});
</script>

<template>
    <section class="relative overflow-hidden" aria-labelledby="hero-title">
        <div
            class="bg-grid pointer-events-none absolute inset-0 opacity-60"
            aria-hidden="true"
        />
        <LiquidBlob
            class="pointer-events-none absolute -top-16 -right-32 size-[22rem] opacity-70 sm:size-[46rem] sm:opacity-90 lg:-right-24"
        />

        <div class="container-site relative pt-10 pb-16 sm:pt-16 lg:pt-20">
            <motion.p v-bind="reveal(0.1)" class="eyebrow">
                {{ studio.hero.eyebrow || studio.headline || 'Product studio' }}
                <span v-if="studio.location" class="text-stone/70"
                    >— {{ studio.location }}</span
                >
            </motion.p>

            <h1 id="hero-title" class="display display-xl mt-8 max-w-[14ch]">
                <motion.span v-bind="reveal(0.2)" class="block">{{
                    headline.lead
                }}</motion.span>
                <motion.em v-bind="reveal(0.35)" class="block">{{
                    headline.accent
                }}</motion.em>
            </h1>

            <div class="mt-14 grid gap-12 lg:grid-cols-12 lg:items-start">
                <motion.div v-bind="reveal(0.5)" class="lg:col-span-6">
                    <p class="lead max-w-xl">
                        {{ studio.hero.statement || studio.tagline }}
                    </p>

                    <div class="mt-10 flex flex-wrap gap-3">
                        <a
                            v-if="studio.hero.primary_cta.label"
                            :href="studio.hero.primary_cta.url || '#contact'"
                            class="btn btn-ink"
                        >
                            {{ studio.hero.primary_cta.label }}
                            <ArrowUpRight
                                class="btn-arrow size-4"
                                aria-hidden="true"
                            />
                        </a>
                        <a
                            v-if="studio.hero.secondary_cta.label"
                            :href="studio.hero.secondary_cta.url || '#work'"
                            class="btn btn-outline"
                        >
                            {{ studio.hero.secondary_cta.label }}
                            <ArrowDownRight
                                class="btn-arrow size-4"
                                aria-hidden="true"
                            />
                        </a>
                    </div>
                </motion.div>

                <!-- Masthead: the brand, shown large, with live availability. -->
                <motion.aside
                    v-bind="reveal(0.65)"
                    class="paper-card relative flex flex-col gap-8 p-6 sm:p-8 lg:col-span-5 lg:col-start-8 lg:-mt-48"
                    aria-label="Studio at a glance"
                >
                    <div class="flex items-start justify-between gap-6">
                        <BrandLogo variant="mark" size="hero" />
                        <p
                            class="inline-flex items-center gap-2 text-sm text-ink-soft"
                        >
                            <span
                                class="status-dot"
                                :class="
                                    !studio.is_accepting_projects &&
                                    'status-dot-off'
                                "
                            />
                            {{
                                studio.is_accepting_projects
                                    ? 'Booking projects'
                                    : 'Fully booked'
                            }}
                        </p>
                    </div>

                    <div>
                        <p class="display text-4xl sm:text-5xl">
                            {{ studio.name }}
                        </p>
                        <p v-if="studio.tagline" class="body-copy mt-3">
                            {{ studio.tagline }}
                        </p>
                    </div>

                    <dl
                        v-if="figures.length"
                        class="rule grid grid-cols-3 gap-4 pt-6"
                    >
                        <div v-for="figure in figures" :key="figure.label">
                            <dt class="meta">{{ figure.label }}</dt>
                            <dd class="display mt-2 text-4xl sm:text-5xl">
                                <AnimatedCounter
                                    :value="figure.value"
                                    :suffix="figure.suffix"
                                />
                            </dd>
                        </div>
                    </dl>
                </motion.aside>
            </div>
        </div>

        <div
            v-if="ticker.length"
            data-tone="dark"
            class="rule-ink border-b border-ink bg-ink py-5 text-paper"
        >
            <Marquee :duration="45" gap="2.5rem">
                <span
                    v-for="item in ticker"
                    :key="item"
                    class="display flex items-center gap-10 text-3xl whitespace-nowrap sm:text-4xl"
                >
                    {{ item }}
                    <span class="text-brand" aria-hidden="true">✦</span>
                </span>
            </Marquee>
        </div>
    </section>
</template>
