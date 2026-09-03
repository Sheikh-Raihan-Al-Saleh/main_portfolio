<script setup lang="ts">
import { motion } from 'motion-v';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
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
                'bg-noise scroll-mt-24 border-t border-border py-20 sm:py-24',
                props.muted && 'bg-muted/30',
                props.class,
            )
        "
    >
        <div class="corner-dot corner-dot-tl" aria-hidden="true" />
        <div class="corner-dot corner-dot-tr" aria-hidden="true" />

        <motion.div
            :variants="stagger(0.1)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            :class="
                cn(
                    'container-laravel section-dashed-xl',
                    props.wide ? 'max-w-7xl' : '',
                )
            "
        >
            <motion.div
                v-if="props.eyebrow || props.heading || props.subheading"
                :variants="fadeUp"
                class="mb-12 max-w-3xl"
            >
                <p
                    v-if="props.eyebrow"
                    class="mb-3 font-mono text-xs text-brand uppercase"
                >
                    {{ props.eyebrow }}
                </p>
                <h2
                    v-if="props.heading"
                    class="font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl"
                >
                    {{ props.heading }}
                </h2>
                <p
                    v-if="props.subheading"
                    class="mt-4 text-lg text-pretty text-muted-foreground"
                >
                    {{ props.subheading }}
                </p>
            </motion.div>

            <motion.div :variants="fadeUp">
                <slot />
            </motion.div>
        </motion.div>
    </section>
</template>
