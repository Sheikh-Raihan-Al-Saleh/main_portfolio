<script setup lang="ts">
import { computed } from 'vue';
import Reveal from '@/components/Site/Reveal.vue';
import { highlightParts } from '@/composables/text';
import { cn } from '@/composables/utils';
import type { SectionHeadingProps } from '@/types';

/**
 * The section opener: a pill label, a headline with one honey-marked phrase,
 * and an optional lead paragraph.
 */

const props = withDefaults(defineProps<SectionHeadingProps>(), {
    eyebrow: null,
    highlight: null,
    description: null,
    align: 'left',
    size: 'md',
    class: undefined,
});

const parts = computed(() => highlightParts(props.title, props.highlight));
</script>

<template>
    <header
        :class="
            cn(
                'grid gap-6',
                align === 'split' && 'lg:grid-cols-12 lg:items-end lg:gap-10',
                props.class,
            )
        "
    >
        <Reveal :class="align === 'split' ? 'lg:col-span-7' : 'max-w-4xl'">
            <p v-if="eyebrow" class="eyebrow mb-5">{{ eyebrow }}</p>
            <h2
                :class="
                    cn('display', size === 'lg' ? 'display-lg' : 'display-md')
                "
            >
                <template v-for="(part, partIndex) in parts" :key="partIndex">
                    <em v-if="part.highlight">{{ part.text }}</em>
                    <template v-else>{{ part.text }}</template>
                </template>
            </h2>
        </Reveal>

        <Reveal
            v-if="description || $slots.default"
            :delay="0.1"
            :class="align === 'split' ? 'lg:col-span-5 lg:pb-2' : 'max-w-2xl'"
        >
            <p v-if="description" class="lead">{{ description }}</p>
            <slot />
        </Reveal>
    </header>
</template>
