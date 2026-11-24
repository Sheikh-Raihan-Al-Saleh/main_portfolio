<script setup lang="ts">
import { ArrowDownRight, ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import BrandLogo from '@/components/BrandLogo.vue';
import AnimatedCounter from '@/components/Site/AnimatedCounter.vue';
import Marquee from '@/components/Site/Marquee.vue';
import { EASE_OUT } from '@/composables/motion';
import { fillTokens } from '@/composables/text';
import { useSite } from '@/composables/useSite';
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

/** The last two words of the headline get the honey marker highlight. */
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
            class="container-site relative grid gap-12 pt-8 pb-16 sm:pt-12 lg:grid-cols-12 lg:items-center lg:gap-10 lg:pt-16 lg:pb-24"
        >
            <div class="lg:col-span-7">
                <motion.p v-bind="reveal(0.1)" class="eyebrow">
                    {{
                        studio.hero.eyebrow ||
                        studio.headline ||
                        'Product studio'
                    }}
                    <span v-if="studio.location" class="font-medium opacity-80"
                        >· {{ studio.location }}</span
                    >
                </motion.p>

                <h1
                    id="hero-title"
                    class="display display-xl mt-6 max-w-[15ch]"
                >
                    <motion.span v-bind="reveal(0.2)">{{
                        headline.lead
                    }}</motion.span>
                    {{ ' ' }}
                    <motion.em v-bind="reveal(0.35)">{{
                        headline.accent
                    }}</motion.em>
                </h1>

                <motion.div v-bind="reveal(0.5)">
                    <p class="lead mt-7 max-w-xl">
                        {{ studio.hero.statement || studio.tagline }}
                    </p>

                    <div class="mt-9 flex flex-wrap gap-3">
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
            </div>

            <!-- The studio at a glance: brand, live availability, key figures. -->
            <motion.aside
                v-bind="reveal(0.65)"
                class="paper-card relative flex flex-col gap-7 rounded-[1.75rem] p-6 sm:p-8 lg:col-span-5"
                aria-label="Studio at a glance"
            >
                <div class="flex items-start justify-between gap-6">
                    <BrandLogo variant="mark" size="hero" />
                    <p
                        class="inline-flex items-center gap-2 rounded-full bg-paper px-3 py-1.5 text-sm font-medium text-ink-soft"
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
                    <p class="display text-3xl sm:text-4xl">
                        {{ studio.name }}
                    </p>
                    <p v-if="studio.tagline" class="body-copy mt-3">
                        {{ studio.tagline }}
                    </p>
                </div>

                <dl v-if="figures.length" class="grid grid-cols-3 gap-3">
                    <div
                        v-for="figure in figures"
                        :key="figure.label"
                        class="rounded-2xl bg-paper p-4"
                    >
                        <dt class="meta">{{ figure.label }}</dt>
                        <dd class="display mt-1 text-3xl sm:text-4xl">
                            <AnimatedCounter
                                :value="figure.value"
                                :suffix="figure.suffix"
                            />
                        </dd>
                    </div>
                </dl>
            </motion.aside>
        </div>

        <div
            v-if="ticker.length"
            data-tone="dark"
            class="bg-ink py-5 text-paper"
        >
            <Marquee :duration="45" gap="2.5rem">
                <span
                    v-for="item in ticker"
                    :key="item"
                    class="display flex items-center gap-10 text-2xl whitespace-nowrap sm:text-3xl"
                >
                    {{ item }}
                    <span
                        class="size-2.5 rounded-full bg-[var(--pop)]"
                        aria-hidden="true"
                    />
                </span>
            </Marquee>
        </div>
    </section>
</template>
