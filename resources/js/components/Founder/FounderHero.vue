<script setup lang="ts">
import { ArrowDownRight, Download } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AnimatedCounter from '@/components/Site/AnimatedCounter.vue';
import SmartImage from '@/components/SmartImage.vue';
import { EASE_OUT } from '@/composables/motion';
import { fillTokens } from '@/composables/text';
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion';
import { useSite } from '@/composables/useSite';
import type { FounderHeroProps } from '@/types';

/**
 * The founder hero: a portrait, the name set large, rotating role titles,
 * the headline numbers and the resume.
 */
const props = defineProps<FounderHeroProps>();

const { founder, studio } = useSite();
const reducedMotion = usePrefersReducedMotion();

const title = computed(() =>
    fillTokens(founder.value.hero.title || "Hi, I'm {{name}}.", {
        name: founder.value.name,
    }),
);

const roles = computed(() =>
    founder.value.roles.length
        ? founder.value.roles
        : [founder.value.headline ?? ''],
);
const roleIndex = ref(0);
let timer: number | undefined;

onMounted(() => {
    if (roles.value.length > 1 && !reducedMotion.value) {
        timer = window.setInterval(() => {
            roleIndex.value = (roleIndex.value + 1) % roles.value.length;
        }, 2600);
    }
});

onBeforeUnmount(() => window.clearInterval(timer));

const figures = computed(() =>
    [
        {
            value: props.stats.yearsExperience,
            label: props.content.hero.years_label,
        },
        {
            value: props.stats.projects,
            label: props.content.hero.projects_label,
        },
        { value: props.stats.skills, label: props.content.hero.skills_label },
    ].filter((figure) => figure.value > 0),
);

const reveal = (delay: number) => ({
    initial: { opacity: 0, y: 32 },
    animate: { opacity: 1, y: 0 },
    transition: { duration: 0.9, ease: EASE_OUT, delay },
});
</script>

<template>
    <section class="relative overflow-hidden" aria-labelledby="founder-title">
        <div
            class="container-site relative grid gap-14 pt-10 pb-20 sm:pt-16 lg:grid-cols-12 lg:items-center"
        >
            <div class="lg:col-span-7">
                <motion.p v-bind="reveal(0.1)" class="eyebrow">
                    Founder of {{ studio.name }}
                </motion.p>

                <motion.h1
                    id="founder-title"
                    v-bind="reveal(0.2)"
                    class="display display-lg mt-8"
                >
                    {{ title }}
                </motion.h1>

                <motion.div
                    v-bind="reveal(0.3)"
                    class="mt-6 h-12 overflow-hidden sm:h-14"
                    aria-live="polite"
                >
                    <AnimatePresence mode="wait">
                        <motion.p
                            :key="roleIndex"
                            class="display text-4xl text-brand-text sm:text-5xl"
                            :initial="{ y: '100%', opacity: 0 }"
                            :animate="{ y: 0, opacity: 1 }"
                            :exit="{ y: '-100%', opacity: 0 }"
                            :transition="{ duration: 0.5, ease: EASE_OUT }"
                        >
                            {{ roles[roleIndex] }}
                        </motion.p>
                    </AnimatePresence>
                </motion.div>

                <motion.p v-bind="reveal(0.4)" class="lead mt-8 max-w-xl">
                    {{ founder.hero.statement || founder.tagline }}
                </motion.p>

                <motion.div
                    v-bind="reveal(0.5)"
                    class="mt-10 flex flex-wrap gap-3"
                >
                    <a
                        v-if="founder.hero.primary_cta.label"
                        :href="founder.hero.primary_cta.url || '#projects'"
                        class="btn btn-ink"
                    >
                        {{ founder.hero.primary_cta.label }}
                        <ArrowDownRight
                            class="btn-arrow size-4"
                            aria-hidden="true"
                        />
                    </a>
                    <a
                        v-if="founder.resume_url"
                        :href="founder.resume_url"
                        target="_blank"
                        rel="noopener"
                        class="btn btn-outline"
                    >
                        <Download class="size-4" aria-hidden="true" />
                        {{ content.hero.resume_label }}
                    </a>
                </motion.div>

                <motion.dl
                    v-if="figures.length"
                    v-bind="reveal(0.6)"
                    class="mt-14 flex flex-wrap gap-x-12 gap-y-6"
                >
                    <div v-for="figure in figures" :key="figure.label">
                        <dd class="display text-6xl">
                            <AnimatedCounter :value="figure.value" />
                        </dd>
                        <dt class="meta mt-1">{{ figure.label }}</dt>
                    </div>
                </motion.dl>
            </div>

            <motion.div
                class="relative lg:col-span-5"
                :initial="{ opacity: 0, scale: 0.94, rotate: -2 }"
                :animate="{ opacity: 1, scale: 1, rotate: 0 }"
                :transition="{ duration: 1.1, ease: EASE_OUT, delay: 0.3 }"
            >
                <SmartImage
                    :src="founder.avatar_url"
                    :alt="founder.name"
                    ratio="4/5"
                    eager
                    class="rounded-[2rem]"
                >
                    <template #fallback>
                        <span class="display text-[10rem] text-stone/30">{{
                            founder.name.charAt(0)
                        }}</span>
                    </template>
                </SmartImage>

                <p
                    class="absolute -bottom-5 left-6 inline-flex items-center gap-3 rounded-full border border-line bg-paper px-5 py-3 text-sm shadow-sm"
                >
                    <span
                        class="status-dot"
                        :class="
                            !founder.is_available_for_work && 'status-dot-off'
                        "
                    />
                    {{
                        founder.is_available_for_work
                            ? content.about.available_open
                            : content.about.available_closed
                    }}
                </p>
            </motion.div>
        </div>
    </section>
</template>
