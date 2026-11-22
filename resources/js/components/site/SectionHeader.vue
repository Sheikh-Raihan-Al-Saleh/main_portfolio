<script setup lang="ts">
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import { cn } from '@/lib/utils';

/**
 * The editorial heading every public section opens with.
 *
 * Full width (the default, `align="center"` for backwards compatibility): a
 * hairline, the chapter label "(01) — Eyebrow" in a narrow left column, and
 * a giant condensed title with its lead in the wide column beside it.
 * `align="left"` stacks the same parts for use inside a half-width column.
 * The highlighted phrase takes the face's accent.
 */
type Props = {
    eyebrow?: string;
    title: string;
    highlight?: string;
    description?: string;
    align?: 'center' | 'left';
    /** Chapter number shown before the eyebrow, e.g. "01". */
    index?: string;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    eyebrow: '',
    highlight: '',
    description: '',
    align: 'center',
    index: '',
    class: undefined,
});

const parts = computed<{ text: string; mark: boolean }[]>(() => {
    const { title, highlight } = props;

    if (!highlight || !title.includes(highlight)) {
        return [{ text: title, mark: false }];
    }

    const [before, ...rest] = title.split(highlight);

    return [
        { text: before ?? '', mark: false },
        { text: highlight, mark: true },
        { text: rest.join(highlight), mark: false },
    ].filter((part) => part.text.length > 0);
});

const stacked = computed(() => props.align === 'left');
</script>

<template>
    <div
        :class="
            cn(
                'grid gap-6 border-t border-border pt-6',
                stacked ? '' : 'lg:grid-cols-12 lg:gap-8',
                props.class,
            )
        "
    >
        <Reveal v-if="eyebrow || index" :class="stacked ? '' : 'lg:col-span-3'">
            <p class="ui-label flex flex-wrap items-center gap-2">
                <span v-if="index" class="text-muted-foreground"
                    >({{ index }})</span
                >
                <span v-if="index && eyebrow" aria-hidden="true">—</span>
                <span v-if="eyebrow">{{ eyebrow }}</span>
            </p>
        </Reveal>

        <div
            :class="
                cn(
                    'flex flex-col gap-6',
                    stacked ? '' : 'lg:col-span-9',
                    !eyebrow && !index && !stacked && 'lg:col-start-4',
                )
            "
        >
            <Reveal :delay="0.05">
                <h2 class="ui-display ui-h2">
                    <template v-for="(part, i) in parts" :key="i">
                        <span v-if="part.mark" class="ui-accent">{{
                            part.text
                        }}</span>
                        <template v-else>{{ part.text }}</template>
                    </template>
                </h2>
            </Reveal>
            <Reveal v-if="description" :delay="0.1">
                <p
                    class="max-w-xl text-lg leading-relaxed text-pretty text-muted-foreground"
                >
                    {{ description }}
                </p>
            </Reveal>
            <slot />
        </div>
    </div>
</template>
