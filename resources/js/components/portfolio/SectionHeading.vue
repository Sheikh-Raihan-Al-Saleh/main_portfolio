<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';

type Props = {
    eyebrow?: string;
    index?: string;
    title: string;
    description?: string;
    align?: 'left' | 'center';
    highlight?: string;
};

const props = withDefaults(defineProps<Props>(), { align: 'left' });

const titleParts = computed<{ text: string; accent: boolean }[]>(() => {
    const hl = props.highlight;

    if (!hl) {
        return [{ text: props.title, accent: false }];
    }

    const parts = props.title.split(hl);

    if (parts.length < 2) {
        return [{ text: props.title, accent: false }];
    }

    return parts.flatMap((part, index) => {
        const segments = [{ text: part, accent: false }];

        if (index < parts.length - 1) {
            segments.push({ text: hl, accent: true });
        }

        return segments;
    });
});
</script>

<template>
    <motion.div
        :variants="stagger(0.12)"
        initial="hidden"
        while-in-view="visible"
        :in-view-options="inViewOnce"
        class="mb-14 flex gap-10"
        :class="
            align === 'center'
                ? 'mx-auto max-w-3xl flex-col items-center text-center'
                : 'max-w-5xl'
        "
    >
        <!-- Sidebar label — Laravel monospace sidebar -->
        <motion.div
            v-if="index || eyebrow"
            :variants="fadeUp"
            class="hidden max-w-72 shrink-0 border-r border-border pr-10 sm:block"
        >
            <span
                class="font-mono text-base text-balance text-foreground uppercase"
            >
                <span v-if="index" class="text-brand">{{ index }}</span>
                {{ eyebrow }}
            </span>
        </motion.div>

        <!-- Main heading -->
        <div>
            <motion.div
                v-if="eyebrow"
                :variants="fadeUp"
                class="mb-4 sm:hidden"
            >
                <span class="font-mono text-xs text-brand uppercase">
                    <template v-if="index">{{ index }} </template>{{ eyebrow }}
                </span>
            </motion.div>

            <motion.h2
                :variants="fadeUp"
                class="font-display text-4xl leading-[1.05] font-bold tracking-tight text-balance sm:text-5xl md:text-6xl"
            >
                <template v-for="(part, i) in titleParts" :key="i">
                    <span v-if="part.accent" class="text-brand">{{
                        part.text
                    }}</span>
                    <span v-else>{{ part.text }}</span>
                </template>
            </motion.h2>

            <motion.p
                v-if="description"
                :variants="fadeUp"
                class="mt-5 max-w-xl text-lg leading-relaxed text-pretty text-muted-foreground"
            >
                {{ description }}
            </motion.p>
        </div>
    </motion.div>
</template>
