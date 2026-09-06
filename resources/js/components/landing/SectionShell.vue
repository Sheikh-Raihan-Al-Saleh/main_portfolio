<script setup lang="ts">
import { motion } from 'motion-v';
import { fadeUp, inViewOnce, sectionReveal, stagger } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Props = {
    eyebrow?: string | null;
    heading?: string | null;
    subheading?: string | null;
    anchor?: string;
    muted?: boolean;
    wide?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    eyebrow: null,
    heading: null,
    subheading: null,
    anchor: undefined,
    muted: false,
    wide: false,
    class: undefined,
});
</script>

<template>
    <section
        :id="props.anchor"
        :class="
            cn(
                'relative scroll-mt-24 overflow-hidden py-16 sm:py-20',
                props.muted ? 'bg-slate-50/70' : 'bg-white',
                props.class,
            )
        "
    >
        <!-- subtle depth glow -->
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div
                class="absolute top-0 left-1/2 h-[420px] w-[900px] -translate-x-1/2 bg-gradient-to-b from-[#2563eb]/[0.03] to-transparent blur-2xl"
            />
        </div>

        <motion.div
            :variants="sectionReveal"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            :class="
                cn(
                    'relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8',
                    props.wide ? 'max-w-7xl' : 'max-w-6xl',
                )
            "
        >
            <motion.div
                v-if="props.eyebrow || props.heading || props.subheading"
                :variants="stagger(0.08)"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
                class="mx-auto mb-10 max-w-3xl text-center sm:mb-12"
            >
                <motion.div
                    v-if="props.eyebrow"
                    :variants="fadeUp"
                    class="flex justify-center"
                >
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold tracking-widest text-[#2563eb] uppercase shadow-sm"
                    >
                        <span
                            class="size-1.5 animate-pulse rounded-full bg-[#2563eb]"
                        />
                        {{ props.eyebrow }}
                    </span>
                </motion.div>
                <motion.h2
                    v-if="props.heading"
                    :variants="fadeUp"
                    class="landing-section-title mt-4 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl"
                >
                    {{ props.heading }}
                </motion.h2>
                <motion.p
                    v-if="props.subheading"
                    :variants="fadeUp"
                    class="mx-auto mt-3 max-w-2xl text-base leading-relaxed text-slate-500 sm:text-lg"
                >
                    {{ props.subheading }}
                </motion.p>
            </motion.div>

            <motion.div
                :variants="fadeUp"
                initial="hidden"
                while-in-view="visible"
                :in-view-options="inViewOnce"
            >
                <slot />
            </motion.div>
        </motion.div>
    </section>
</template>
