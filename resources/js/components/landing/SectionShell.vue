<script setup lang="ts">
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import { cn } from '@/lib/utils';

/**
 * The frame every content block renders in (studio home blocks and project
 * landing pages): editorial heading, then the block body.
 */
type Props = {
    eyebrow?: string | null;
    heading?: string | null;
    subheading?: string | null;
    anchor?: string;
    /** A tinted band, to separate consecutive blocks. */
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

const hasHeading = computed(() =>
    Boolean(props.eyebrow || props.heading || props.subheading),
);
</script>

<template>
    <section
        :id="props.anchor"
        :class="
            cn(
                'section scroll-mt-24',
                props.muted && 'bg-paper-deep',
                props.class,
            )
        "
    >
        <div class="container-site">
            <Reveal
                v-if="hasHeading"
                class="mb-12 grid gap-6 sm:mb-16 lg:grid-cols-12 lg:items-end"
            >
                <div class="lg:col-span-7">
                    <p v-if="props.eyebrow" class="eyebrow mb-5">
                        {{ props.eyebrow }}
                    </p>
                    <h2 v-if="props.heading" class="display display-md">
                        {{ props.heading }}
                    </h2>
                </div>
                <p v-if="props.subheading" class="lead lg:col-span-5">
                    {{ props.subheading }}
                </p>
            </Reveal>

            <Reveal
                :delay="hasHeading ? 0.1 : 0"
                :class="props.wide ? '' : 'mx-auto max-w-6xl'"
            >
                <slot />
            </Reveal>
        </div>
    </section>
</template>
