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
                'relative scroll-mt-24 overflow-hidden py-20 sm:py-28',
                props.muted ? 'ui-band' : '',
                props.class,
            )
        "
    >
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
                class="mx-auto mb-12 max-w-4xl text-center sm:mb-16"
            >
                <motion.div
                    v-if="props.eyebrow"
                    :variants="fadeUp"
                    class="flex justify-center"
                >
                    <span class="ui-eyebrow">{{ props.eyebrow }}</span>
                </motion.div>
                <motion.h2
                    v-if="props.heading"
                    :variants="fadeUp"
                    class="ui-display ui-h2 mt-5 text-foreground"
                >
                    {{ props.heading }}
                </motion.h2>
                <motion.p
                    v-if="props.subheading"
                    :variants="fadeUp"
                    class="mx-auto mt-5 max-w-2xl text-lg leading-relaxed text-pretty text-muted-foreground sm:text-xl"
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
