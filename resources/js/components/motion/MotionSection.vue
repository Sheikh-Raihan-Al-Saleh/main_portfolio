<script setup lang="ts">
import { computed } from 'vue';
import SectionHeading from '@/components/portfolio/SectionHeading.vue';
import { cn } from '@/lib/utils';

/**
 * The shell every Studio chapter is built on.
 *
 * It owns the things that must never drift between sections — the container
 * width, the dashed frame, the corner dots, the noise grain, the chapter
 * numbering and the heading — so a new section is content plus a heading, not
 * a fresh copy of the layout. Sections that need to sit outside the frame
 * (a full-bleed backdrop, a marquee) can opt out of each piece.
 */
type Props = {
    id?: string;
    /** Chapter number, printed in brand colour beside the eyebrow. */
    index?: string;
    eyebrow?: string;
    title: string;
    /** The word inside `title` to paint in brand colour. */
    highlight?: string;
    description?: string;
    align?: 'left' | 'center';
    /** Renders the dashed vertical frame at xl and above. */
    frame?: boolean;
    /** Draws the separation rule and the corner dots. */
    bordered?: boolean;
    /** Applies `content-visibility: auto`, for long pages. */
    defer?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    id: undefined,
    index: undefined,
    eyebrow: undefined,
    highlight: undefined,
    description: undefined,
    align: 'left',
    frame: true,
    bordered: true,
    defer: true,
    class: undefined,
});

const sectionClass = computed(() =>
    cn(
        'bg-noise relative scroll-mt-20 py-20 sm:py-24',
        props.bordered && 'border-t border-border',
        props.defer && 'cv-auto',
        props.class,
    ),
);

const containerClass = computed(() =>
    cn('container-laravel', props.frame && 'section-dashed-xl'),
);
</script>

<template>
    <section :id="props.id" :class="sectionClass">
        <div
            v-if="props.bordered"
            class="corner-dot corner-dot-tl"
            aria-hidden="true"
        />
        <div
            v-if="props.bordered"
            class="corner-dot corner-dot-tr"
            aria-hidden="true"
        />

        <div :class="containerClass">
            <SectionHeading
                :index="props.index"
                :eyebrow="props.eyebrow"
                :title="props.title"
                :highlight="props.highlight"
                :description="props.description"
                :align="props.align"
            />

            <slot />
        </div>
    </section>
</template>
